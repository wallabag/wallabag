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
            docker.write_text('#!' + sys.executable + '\nimport json,sys,tarfile\nprint(json.dumps({"members": tarfile.open(fileobj=sys.stdin.buffer, mode="r|*").getnames(), "arguments": sys.argv[1:]}))\n')
            docker.chmod(0o755)
            # Recorder is outside the Git build context.
            (root / '.git/info/exclude').write_text('recorder/\n')
            result = subprocess.check_output(['sh', str(root / 'scripts/build-production-image'), 'synthetic:build'], env={**os.environ, 'PATH': str(recorder) + os.pathsep + os.environ['PATH']}, text=True)
            receipt = json.loads(result)
            self.assertIn('tracked.txt', receipt['members'])
            self.assertFalse(any(path.startswith(('web/uploads', 'data/', '.git/')) for path in receipt['members']))
            self.assertIn('--network=none', receipt['arguments'])
            self.assertEqual('-', receipt['arguments'][-1])
        exclusions = (ROOT / '.dockerignore').read_text().splitlines()
        self.assertIn('web/uploads', exclusions)
        self.assertIn('data', exclusions)


if __name__ == '__main__':
    unittest.main()
