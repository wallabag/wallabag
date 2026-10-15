#!/usr/bin/env python3
"""Cache public composer.lock archives for offline, credential-free validation."""

import concurrent.futures
import hashlib
import json
import re
import urllib.request
from pathlib import Path

root = Path(__file__).resolve().parents[1]
locks = [
    json.loads((root / path).read_text())
    for path in ["composer.lock", "docker/rss-tests/phpunit/composer.lock"]
]
packages = [
    p for lock in locks for p in lock["packages"] + lock.get("packages-dev", [])
]
cache = root / ".validation/composer-cache/files"


def one(p):
    d = p["dist"]
    url = d["url"]
    out = cache / p["name"] / (hashlib.sha1(url.encode()).hexdigest() + ".zip")
    if out.exists():
        return
    m = re.match(r"https://api.github.com/repos/([^/]+/[^/]+)/zipball/(.+)", url)
    fetch = "https://codeload.github.com/" + m[1] + "/legacy.zip/" + m[2]
    for attempt in range(3):
        try:
            with urllib.request.urlopen(fetch, timeout=45) as r:
                data = r.read()
            out.parent.mkdir(parents=True, exist_ok=True)
            out.write_bytes(data)
            return
        except Exception:
            if attempt == 2:
                raise


with concurrent.futures.ThreadPoolExecutor(max_workers=4) as pool:
    for i, _ in enumerate(pool.map(one, packages), 1):
        if i % 40 == 0:
            print("Cached", i, flush=True)
print("Cached all pinned dependency archives", flush=True)
