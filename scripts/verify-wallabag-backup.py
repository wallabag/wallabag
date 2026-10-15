#!/usr/bin/env python3
"""Restore a server-local backup in disposable network-none containers, then migrate.

Never connects to the live DB. Requires Docker and operator-host PyYAML. Logs can
contain restored data: the backup directory and all files must remain private.
"""

import argparse
import gzip
import hashlib
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile
import time
import yaml

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('backup', type=Path)
parser.add_argument('--application-image', required=True)
parser.add_argument('--database-memory', choices=['512m', '1g', '2g'], default='1g')
args = parser.parse_args()
os.umask(0o077)
root = args.backup.resolve(strict=True)
backup = json.loads((root / 'backup.json').read_text())
image = backup['previous_db_image']
if not image.startswith('sha256:'):
    parser.error('Backup must record its exact previous DB image ID.')
name = 'wallabag-restore-' + hashlib.sha256(str(root).encode()).hexdigest()[:12]
owner = 'io.wallabag.restore'
created = []
volumes = [name + '-data', name + '-socket']
report = {'restore_pass': False, 'migration_pass': False, 'production_network_access': False, 'production_data_modified': False}


def run(command, **kwargs):
    return subprocess.run(command, check=True, **kwargs)


def sql(query, database=None):
    command = ['docker', 'exec', name, 'mariadb', '-uroot', '-N', '-B', '-e', query]
    if database:
        command.append(database)
    return subprocess.check_output(command, text=True)


for kind, target in [('container', name), ('container', name + '-app')] + [('volume', v) for v in volumes]:
    if subprocess.run(['docker', kind, 'inspect', target], capture_output=True).returncode == 0:
        raise SystemExit('Restore resource already exists; refusing adoption: ' + target)
