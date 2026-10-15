#!/usr/bin/env python3
"""Isolated MariaDB/Chromium runtime. Requires an explicitly released runtime slot.

Images must already be cached and supplied by immutable digest. No pulls, Docker
networks, host ports, production data, host credentials or foreign resource changes.
"""

import argparse
import hashlib
import json
from pathlib import Path
import re
import shutil
import subprocess
import time

ROOT = Path(__file__).resolve().parents[2]
ART = ROOT / '.validation'
KEY = hashlib.sha256(str(ROOT).encode()).hexdigest()[:12]
PREFIX = 'wallabag-visual-' + KEY
LABEL = 'io.wallabag.validation.root=' + str(ROOT)
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--runtime-slot-released', action='store_true', required=True)
parser.add_argument('--mariadb-image', required=True)
parser.add_argument('--browser-image', required=True)
parser.add_argument('--public-x-receipt', type=Path, help='Optional previously retrieved public article JSON; exact integrity is verified.')
args = parser.parse_args()
if args.public_x_receipt:
    args.public_x_receipt = args.public_x_receipt.resolve(strict=True)
    if hashlib.sha256(args.public_x_receipt.read_bytes()).hexdigest() != 'b4fe5c4a1619c55187a2f692a09b0c7853077b241ab850e66e2045859281213d':
        parser.error('Public article receipt integrity mismatch.')
for image in [args.mariadb_image, args.browser_image]:
    if not re.fullmatch(r'[a-zA-Z0-9./:_-]+@sha256:[a-f0-9]{64}', image):
        parser.error('Use an image reference pinned by sha256 digest.')
if not args.browser_image.startswith('mcr.microsoft.com/playwright:v1.63.0-noble@'):
    parser.error('Use the matching official Playwright v1.63.0-noble image digest.')
PHP_IMAGE = 'wallabag-local-' + KEY + ':test'
EVIDENCE = ART / 'visual-evidence'
STAGE = ART / 'visual-app'
PACKAGE = ART / 'playwright/node_modules/playwright-core'
if not STAGE.is_dir() or not (PACKAGE / 'package.json').is_file():
    parser.error('Prepare the isolated app and own playwright-core package first.')
if json.loads((PACKAGE / 'package.json').read_text())['version'] != '1.63.0':
    parser.error('This harness expects playwright-core 1.63.0 and its matching browser image.')


def run(*command, **kwargs):
    return subprocess.run(list(command), check=True, **kwargs)


def output(*command):
    return subprocess.check_output(list(command), text=True).strip()


names = {kind: PREFIX + '-' + kind for kind in ['db', 'app', 'browser', 'data', 'socket']}
for kind, name in names.items():
    resource = 'volume' if kind in ['data', 'socket'] else 'container'
    if subprocess.run(['docker', resource, 'inspect', name], capture_output=True).returncode == 0:
        raise SystemExit('Resource exists; refusing to adopt or replace: ' + name)
for image in [PHP_IMAGE, args.mariadb_image, args.browser_image]:
    run('docker', 'image', 'inspect', image, stdout=subprocess.DEVNULL)
php = json.loads(output('docker', 'image', 'inspect', PHP_IMAGE))[0]
if php['Config'].get('Labels', {}).get('io.wallabag.validation.root') != str(ROOT):
    raise SystemExit('PHP image ownership mismatch.')
EVIDENCE.mkdir(exist_ok=True)
created = []
report = {'status': 'running', 'images': {}, 'networks_created': [], 'published_ports': [], 'synthetic': True}
for image in [PHP_IMAGE, args.mariadb_image, args.browser_image]:
    report['images'][image] = json.loads(output('docker', 'image', 'inspect', image))[0]['Id']


def checkpoint():
    report['created_resources'] = [{'kind': kind, 'name': name} for kind, name in created]
    (EVIDENCE / 'runtime-result.json').write_text(json.dumps(report, indent=2))


def create(kind, image, options, command):
    name = names[kind]
    run('docker', 'create', '--name', name, '--label', LABEL, '--cpus', '1',
        '--memory', '768m' if kind == 'browser' else ('512m' if kind == 'db' else '256m'),
        '--pids-limit', '256' if kind == 'browser' else '128', '--pull', 'never',
        *options, image, *command, stdout=subprocess.DEVNULL)
    created.append(('container', name))
    checkpoint()


