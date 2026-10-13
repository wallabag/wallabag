---
mode: testing
url: http://127.0.0.1:8000/login
max_steps: 20
tags: [smoke, auth]
---

# Log in to wallabag

## Wrong password is rejected
Open http://127.0.0.1:8000/login.
Type "wallabag" into the username field and "not-the-password" into the password field, then click the "Log in" button.
Wait for the page to finish loading.
Verify the URL contains "/login" and the "Log in" button is still shown.

## Correct credentials log in
Open http://127.0.0.1:8000/login.
Type "wallabag" into the username field and "wallabag" into the password field, then click the "Log in" button.
Wait for the page to finish loading.
Verify the URL does not contain "/login".

## Log out
Open http://127.0.0.1:8000/logout.
Wait for the page to finish loading.
Verify the URL contains "/login" and the "Log in" button is shown.
