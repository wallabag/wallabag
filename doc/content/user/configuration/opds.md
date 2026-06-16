---
title: OPDS
weight: 3
---

wallabag provides an [OPDS](https://opds.io/) catalog so you can browse your
saved articles and download them as EPUB files from any OPDS-compatible reader
application (for example KOReader, Foliate, Thorium, or many e-ink readers).

The catalog is **read-only**: it is for discovering and downloading articles.
Marking articles as read, starring, or tagging is not part of OPDS — use the web
interface or the API for that.

## Getting your catalog URL

The OPDS catalog uses the same personal feed token as the [RSS feeds]({{< relref "rss.md" >}}).
If you have not created one yet, open the **Feeds** tab and click
`Create your token` (you can change it later with `Reset your token`).

Your catalog URL is then:

```
https://your-wallabag-instance/opds/{username}/{token}
```

Add that URL to your OPDS reader as a new catalog/server. Because the token is
in the URL, you normally leave the username and password fields of the reader
**blank**.

## What's in the catalog

Opening the catalog URL shows a navigation feed with:

* **Digest** — the most recent unread articles bundled into a single EPUB. By
  default it contains the 20 newest unread articles. You can change this with the
  `limit` query parameter, up to a maximum of 50:

  ```
  https://your-wallabag-instance/opds/{username}/{token}/digest.epub?limit=10
  ```

* **Unread**, **Starred**, **Archive**, **All** — one shelf per article status.
  Opening a shelf lists its articles; selecting an article downloads it as an
  EPUB.

## Pagination

Like the RSS feeds, shelves are paginated and honour the same per-feed article
limit you configure on the **Feeds** tab (50 by default). You can add `/2` to a
shelf URL to jump to the second page; feeds expose `next`, `previous` and `last`
links so readers can page through automatically.

## Security note

Anyone who has your catalog URL can read and download your articles, because the
token grants access without a separate login (this is the same trade-off as the
RSS feeds). Treat the URL as a secret, and reset your token if it is ever
exposed.
