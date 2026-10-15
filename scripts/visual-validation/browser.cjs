/* Run only in the isolated browser container sharing the test PHP namespace. */
const { chromium } = require('playwright-core');
const assert = require('node:assert/strict');
const fs = require('node:fs');

(async () => {
  assert.equal(process.env.WALLABAG_ISOLATED_VISUAL, '1');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, serviceWorkers: 'block' });
  // Never let synthetic page content initiate external browser traffic.
  await context.route('**/*', route => {
    const url = new URL(route.request().url());
    return url.origin === 'http://127.0.0.1:8080' ? route.continue() : route.abort();
  });
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(String(error)));
  const response = await page.goto('http://127.0.0.1:8080/login');
  assert.equal(response.headers()['x-wallabag-validation'], 'synthetic-network-none');
  await page.locator('input[name="_username"]').fill('admin');
  await page.locator('input[name="_password"]').fill('mypassword');
  await Promise.all([page.waitForNavigation(), page.locator('button[type="submit"], input[type="submit"]').first().click()]);
  await page.goto('http://127.0.0.1:8080/rss/subscriptions');
  await page.locator('#new-url').fill('https://feeds.example.org/rss');
  await page.locator('#new-name').fill('Visual synthetic feed');
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Subscribe', exact: true }).click()]);
  assert.equal(await page.getByRole('heading', { name: 'Visual synthetic feed', exact: true }).count(), 1);
  const section = page.locator('section').filter({ has: page.getByRole('heading', { name: 'Visual synthetic feed', exact: true }) });
  await section.locator('input[name="enabled"]').uncheck({ force: true });
  await Promise.all([page.waitForNavigation(), section.getByRole('button', { name: 'Save settings' }).click()]);
  assert.equal(await page.locator('section input[name="enabled"]').isChecked(), false);
  await page.screenshot({ path: '/evidence/rss-desktop.png', fullPage: true });
  await page.locator('#import-json').fill(JSON.stringify({ feeds: [{ url: 'https://feeds.example.org/history', name: 'Historical synthetic feed', enabled: false }], seen_items: {} }));
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Import subscriptions' }).click()]);
  assert.equal(await page.getByRole('heading', { name: 'Historical synthetic feed', exact: true }).count(), 1);
  await page.goto('http://127.0.0.1:8080/config');
  await page.locator('input[name="config[x_article_reader_enabled]"]').check({ force: true });
  await Promise.all([page.waitForNavigation(), page.locator('#config_save').click()]);
  async function capture(id, title, expected, screenshot) {
    await page.goto('http://127.0.0.1:8080/new');
    await page.locator('input[name="entry[url]"]').fill(`https://x.com/synthetic/status/${id}`);
    await Promise.all([page.waitForNavigation(), page.locator('input[name="entry[url]"]').locator('xpath=ancestor::form').locator('button[type="submit"], input[type="submit"]').first().click()]);
    await Promise.all([page.waitForNavigation(), page.getByRole('link', { name: title, exact: true }).first().click()]);
    assert.ok((await page.locator('body').innerText()).includes(expected), expected);
    await page.screenshot({ path: `/evidence/${screenshot}.png`, fullPage: true });
  }
  await capture('12345', 'Synthetic visual article', 'Synthetic closing paragraph.', 'x-article-desktop');
  await capture('12346', 'Synthetic author — X post (partial capture)', 'Partial X capture.', 'x-partial-desktop');
  await capture('12347', 'X — X post (unavailable)', 'X capture unavailable.', 'x-failure-desktop');
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('http://127.0.0.1:8080/rss/subscriptions');
  await page.screenshot({ path: '/evidence/rss-mobile.png', fullPage: true });
  assert.ok(await page.locator('body').evaluate(el => el.scrollWidth <= window.innerWidth + 1), 'mobile horizontal overflow');
  const historical = page.locator('section').filter({ has: page.getByRole('heading', { name: 'Historical synthetic feed', exact: true }) });
  await Promise.all([page.waitForNavigation(), historical.getByRole('button', { name: 'Unsubscribe' }).click()]);
  assert.equal(await page.getByRole('heading', { name: 'Historical synthetic feed', exact: true }).count(), 0);
  assert.deepEqual(errors, [], 'browser JavaScript exceptions');
  fs.writeFileSync('/evidence/browser-result.json', JSON.stringify({ status: 'pass', synthetic: true, viewport: ['1440x1000', '390x844'], checks: ['login', 'rss-create', 'rss-disable', 'rss-import', 'rss-unsubscribe', 'x-opt-in', 'x-body', 'x-partial', 'x-failure', 'no-horizontal-overflow', 'no-page-errors'] }, null, 2));
  await browser.close();
})().catch(error => { console.error(error); process.exitCode = 1; });