try:
    for volume in volumes:
        run(['docker', 'volume', 'create', '--label', owner + '=' + str(root), volume], stdout=subprocess.DEVNULL)
        created.append(('volume', volume))
    run(['docker', 'create', '--name', name, '--label', owner + '=' + str(root), '--network', 'none', '--cpus', '1', '--memory', args.database_memory, '--pids-limit', '128', '--env', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1', '--mount', 'type=volume,src=' + volumes[0] + ',dst=/var/lib/mysql', '--mount', 'type=volume,src=' + volumes[1] + ',dst=/run/mysqld', image, '--skip-networking', '--innodb-buffer-pool-size=64M', '--max-allowed-packet=256M'], stdout=subprocess.DEVNULL)
    created.append(('container', name))
    run(['docker', 'start', name], stdout=subprocess.DEVNULL)
    for attempt in range(60):
        if subprocess.run(['docker', 'exec', name, 'sh', '-c', 'case "$(cat /proc/1/comm)" in mariadbd|mysqld) mariadb-admin -uroot ping >/dev/null;; *) exit 1;; esac'], capture_output=True, timeout=5).returncode == 0:
            break
        time.sleep(1)
    else:
        raise RuntimeError('Isolated restore DB readiness timeout.')
    # A seekable temporary input and file stderr avoid deadlock if a client emits
    # a large SQL error. Never print that stderr: it may quote restored content.
    with tempfile.TemporaryFile(dir=root) as raw, gzip.open(root / 'database.sql.gz', 'rb') as source:
        shutil.copyfileobj(source, raw)
        raw.seek(0)
        with (root / 'restore.error').open('wb') as error:
            result = subprocess.run(['docker', 'exec', '-i', name, 'mariadb', '-uroot', '--max-allowed-packet=256M'], stdin=raw, stdout=subprocess.DEVNULL, stderr=error, timeout=180)
        if result.returncode:
            raise RuntimeError('Restore failed; inspect private server-local restore.error.')
    parameters = yaml.safe_load((root / 'runtime/parameters.yml').read_text())
    dbname = parameters['parameters']['database_name']
    prefix = parameters['parameters']['database_table_prefix']
    if not all(c.isalnum() or c == '_' for c in prefix):
        raise RuntimeError('Unexpected table prefix.')
    entries = "SELECT id,SHA2(CONCAT_WS('|',COALESCE(url,''),COALESCE(content,''),COALESCE(title,''),user_id,is_archived,is_starred),256) FROM `" + prefix + "entry` ORDER BY id"
    users = "SELECT id,SHA2(CONCAT_WS('|',username_canonical,password,COALESCE(salt,''),enabled),256) FROM `" + prefix + "user` ORDER BY id"
    before_entries, before_users = sql(entries, dbname), sql(users, dbname)
    report.update(restore_pass=True, entry_count=len(before_entries.splitlines()), user_count=len(before_users.splitlines()))
    parameters['parameters'].update(database_host='localhost', database_port=None, database_socket='/run/mysqld/mysqld.sock', database_user='root', database_password=None)
    config = root / 'restore-runtime'
    config.mkdir(exist_ok=True)
    (config / 'parameters.yml').write_text(yaml.safe_dump(parameters, sort_keys=False))
    for filename in ['site-credentials-key', 'php.ini']:
        shutil.copy2(root / 'runtime' / filename, config / filename)
    command = ['docker', 'create', '--name', name + '-app', '--label', owner + '=' + str(root), '--network', 'none', '--cpus', '1', '--memory', '256m', '--pids-limit', '128', '--mount', 'type=volume,src=' + volumes[1] + ',dst=/run/mysqld']
    for filename in ['parameters.yml', 'site-credentials-key', 'php.ini']:
        command += ['--mount', 'type=bind,src=' + str(config / filename) + ',dst=/run/wallabag/' + filename + ',readonly']
    command += [args.application_image, 'console', 'doctrine:migrations:migrate', '--env=prod', '--no-interaction', '--no-debug']
    run(command, stdout=subprocess.DEVNULL)
    created.append(('container', name + '-app'))
    with (root / 'restore-migration.log').open('w') as log:
        run(['docker', 'start', '-a', name + '-app'], stdout=log, stderr=subprocess.STDOUT, timeout=120)
    state = json.loads(subprocess.check_output(['docker', 'inspect', name + '-app']))[0]['State']
    if state['ExitCode'] != 0:
        raise RuntimeError('Isolated migration failed; inspect private restore-migration.log.')
    report.update(migration_pass=True, existing_entries_unchanged=before_entries == sql(entries, dbname), authentication_fingerprints_unchanged=before_users == sql(users, dbname))
    report['new_rss_tables'] = int(sql("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('" + prefix + "rss_feed','" + prefix + "rss_seen_item')", dbname).strip())
    report['x_capture_type_column'] = int(sql("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='" + prefix + "entry' AND COLUMN_NAME='x_capture_type'", dbname).strip())
    if report['x_capture_type_column'] != 1 or not report['existing_entries_unchanged'] or not report['authentication_fingerprints_unchanged'] or report['new_rss_tables'] != 2:
        raise RuntimeError('Isolated migration preservation check failed.')
finally:
    errors = []
    for kind, target in reversed(created):
        obj = json.loads(subprocess.check_output(['docker', kind, 'inspect', target]))[0]
        if kind == 'container':
            with (root / (target + '.log')).open('wb') as log:
                subprocess.run(['docker', 'logs', '--tail', '100', target], stdout=log, stderr=subprocess.STDOUT)
            if target == name:
                report['restore_db_state'] = {key: obj['State'][key] for key in ['Status', 'ExitCode', 'OOMKilled']}
        labels = obj.get('Labels', {}) if kind == 'volume' else obj['Config'].get('Labels', {})
        if labels.get(owner) != str(root):
            errors.append('Ownership mismatch: ' + target)
            continue
        result = subprocess.run(['docker', kind, 'rm', *(['-f'] if kind == 'container' else []), target], capture_output=True)
        if result.returncode:
            errors.append('Removal failed: ' + target)
    report['cleanup_errors'] = errors
    (root / 'restore-proof.json').write_text(json.dumps(report, indent=2))
    print(json.dumps(report))
