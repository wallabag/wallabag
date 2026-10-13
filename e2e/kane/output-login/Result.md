---
test: ../login_test.md
status: passed
started: 2026-10-13T15:40:19.350Z
duration_s: 115
session_id: 2f636756-1f2a-47df-8c77-8e8ab7ab492b
---

# Log in to wallabag — Result

## Wrong password is rejected ✓ passed (48.8s)
md5: 970b81a605cf7ace30c2a9c37e329ce0
Open http://127.0.0.1:8000/login.
Type "wallabag" into the username field and "not-the-password" into the password field, then click the "Log in" button.
Wait for the page to finish loading.
Verify the URL contains "/login" and the "Log in" button is still shown.

## Correct credentials log in ✓ passed (35.8s)
md5: 7e940398fc489f9534cd24ceac8d78a3
Open http://127.0.0.1:8000/login.
Type "wallabag" into the username field and "wallabag" into the password field, then click the "Log in" button.
Wait for the page to finish loading.
Verify the URL does not contain "/login".

## Log out ✓ passed (28.7s)
md5: 7b4d8507f2930526a90d15df5aa5fe52
Open http://127.0.0.1:8000/logout.
Wait for the page to finish loading.
Verify the URL contains "/login" and the "Log in" button is shown.
