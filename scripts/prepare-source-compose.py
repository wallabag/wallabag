#!/usr/bin/env python3
"""Create a server-local Compose candidate; never prints expanded secrets.

Requires PyYAML on the operator host. Does not apply/restart any service.
"""

import argparse
import copy
import json
import os
from pathlib import Path
import re
import yaml

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('source', type=Path)
parser.add_argument('candidate', type=Path)
parser.add_argument('--image', required=True)
parser.add_argument('--revision', required=True)
parser.add_argument('--runtime-config', required=True, type=Path)
parser.add_argument('--service', default='wallabag')
args = parser.parse_args()
if not re.fullmatch(r'[a-f0-9]{40}', args.revision) or not args.image.endswith(':' + args.revision):
    parser.error('Use a full source revision and matching immutable local image tag.')
source = yaml.safe_load(args.source.read_text())
candidate = copy.deepcopy(source)
services = candidate['services']
if 'rss-worker' in services:
    parser.error('Candidate helper will not replace an existing worker.')
app = services[args.service]
if app.get('entrypoint') or app.get('command') or app.get('container_name'):
    parser.error('Review custom command/entrypoint/container_name manually.')
app.pop('build', None)
app['image'] = args.image
mounts = app.setdefault('volumes', [])
for name, target in [('parameters.yml', '/run/wallabag/parameters.yml'), ('site-credentials-key', '/run/wallabag/site-credentials-key'), ('php.ini', '/run/wallabag/php.ini')]:
    path = (args.runtime_config / name).resolve(strict=True)
    if not path.is_file() or not path.stat().st_size:
        parser.error('Required existing runtime file missing: ' + name)
    mounts.append({'type': 'bind', 'source': str(path), 'target': target, 'read_only': True})
labels = app.get('labels', {})
if isinstance(labels, list):
    labels = dict(item.split('=', 1) for item in labels)
labels['com.centurylinklabs.watchtower.enable'] = 'false'
app['labels'] = labels
# Workers share connectivity, never ingress routing or application network identity.
if app.get('network_mode'):
    parser.error('Review custom network_mode manually before adding a worker.')
worker = {key: copy.deepcopy(app[key]) for key in ['image', 'volumes', 'restart'] if key in app}
if 'networks' in app:
    worker['networks'] = list(app['networks'])
worker['labels'] = {'com.centurylinklabs.watchtower.enable': 'false', 'traefik.enable': 'false'}
worker['command'] = ['rss-worker']
worker['cpus'] = 1
worker['mem_limit'] = '256m'
worker['pids_limit'] = 128
worker['healthcheck'] = {'disable': True}
services['rss-worker'] = worker
for name, service in source['services'].items():
    if name != args.service and service != services[name]:
        raise SystemExit('Refusing dependency changes.')
descriptor = os.open(args.candidate, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
with os.fdopen(descriptor, 'w') as stream:
    yaml.safe_dump(candidate, stream, sort_keys=False)
print(json.dumps({'candidate_created': True, 'application_service': args.service, 'added_worker': 'rss-worker', 'dependency_definitions_unchanged': True, 'revision': args.revision}))
