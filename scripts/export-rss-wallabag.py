#!/usr/bin/env python3
"""Export an offline SQLite backup of rss-wallabag; never opens the source for writes."""

import argparse
import json
from pathlib import Path
import sqlite3

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument(
    "database",
    type=Path,
    help="Offline consistent SQLite backup, not a running WAL database",
)
args = parser.parse_args()
path = args.database.resolve(strict=True)
with sqlite3.connect(path.as_uri() + "?mode=ro", uri=True) as db:
    db.row_factory = sqlite3.Row
    feeds, seen = [], {}
    for row in db.execute("SELECT * FROM feeds ORDER BY position, id"):
        feeds.append(
            dict(
                url=row["url"],
                name=row["name"],
                tags=json.loads(row["tags_json"]),
                max_items=row["max_items"],
                enabled=bool(row["enabled"]),
            )
        )
        seen[row["url"]] = [
            dict(url=item["url"])
            for item in db.execute(
                "SELECT url FROM seen_items WHERE feed_id = ? ORDER BY id", (row["id"],)
            )
        ]
print(json.dumps(dict(feeds=feeds, seen_items=seen), ensure_ascii=False))
