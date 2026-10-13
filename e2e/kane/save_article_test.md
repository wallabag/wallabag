---
mode: testing
url: http://127.0.0.1:8000/login
max_steps: 25
tags: [smoke, entries]
---

# Save an article with the bookmarklet

## Log in
Open http://127.0.0.1:8000/login.
Type "wallabag" into the username field and "wallabag" into the password field, then click the "Log in" button.
Wait for the page to finish loading.
Verify the URL does not contain "/login".

## Save a new article
Open http://127.0.0.1:8000/bookmarklet?url=https://example.com/ (this is the URL the wallabag bookmarklet opens to save a page).
Wait for the page to finish loading.
Verify the unread list contains an entry titled "example.com".
