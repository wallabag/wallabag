#!/usr/bin/env python3
"""Source-only preparation; never starts a process, container, server or browser."""

import json
from pathlib import Path
import shutil
import subprocess

ROOT = Path(__file__).resolve().parents[1]
DEST = ROOT / '.validation/visual-app'
if DEST.exists():
    raise SystemExit('Staging directory exists; inspect it before removing/recreating it.')
DEST.mkdir(parents=True)
# Copy only versioned sources and this new harness, not data, credentials or caches.
files = subprocess.check_output(['git', 'ls-files', '-z'], cwd=ROOT).decode().split('\0')
files += [str(p.relative_to(ROOT)) for p in (ROOT / 'scripts/visual-validation').glob('*') if p.is_file()]
for name in sorted(set(files)):
    if not name or name.startswith(('data/', 'var/', 'vendor/', '.git/')) or name == 'app/config/parameters.yml':
        continue
    source = ROOT / name
    if source.is_file():
        target = DEST / name
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(source, target)
(DEST / 'vendor').symlink_to('/source/vendor', target_is_directory=True)
(DEST / 'data/db').mkdir(parents=True)
(DEST / 'var').mkdir()
config = DEST / 'app/config'
parameters = (config / 'parameters.yml.dist').read_text()
parameters = parameters.replace('database_host: 127.0.0.1', 'database_host: localhost')
parameters = parameters.replace('database_name: wallabag', 'database_name: wallabag_visual_synthetic')
parameters = parameters.replace('database_socket: null', 'database_socket: /run/mysqld/mysqld.sock')
parameters = parameters.replace('https://your-wallabag-instance.wallabag.org', 'http://127.0.0.1:8080')
parameters = parameters.replace('CHANGE_ME_TO_SOMETHING_SECRET_AND_RANDOM', 'isolated-local-fixtures-only-no-production-use')
(config / 'parameters.yml').write_text(parameters)
(config / 'parameters_test.yml').write_text('''parameters:
    test_database_driver: pdo_mysql
    test_database_host: localhost
    test_database_port: null
    test_database_name: wallabag_visual_synthetic
    test_database_user: root
    test_database_password: null
    test_database_charset: utf8mb4
    env(TEST_DATABASE_PATH): ''
''')
test = (config / 'config_test.yml').read_text()
test = test.replace('session.storage.mock_file', 'session.storage.native')
test = test.replace('enabled: false', 'enabled: true')
(config / 'config_test.yml').write_text(test)
with (config / 'services_test.yml').open('a') as stream:
    stream.write('''
    Wallabag\\CoreBundle\\Helper\\PublicHttpClient:
        class: WallabagVisualFixtureHttpClient
        file: '%kernel.project_dir%/scripts/visual-validation/FixtureHttpClient.php'
''')
(DEST / 'scripts/visual-validation/phpunit-bootstrap.php').write_text('''<?php
require_once '/work/app/AppKernel.php';
require_once '/work/vendor/autoload.php';
''')
(ROOT / '.validation/visual-preparation.json').write_text(json.dumps({
    'status': 'prepared-not-executed',
    'source_head': subprocess.check_output(['git', 'rev-parse', 'HEAD'], cwd=ROOT, text=True).strip(),
    'staged_root': str(DEST),
    'database': 'wallabag_visual_synthetic',
    'external_http': 'synthetic fixtures only; unexpected URLs fail closed',
    'runtime_gate': 'explicit settled release after Startup and Integration required',
}, indent=2))
print(DEST)
