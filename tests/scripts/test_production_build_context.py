"""Exercise the real build helper with a synthetic repository and Docker recorder."""
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile
import unittest

ROOT = Path(__file__).resolve().parents[2]


class ProductionContextTest(unittest.TestCase):
    def test_only_committed_source_is_sent_to_docker(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / 'scripts').mkdir()
            shutil.copy2(ROOT / 'scripts/build-production-image', root / 'scripts/build-production-image')
            (root / '.gitignore').write_text('web/uploads/\ndata/\n')
            (root / 'tracked.txt').write_text('source only')
            (root / '.env').write_text('APP_SECRET=synthetic-local-secret')
            (root / '.env.local').write_text('DATABASE_URL=synthetic-private-database')
            (root / '.composer-auth.json').write_text('{"http-basic":{"example.invalid":{"password":"synthetic-token"}}}')
            subprocess.run(['git', 'init', '-q', str(root)], check=True)
            subprocess.run(['git', '-C', str(root), 'add', '.'], check=True)
            subprocess.run(['git', '-C', str(root), '-c', 'user.name=Synthetic', '-c', 'user.email=synthetic@example.invalid', '-c', 'commit.gpgsign=false', 'commit', '-qm', 'Synthetic fixture'], check=True)
            for relative in ['web/uploads/import/ignored-private.zip', 'data/ignored-key']:
                path = root / relative
                path.parent.mkdir(parents=True, exist_ok=True)
                path.write_text('synthetic private sentinel')
            recorder = root / 'recorder'
            recorder.mkdir()
            docker = recorder / 'docker'
            docker.write_text(
                '#!' + sys.executable + '\n'
                'import json,os,sys,tarfile\n'
                'record=os.environ["DOCKER_RECORD"]\n'
                'calls=json.load(open(record)) if os.path.exists(record) else []\n'
                'call={"arguments":sys.argv[1:]}\n'
                'if sys.argv[1] == "build": call["members"]=tarfile.open(fileobj=sys.stdin.buffer, mode="r|*").getnames()\n'
                'calls.append(call)\njson.dump(calls,open(record,"w"))\n'
                'if sys.argv[1:3] == ["image","inspect"]:\n'
                ' revision=next(a.split("=",1)[1] for a in calls[0]["arguments"] if a.startswith("VCS_REF="))\n'
                ' print("8.2.35|"+revision)\n'
                'elif sys.argv[1] == "run":\n'
                ' entrypoint=sys.argv[sys.argv.index("--entrypoint")+1]\n'
                ' revision=next(a.split("=",1)[1] for a in calls[0]["arguments"] if a.startswith("VCS_REF="))\n'
                ' print("8.2.35" if entrypoint == "php" else revision)\n'
            )
            docker.chmod(0o755)
            # Recorder is outside the Git build context.
            (root / '.git/info/exclude').write_text('recorder/\n')
            record = root / 'calls.json'
            result = subprocess.check_output(
                ['sh', str(root / 'scripts/build-production-image'), 'synthetic:build'],
                env={**os.environ, 'PATH': str(recorder) + os.pathsep + os.environ['PATH'], 'DOCKER_RECORD': str(record)},
                text=True,
            )
            calls = json.loads(record.read_text())
            build, inspect, run, source = calls
            self.assertIn('tracked.txt', build['members'])
            self.assertFalse(any(path.startswith(('web/uploads', 'data/', '.git/')) for path in build['members']))
            self.assertFalse(any(path in {'.env', '.env.local', '.composer-auth.json'} for path in build['members']))
            self.assertIn('--build-arg', build['arguments'])
            self.assertIn('PHP_VERSION=8.2.35', build['arguments'])
            self.assertNotIn('--network=none', build['arguments'])
            self.assertIn('--format', inspect['arguments'])
            self.assertEqual('8.2.35', result.split('php_label=')[1].split()[0])
            self.assertIn('--network', run['arguments'])
            self.assertIn('none', run['arguments'])
            self.assertIn('--entrypoint', run['arguments'])
            self.assertEqual('php', run['arguments'][run['arguments'].index('--entrypoint') + 1])
            self.assertEqual('cat', source['arguments'][source['arguments'].index('--entrypoint') + 1])
            self.assertIn('source_revision=', result)

    def test_production_image_declares_and_checks_patched_php(self):
        dockerfile = (ROOT / 'docker/production/Dockerfile').read_text()
        self.assertIn('ARG PHP_VERSION=8.2.35', dockerfile)
        self.assertIn('php:${PHP_VERSION}-apache-trixie@sha256:', dockerfile)
        self.assertIn('com.jonniesweb.wallabag.php.version="${PHP_VERSION}"', dockerfile)
        self.assertIn('test "$(php -r \'echo PHP_VERSION;\')" = "$PHP_VERSION"', dockerfile)
        self.assertNotIn('wallabag/wallabag:2.6.14', dockerfile)
        exclusions = (ROOT / '.dockerignore').read_text().splitlines()
        self.assertIn('web/uploads', exclusions)
        self.assertIn('data', exclusions)


if __name__ == '__main__':
    unittest.main()
