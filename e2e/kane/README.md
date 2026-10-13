# End-to-end (e2e) tests for web

Browser end-to-end tests for wallabag, authored with [KaneCLI](https://www.testmuai.com/kane-cli/) by TestMu AI. Each test is a plain-English `_test.md` file; KaneCLI runs it in a real Chrome browser and records each step so later runs replay without re-authoring.

| Test | What it covers |
|---|---|
| `login_test.md` | A wrong password is rejected, the default user can log in, and logging out returns to the login page |
| `save_article_test.md` | Saving `https://example.com/` through the bookmarklet URL adds it to the unread list |

The tests expect a development instance at `http://127.0.0.1:8000` with the default `wallabag` / `wallabag` user created by `wallabag:install`.

## Run the tests

Start wallabag locally first (see [CONTRIBUTING.md](../../.github/CONTRIBUTING.md)):

```bash
make dev-docker-up
make dev-setup
```

### With KaneCLI

```bash
npm install -g @testmuai/kane-cli
kane-cli login
kane-cli testmd run e2e/kane/login_test.md --agent
kane-cli testmd run e2e/kane/save_article_test.md --agent
```

### With the exported Playwright code

Each test also has an exported Playwright (JavaScript) version under `output-<test>/playwright-javascript-code/`:

```bash
cd e2e/kane/output-login/playwright-javascript-code
npm install
npx playwright install chromium
node test.js
```

Copy `.env.example` to `.env` and fill in your TestMu AI (LambdaTest) credentials first.

## Editing a test

Edit the step text in the `_test.md` file and run it again with `kane-cli testmd run`. Changed steps (and the steps after them) are re-recorded; unchanged steps replay from `output-<test>/`.
