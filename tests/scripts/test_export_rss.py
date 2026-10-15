"""Offline exporter regression; uses synthetic data in a temporary directory."""

import hashlib
import json
from pathlib import Path
import sqlite3
import subprocess
import sys
import tempfile
import unittest


class ExportRssTest(unittest.TestCase):
    def test_preserves_subscriptions_and_seen_without_source_writes(self):
        with tempfile.TemporaryDirectory(prefix="wallabag-rss-export-") as tmp:
            database = Path(tmp) / "offline.sqlite"
            with sqlite3.connect(database) as db:
                db.executescript("""
                    CREATE TABLE feeds (id INTEGER PRIMARY KEY, url TEXT, name TEXT,
                        tags_json TEXT, max_items INTEGER, enabled INTEGER, position INTEGER);
                    CREATE TABLE seen_items (id INTEGER PRIMARY KEY, feed_id INTEGER, url TEXT);
                    INSERT INTO feeds VALUES (1, 'https://example.com/feed', 'Example', '["Tech"]', 3, 0, 0);
                    INSERT INTO seen_items VALUES (1, 1, 'https://example.com/one#fragment');
                """)
            before = hashlib.sha256(database.read_bytes()).hexdigest()
            script = (
                Path(__file__).resolve().parents[2] / "scripts/export-rss-wallabag.py"
            )
            result = subprocess.run(
                [sys.executable, str(script), str(database)],
                check=True,
                capture_output=True,
                text=True,
            )
            data = json.loads(result.stdout)
            self.assertEqual(data["feeds"][0]["tags"], ["Tech"])
            self.assertEqual(data["feeds"][0]["max_items"], 3)
            self.assertFalse(data["feeds"][0]["enabled"])
            self.assertEqual(
                data["seen_items"]["https://example.com/feed"],
                [{"url": "https://example.com/one#fragment"}],
            )
            self.assertEqual(before, hashlib.sha256(database.read_bytes()).hexdigest())


if __name__ == "__main__":
    unittest.main()
