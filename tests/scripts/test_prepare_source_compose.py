import json
from pathlib import Path
import stat
import subprocess
import sys
import tempfile
import unittest

import yaml

SCRIPT = Path(__file__).resolve().parents[2] / 'scripts/prepare-source-compose.py'


class ComposeCandidateTest(unittest.TestCase):
    def test_candidate_preserves_dependencies_and_ports_without_copying_worker_env(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            original = {'services': {
                'wallabag': {'image': 'old:stable', 'ports': ['1111:80'], 'environment': {'EXISTING_SECRET': 'synthetic'}, 'volumes': ['./media:/var/www/wallabag/web/assets/images'], 'labels': {'traefik.enable': 'true', 'traefik.http.routers.wallabag.rule': 'Host(`synthetic.test`)', 'caddy': 'synthetic.test'}, 'networks': {'private': {'ipv4_address': '172.31.0.5', 'aliases': ['application']}, 'ingress': {'ipv6_address': 'fd00::5'}}, 'deploy': {'labels': {'traefik.enable': 'true'}}, 'expose': ['80']},
                'db': {'image': 'database:fixed', 'environment': {'PASSWORD': 'synthetic'}},
            }}
            source = root / 'compose.yml'
            source.write_text(json.dumps(original))
            for name in ['parameters.yml', 'site-credentials-key', 'php.ini']:
                (root / name).write_text('synthetic-test-only')
            candidate = root / 'candidate.yml'
            command = [sys.executable, str(SCRIPT), str(source), str(candidate), '--image', 'local:' + 'a' * 40, '--revision', 'a' * 40, '--runtime-config', str(root)]
            subprocess.run(command, check=True, capture_output=True)
            actual = yaml.safe_load(candidate.read_text())
            self.assertEqual(original['services']['db'], actual['services']['db'])
            self.assertEqual(['1111:80'], actual['services']['wallabag']['ports'])
            self.assertEqual(original['services']['wallabag']['environment'], actual['services']['wallabag']['environment'])
            self.assertNotIn('environment', actual['services']['rss-worker'])
            self.assertNotIn('ports', actual['services']['rss-worker'])
            self.assertEqual(['private', 'ingress'], actual['services']['rss-worker']['networks'])
            self.assertEqual(original['services']['wallabag']['networks'], actual['services']['wallabag']['networks'])
            self.assertEqual({'traefik.enable': 'false', 'com.centurylinklabs.watchtower.enable': 'false'}, actual['services']['rss-worker']['labels'])
            self.assertNotIn('deploy', actual['services']['rss-worker'])
            self.assertNotIn('expose', actual['services']['rss-worker'])
            self.assertEqual('256m', actual['services']['rss-worker']['mem_limit'])
            self.assertEqual(0o600, stat.S_IMODE(candidate.stat().st_mode))
            self.assertEqual(original, yaml.safe_load(source.read_text()))
            self.assertNotEqual(0, subprocess.run(command, capture_output=True).returncode)
            original['services']['wallabag']['network_mode'] = 'host'
            source.write_text(json.dumps(original))
            rejected = root / 'rejected.yml'
            command[3] = str(rejected)
            self.assertNotEqual(0, subprocess.run(command, capture_output=True).returncode)
            self.assertFalse(rejected.exists())


if __name__ == '__main__':
    unittest.main()