try:
    for kind in ['data', 'socket']:
        run('docker', 'volume', 'create', '--label', LABEL, names[kind], stdout=subprocess.DEVNULL)
        created.append(('volume', names[kind]))
        checkpoint()
    socket = 'type=volume,src=' + names['socket'] + ',dst=/run/mysqld'
    create('db', args.mariadb_image, ['--network', 'none', '--env', 'MARIADB_ALLOW_EMPTY_ROOT_PASSWORD=1',
           '--env', 'MARIADB_DATABASE=wallabag_visual_synthetic', '--mount', socket,
           '--mount', 'type=volume,src=' + names['data'] + ',dst=/var/lib/mysql'],
           ['--skip-networking', '--socket=/run/mysqld/mysqld.sock', '--innodb-buffer-pool-size=64M'])
    run('docker', 'start', names['db'], stdout=subprocess.DEVNULL)
    for attempt in range(60):
        ready = subprocess.run(['docker', 'exec', names['db'], 'mariadb-admin', '--socket=/run/mysqld/mysqld.sock', '-uroot', 'ping'], capture_output=True, timeout=5)
        if ready.returncode == 0:
            break
        time.sleep(1)
    else:
        raise RuntimeError('Synthetic MariaDB did not become ready within 60 seconds.')
    report['mariadb_version'] = output('docker', 'exec', names['db'], 'mariadb', '-uroot', '-Nse', 'SELECT VERSION()')
    app_options = ['--network', 'none', '--env', 'WALLABAG_ISOLATED_VISUAL=1', '--env', 'TEST_DATABASE_PATH=',
                   '--env', 'SYMFONY_DEPRECATIONS_HELPER=weak', '--mount', socket,
                   '--mount', 'type=bind,src=' + str(STAGE) + ',dst=/work',
                   '--mount', 'type=bind,src=' + str(ROOT) + ',dst=/source,readonly',
                   '--mount', 'type=bind,src=' + str(EVIDENCE) + ',dst=/evidence', '--workdir', '/work']
    if args.public_x_receipt:
        app_options += ['--env', 'WALLABAG_PUBLIC_X_RECEIPT=1', '--mount', 'type=bind,src=' + str(args.public_x_receipt) + ',dst=/receipts/public-x.json,readonly']
    create('app', PHP_IMAGE, app_options, ['-ec', 'exec php -S 127.0.0.1:8080 -t web scripts/visual-validation/router.php'])
    run('docker', 'start', names['app'], stdout=subprocess.DEVNULL)
    for command in [
        ['php', 'scripts/visual-validation/console.php', 'doctrine:migrations:migrate', '--no-interaction'],
        ['php', 'scripts/visual-validation/console.php', 'doctrine:fixtures:load', '--no-interaction'],
        ['php', 'scripts/visual-validation/console.php', 'doctrine:schema:validate', '--skip-sync'],
        ['php', 'scripts/visual-validation/console.php', 'assets:install', 'web'],
        ['php', '/source/.validation/phpunit/vendor/bin/phpunit', '--bootstrap', 'scripts/visual-validation/phpunit-bootstrap.php', '--filter', '(Rss|Grabber|MediumExtractor|MediumContentProxyTest|FeedParserTest|FeedImporterTest|PublicHttpClientTest|XPostExtractorTest|FxArticleReaderTest|XContentProxyTest|XEntry|XArticleRestControllerTest)', '--log-junit', '/evidence/mariadb-phpunit.xml'],
    ]:
        with (EVIDENCE / 'mariadb-checks.log').open('a') as log:
            run('docker', 'exec', names['app'], *command, stdout=log, stderr=subprocess.STDOUT, timeout=180)
    # BrowserKit needs mock sessions; actual HTTP requests need native sessions.
    # No HTTP request has been served yet, so clear only this staged test cache.
    config = STAGE / 'app/config/config_test.yml'
    config.write_text(config.read_text().replace('session.storage.mock_file', 'session.storage.native'))
    cache = STAGE / 'var/cache/test'
    if cache.exists():
        shutil.rmtree(cache)
    if args.public_x_receipt:
        article = json.loads(args.public_x_receipt.read_text())['status']['article']
        (EVIDENCE / 'public-article-blocks.json').write_text(json.dumps(article['content']['blocks']))
    create('browser', args.browser_image,
           ['--network', 'container:' + names['app'], '--init', '--shm-size', '256m',
            '--env', 'WALLABAG_ISOLATED_VISUAL=1', '--env', 'NODE_PATH=/packages',
            '--env', 'WALLABAG_PUBLIC_X_RECEIPT=' + ('1' if args.public_x_receipt else '0'),
            '--mount', 'type=bind,src=' + str(PACKAGE) + ',dst=/packages/playwright-core,readonly',
            '--mount', 'type=bind,src=' + str(ROOT / 'scripts/visual-validation') + ',dst=/suite,readonly',
            '--mount', 'type=bind,src=' + str(EVIDENCE) + ',dst=/evidence'],
           ['node', '/suite/browser.cjs'])
    with (EVIDENCE / 'browser.log').open('w') as log:
        run('docker', 'start', '-a', names['browser'], stdout=log, stderr=subprocess.STDOUT, timeout=180)
    exit_code = output('docker', 'inspect', '--format', '{{.State.ExitCode}}', names['browser'])
    if exit_code != '0':
        raise RuntimeError('Chromium suite failed; inspect browser.log.')
    if not (EVIDENCE / 'browser-result.json').is_file():
        raise RuntimeError('Browser completion receipt missing.')
    report['status'] = 'pass'
finally:
    if report['status'] == 'running':
        report['status'] = 'failed'
    # Only resources successfully created in this invocation are eligible for removal.
    cleanup_errors = []
    for kind, name in reversed(created):
        item = json.loads(output('docker', kind, 'inspect', name))[0]
        labels = item.get('Labels', {}) if kind == 'volume' else item['Config'].get('Labels', {})
        if labels.get('io.wallabag.validation.root') != str(ROOT):
            cleanup_errors.append('Ownership mismatch: ' + name)
            continue
        if kind == 'container':
            logs = subprocess.run(['docker', 'logs', name], capture_output=True, text=True)
            (EVIDENCE / (name + '.log')).write_text(logs.stdout + logs.stderr)
        result = subprocess.run(['docker', kind, 'rm', *(['-f'] if kind == 'container' else []), name], capture_output=True, text=True)
        if result.returncode:
            cleanup_errors.append(result.stderr)
    report['cleanup_errors'] = cleanup_errors
    report['resources_remaining'] = [name for kind, name in created if subprocess.run(['docker', kind, 'inspect', name], capture_output=True).returncode == 0]
    (EVIDENCE / 'runtime-result.json').write_text(json.dumps(report, indent=2))
    if cleanup_errors:
        raise RuntimeError('Owned cleanup incomplete; see runtime-result.json.')
