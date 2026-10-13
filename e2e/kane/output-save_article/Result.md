---
test: ../save_article_test.md
status: passed
started: 2026-10-13T15:43:44.932Z
duration_s: 71
session_id: fe857bd4-52de-4a27-8bd3-1aad5b236c07
---

# Save an article with the bookmarklet — Result

## Log in ✓ passed (44.8s)
md5: 7e940398fc489f9534cd24ceac8d78a3
Open http://127.0.0.1:8000/login.
Type "wallabag" into the username field and "wallabag" into the password field, then click the "Log in" button.
Wait for the page to finish loading.
Verify the URL does not contain "/login".

## Save a new article ✓ passed (22.2s)
md5: a923b51c5cdca33e45bad490a30618a1
Open http://127.0.0.1:8000/bookmarklet?url=https://example.com/ (this is the URL the wallabag bookmarklet opens to save a page).
Wait for the page to finish loading.
Verify the unread list contains an entry titled "example.com".
