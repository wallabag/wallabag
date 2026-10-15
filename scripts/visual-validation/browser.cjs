/* Run only in the isolated browser container sharing the test PHP namespace. */
const { chromium } = require('playwright-core');
const assert = require('node:assert/strict');
const fs = require('node:fs');
let browser;
let page;

(async () => {
  assert.equal(process.env.WALLABAG_ISOLATED_VISUAL, '1');
  browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, serviceWorkers: 'block' });
  // Never let synthetic page content initiate external browser traffic.
  await context.route('**/*', route => {
    const url = new URL(route.request().url());
    return url.origin === 'http://127.0.0.1:8080' ? route.continue() : route.abort();
  });
  page = await context.newPage();
  const errors = [];
  page.on('pageerror', error => { errors.push(String(error)); console.error('Page error:', String(error)); });
  page.on('console', message => { if (message.type() === 'error') console.error('Browser console:', message.text()); });
  page.on('requestfailed', request => console.error('Request failed:', request.url(), request.failure()));
  page.on('response', response => {
    if (response.status() >= 400) console.error('HTTP', response.status(), response.url());
  });
  const response = await page.goto('http://127.0.0.1:8080/login');
  assert.equal(response.headers()['x-wallabag-validation'], 'synthetic-network-none');
  await page.locator('input[name="_username"]').fill('admin');
  await page.locator('input[name="_password"]').fill('mypassword');
  await Promise.all([page.waitForNavigation(), page.locator('button[type="submit"], input[type="submit"]').first().click()]);
  await page.goto('http://127.0.0.1:8080/rss/subscriptions');
  fs.writeFileSync('/evidence/rss-rendered.html', await page.content());
  console.log('Rendered CSS:', await page.evaluate(() => ({ sheets: [...document.styleSheets].map(s => s.href), font: getComputedStyle(document.body).fontFamily })));
  assert.ok(await page.locator('link[rel="stylesheet"][href$="material.css"]').count(), 'production stylesheet is linked');
  await page.locator('#new-url').fill('https://feeds.example.org/rss');
  await page.locator('#new-name').fill('Visual synthetic feed');
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Subscribe', exact: true }).click()]);
  assert.equal(await page.getByRole('heading', { name: 'Visual synthetic feed', exact: true }).count(), 1);
  const section = page.locator('section').filter({ has: page.getByRole('heading', { name: 'Visual synthetic feed', exact: true }) });
  assert.equal(await section.locator('input[name="enabled"]').isChecked(), true);
  await section.locator('label[for^="enabled-"]').click();
  await Promise.all([page.waitForNavigation(), section.getByRole('button', { name: 'Save settings' }).click()]);
  assert.equal(await page.locator('section input[name="enabled"]').isChecked(), false);
  await page.screenshot({ path: '/evidence/rss-desktop.png', fullPage: true });
  await page.locator('#import-json').fill(JSON.stringify({ feeds: [{ url: 'https://feeds.example.org/history', name: 'Historical synthetic feed', enabled: false }], seen_items: {} }));
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Import subscriptions' }).click()]);
  assert.equal(await page.getByRole('heading', { name: 'Historical synthetic feed', exact: true }).count(), 1);
  await page.goto('http://127.0.0.1:8080/config');
  await page.locator('label[for="config_x_article_reader_enabled"]').click();
  assert.equal(await page.locator('input[name="config[x_article_reader_enabled]"]').isChecked(), true);
  await Promise.all([page.waitForNavigation(), page.locator('#config_save').click()]);
  await page.goto('http://127.0.0.1:8080/grabbers');
  assert.ok((await page.locator('body').innerText()).includes('Medium public stories'));
  await page.locator('label[for="grabber-enabled-medium"]').click();
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Save medium settings' }).click()]);
  assert.equal(await page.locator('#grabber-enabled-medium').isChecked(), false);
  await page.locator('label[for="grabber-enabled-medium"]').click();
  await Promise.all([page.waitForNavigation(), page.getByRole('button', { name: 'Save medium settings' }).click()]);
  assert.equal(await page.locator('#grabber-enabled-medium').isChecked(), true);
  await page.screenshot({ path: '/evidence/special-grabbers-desktop.png', fullPage: true });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.screenshot({ path: '/evidence/special-grabbers-mobile.png', fullPage: true });
  assert.ok(await page.locator('body').evaluate(el => el.scrollWidth <= window.innerWidth + 1), 'grabbers mobile horizontal overflow');
  await page.setViewportSize({ width: 1440, height: 1000 });
  async function captureUrl(url, title, expected, screenshot) {
    await page.goto('http://127.0.0.1:8080/new');
    if (!(await page.locator('input[name="entry[url]"]').isVisible())) await page.locator('#nav-btn-add').click();
    await page.locator('input[name="entry[url]"]').fill(url);
    await Promise.all([page.waitForNavigation(), page.locator('input[name="entry[url]"]').locator('xpath=ancestor::form').locator('button[type="submit"], input[type="submit"]').first().click()]);
    await Promise.all([page.waitForNavigation(), page.getByRole('link', { name: title, exact: true }).first().click()]);
    assert.ok((await page.locator('body').innerText()).includes(expected), expected);
    await page.screenshot({ path: `/evidence/${screenshot}.png`, fullPage: true });
  }
  const capture = (id, title, expected, screenshot) => captureUrl(`https://x.com/synthetic/status/${id}`, title, expected, screenshot);
  await captureUrl('https://medium.com/@author/story-abcdef012345', 'Synthetic Medium story', 'Synthetic Medium ending.', 'medium-public-desktop');
  await capture('12345', 'Synthetic visual article', 'Synthetic closing paragraph.', 'x-article-desktop');
  await capture('12346', 'Synthetic author — X post (partial capture)', 'Partial X capture.', 'x-partial-desktop');
  await capture('12347', 'X — X post (unavailable)', 'X capture unavailable.', 'x-failure-desktop');
  if (process.env.WALLABAG_PUBLIC_X_RECEIPT === '1') {
    await capture('2069824386283319343', 'Loops You Can Trust', 'Article text captured from public X data', 'x-public-article-desktop');
    await page.screenshot({ path: '/evidence/x-public-article-preview.png', clip: { x: 0, y: 0, width: 1440, height: 500 } });
    const normalize = text => text.replace(/\s+/gu, ' ').trim();
    const rendered = normalize(await page.locator('body').innerText());
    const blocks = JSON.parse(fs.readFileSync('/evidence/public-article-blocks.json', 'utf8'));
    for (const block of blocks) if (normalize(block.text)) assert.ok(rendered.includes(normalize(block.text)), 'Public source block is visible in the reading view');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.screenshot({ path: '/evidence/x-public-article-mobile.png', fullPage: true });
  }
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('http://127.0.0.1:8080/rss/subscriptions');
  await page.screenshot({ path: '/evidence/rss-mobile.png', fullPage: true });
  assert.ok(await page.locator('body').evaluate(el => el.scrollWidth <= window.innerWidth + 1), 'mobile horizontal overflow');
  const historical = page.locator('section').filter({ has: page.getByRole('heading', { name: 'Historical synthetic feed', exact: true }) });
  await Promise.all([page.waitForNavigation(), historical.getByRole('button', { name: 'Unsubscribe' }).click()]);
  assert.equal(await page.getByRole('heading', { name: 'Historical synthetic feed', exact: true }).count(), 0);
  assert.deepEqual(errors, [], 'browser JavaScript exceptions');
  fs.writeFileSync('/evidence/browser-result.json', JSON.stringify({ status: 'pass', synthetic_account_and_feeds: true, public_article_receipt: process.env.WALLABAG_PUBLIC_X_RECEIPT === '1', viewport: ['1440x1000', '390x844'], checks: ['login', 'rss-create', 'rss-disable', 'rss-import', 'rss-unsubscribe', 'x-opt-in', 'x-body', 'x-partial', 'x-failure', 'medium-public-only', 'grabber-disable-enable', 'no-horizontal-overflow', 'no-page-errors'] }, null, 2));
  await browser.close();
})().catch(async error => {
  console.error(error);
  if (page) {
    fs.writeFileSync('/evidence/failure-rendered.html', await page.content());
    await page.screenshot({ path: '/evidence/failure.png', fullPage: true }).catch(() => {});
  }
  if (browser) await browser.close();
  process.exitCode = 1;
});
