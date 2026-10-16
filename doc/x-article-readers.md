# Public X Article extraction: evaluation and evidence

Evaluated 2026-10-15, using only the explicitly supplied public post URL. No X login, session cookies, private credentials, paid subscription, anti-bot bypass, private Wallabag data, or production data was used.

## Options

| Method | Access/cost | Completeness and reliability | Privacy/attribution |
|---|---|---|---|
| [Official X Post Lookup](https://docs.x.com/x-api/posts/lookup/introduction) and [Articles documentation](https://docs.x.com/x-api/articles/introduction) | Approved app/token; current [pay-per-use platform](https://docs.x.com/overview). Not invoked. | Supported official access is preferable where available, but it requires an access/billing decision. Article creation/publishing documentation does not establish unauthenticated body retrieval. | Sends request through the configured X app; no credentials created here. |
| Public X oEmbed | No credentials/payment used | Tested: returns only a t.co link for this example. Kept as explicitly partial fallback, never a body capture. | Sends post URL and server IP to X. Safe text/links, original attribution retained. |
| [FxTwitter/FxEmbed public API](https://docs.fxembed.com/api/twitter/operations/2statusid/) | Public documented endpoint; no signup, key or payment in our test. [Open-source project](https://github.com/FxEmbed/FxEmbed). | **Selected.** Returned structured article blocks, media references, title, author and date. Third-party upstream/schema changes and outages remain possible; no SLA or independent guarantee of X completeness. Missing body, mismatched IDs, protected/tombstone responses and unsupported blocks cannot become a silent full capture. | Provider/CDN receive public post ID, server IP and request metadata. No private Wallabag content, OAuth token, cookies or user identity is sent. Per-user opt-in defaults off. Retention is not independently verified; direct use avoids adding Xtracticle as a second processor. |
| [Xtracticle Markdown API](https://xtracticle.com/) | Advertised free/no login; tested without payment/key | Returned a 19,041-byte Markdown document with matching title, opening and closing sections. It uses FxTwitter underneath, so this is a conversion cross-check, **not independent confirmation**. | [Privacy policy](https://xtracticle.com/privacy) says edge cache up to one hour plus fallback up to seven days; API logs include post ID, request metadata and IP processing. Preserve author/source links. Not used by the implementation. |
| [Sorsa article API](https://api.sorsa.io/blog/x-articles-api) | Requires a service key; advertises a free request allowance and paid plans. Not signed up or invoked. | Provider claims a dedicated article-body endpoint. Unverified here; not needed after the public FxTwitter result. | Additional processor and credential/access decision; no account or credentials created. |
| [Jina Reader](https://jina.ai/reader/) | Generic public URL-to-text service; account/limits vary | Considered, but its generic documentation does not establish X Article completeness. Not selected or invoked after a structured public method worked. | Would add another processor; unnecessary for this implementation. |

## Example: actual results

Post: `https://x.com/poteto/status/2069824386283319343`

- X oEmbed: HTTP 200, author lauren (@poteto), short link only. Its public 301 destination is `https://x.com/i/article/2066965659259686912`; that direct article page returned HTTP 404/login markup without the body.
- FxTwitter legacy and documented v2 endpoints: HTTP 200. Implemented endpoint: `https://api.fxtwitter.com/2/status/2069824386283319343`.
- Returned title: **Loops You Can Trust**. Article ID: `2066965659259686912`.
- Returned structure: **79 blocks**, including **69 nonempty text blocks**, 10 atomic media/post references, 6 section headings, 5 ordered-list items and one quotation. Total provider block text: **16,174 Unicode characters**.
- Offline replay of the actual v2 receipt through the real ContentProxy/Graby sanitizer verified **every nonempty block survives**, including the closing paragraph. Stored status: **200** for this verified text-and-links snapshot. Images, cover and embedded posts are attributed external links, not downloaded archives.
- Receipt SHA-256: `b4fe5c4a1619c55187a2f692a09b0c7853077b241ab850e66e2045859281213d`. Dynamic counters may change future response hashes.
- Closing-paragraph SHA-256: `d5c291ad6fdfb954f1652d4707635bf4fe8f1d5cf3c9eb677aa5b101e5d001c1`.

This verifies that the public reader delivered substantial structured body text and that our application did not truncate it. Because the original X page was unavailable to the unauthenticated client, it does **not** independently establish byte-for-byte identity with X's current rendered article, future availability, or completeness of embedded media. Xtracticle shares the same upstream.

## Product behavior

Enable the checkbox in **Settings** to permit public article reads through FxTwitter. The label discloses the public post ID and server IP sharing. It is disabled by default and owned per user. The authenticated API equivalent is:

`PATCH /api/config.json` with `{"x_article_reader_enabled":true}`.

Normal new-entry UI/API saves, reloads and RSS imports then attempt the structured reader for supported X post URLs. A complete supported block conversion stores the article title, attribution, publication date, body and media/source links. Unknown/unavailable embeds mark the result **partial** (206). Reader failure falls back to a clearly partial public embed; failure of both paths creates an explicit unavailable bookmark (502). Failed reloads retain earlier captured content.

Reproduce the public receipt explicitly, then replay it locally:

```sh
python3 scripts/capture-public-x-evidence.py https://x.com/poteto/status/2069824386283319343
# After local-validation setup/test, inside its network-none PHP runtime:
php scripts/verify-x-article.php .validation/x-research/2069824386283319343.json 2069824386283319343
```

Research responses and full article text stay in ignored `.validation/`; only the evaluation and non-content evidence are checked in.

## Inline Article images

The image correction renders cover and body raster images only from exact HTTPS
`pbs.twimg.com/media/` URLs with fixed filename characters and raster extensions.
It escapes captions and attributes, sets `referrerpolicy="no-referrer"`, and
retains source links. Images load in the reader’s browser from X’s media CDN;
image bytes are **not archived locally**. The general server-side image downloader
is skipped for structured X Articles, including when that downloader is enabled
for ordinary entries. Unsupported media remains visibly partial; arbitrary media
URLs, SVG, credentials, ports, query strings and alternate hosts are rejected.

PDF, EPUB and MOBI exports convert Article image elements to their caption text
and preserve surrounding source links; EPUB does not fetch an Article preview
cover. Binary exporters receive a restricted markup allowlist without media,
CSS or resource attributes. JSON exports and stored content retain browser image
references unchanged. This avoids invoking legacy exporter media fetchers.

## Saved-body notices

Future structured Article saves and successful refreshes omit the generated
provider disclosure and general partial-snapshot preamble. Capture provenance and
HTTP status remain unchanged: unsupported content is still `206`, and failed
refreshes retain an existing Article with failure status `502`. Per-item unavailable
media placeholders and source attribution remain. This changes generation only;
it does not strip matching source prose or rewrite existing saved entries.
