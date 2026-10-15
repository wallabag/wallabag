#!/usr/bin/env python3
"""Explicit opt-in: send one public X post ID to FxTwitter; save a local replay receipt."""

import hashlib
import json
from pathlib import Path
import re
import sys
import urllib.parse
import urllib.request

if len(sys.argv) != 2:
    raise SystemExit(
        "Usage: python3 scripts/capture-public-x-evidence.py https://x.com/user/status/ID"
    )
url = urllib.parse.urlsplit(sys.argv[1])
match = re.fullmatch(
    r"/(?:[A-Za-z0-9_]{1,15}|i/web)/status/(\d{1,25})(?:/[^?#]*)?", url.path
)
if (
    url.scheme not in ["http", "https"]
    or url.hostname
    not in [
        "x.com",
        "www.x.com",
        "twitter.com",
        "www.twitter.com",
        "mobile.twitter.com",
    ]
    or url.username
    or url.password
    or url.port
    or not match
):
    raise SystemExit("Expected a public X post URL without credentials.")
post_id = match[1]
endpoint = "https://api.fxtwitter.com/2/status/" + post_id
with urllib.request.urlopen(endpoint, timeout=20) as response:
    raw = response.read(2 * 1024 * 1024 + 1)
    if len(raw) > 2 * 1024 * 1024:
        raise SystemExit("Response exceeds 2 MiB.")
data = json.loads(raw)
post = data.get("status", {})
if (
    data.get("code") != 200
    or post.get("id") != post_id
    or post.get("type") == "tombstone"
    or post.get("author", {}).get("protected")
):
    raise SystemExit(
        "Public provider did not return the requested available public post."
    )
article = post.get("article") or {}
blocks = article.get("content", {}).get("blocks", [])
if not blocks:
    raise SystemExit("No article body; a preview is not a full capture.")
root = Path(__file__).resolve().parents[1]
out = root / ".validation/x-research"
out.mkdir(parents=True, exist_ok=True)
(out / (post_id + ".json")).write_bytes(raw)
report = dict(
    endpoint=endpoint,
    post_id=post_id,
    article_id=article.get("id"),
    title=article.get("title"),
    blocks=len(blocks),
    text_characters=sum(len(b.get("text", "")) for b in blocks),
    sha256=hashlib.sha256(raw).hexdigest(),
)
(out / (post_id + "-receipt.json")).write_text(json.dumps(report, indent=2) + "\n")
print(json.dumps(report))
