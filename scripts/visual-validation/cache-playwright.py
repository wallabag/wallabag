#!/usr/bin/env python3
"""Cache the public pinned browser client, verifying npm's SHA-512 integrity."""

import base64
import hashlib
import io
import json
from pathlib import Path
import tarfile
import urllib.request

ROOT = Path(__file__).resolve().parents[2]
DEST = ROOT / '.validation/playwright/node_modules/playwright-core'
if DEST.exists():
    raise SystemExit('Own Playwright cache already exists; inspect before replacing.')
with urllib.request.urlopen('https://registry.npmjs.org/playwright-core/1.63.0', timeout=30) as response:
    metadata = json.load(response)
url = metadata['dist']['tarball']
if url != 'https://registry.npmjs.org/playwright-core/-/playwright-core-1.63.0.tgz':
    raise SystemExit('Unexpected package URL.')
with urllib.request.urlopen(url, timeout=60) as response:
    payload = response.read(20 * 1024 * 1024 + 1)
integrity = 'sha512-' + base64.b64encode(hashlib.sha512(payload).digest()).decode()
if len(payload) > 20 * 1024 * 1024 or integrity != metadata['dist']['integrity']:
    raise SystemExit('Package integrity or size check failed.')
DEST.mkdir(parents=True)
with tarfile.open(fileobj=io.BytesIO(payload), mode='r:gz') as archive:
    for member in archive.getmembers():
        parts = Path(member.name).parts
        if not parts or parts[0] != 'package' or '..' in parts or member.issym() or member.islnk():
            raise SystemExit('Unsafe archive member.')
        target = DEST.joinpath(*parts[1:])
        if member.isdir():
            target.mkdir(parents=True, exist_ok=True)
        elif member.isfile():
            target.parent.mkdir(parents=True, exist_ok=True)
            with archive.extractfile(member) as source:
                target.write_bytes(source.read())
        else:
            raise SystemExit('Unsupported archive member.')
(DEST.parents[1] / 'package-receipt.json').write_text(json.dumps({
    'package': 'playwright-core', 'version': '1.63.0', 'integrity': integrity,
    'tarball_sha256': hashlib.sha256(payload).hexdigest(),
}, indent=2))
print('Pinned Playwright client cached; integrity verified.')
