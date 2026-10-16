/* Opt-in static rendering of an already cached public Article, never production data. */
const { chromium } = require('../.validation/playwright/node_modules/playwright-core');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
(async () => {
  assert.equal(process.env.WALLABAG_PUBLIC_IMAGE_VERIFY, '1');
  const html = fs.readFileSync(path.join(__dirname, '../.validation/real-article.html'), 'utf8');
  const browser = await chromium.launch({ headless: true });
  try {
    const context = await browser.newContext({ serviceWorkers: 'block' });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(String(error)));
    // First parse without allowing network requests, then allowlist exact initial
    // raster references. Browser redirects are not a tested security boundary.
    let allowed = new Set();
    await context.route('**/*', route => allowed.has(route.request().url()) && route.request().resourceType() === 'image' ? route.continue() : route.abort());
    await page.setContent('<meta charset="utf-8"><style>img{max-width:100%;height:auto}</style>' + html);
    const sources = await page.locator('img').evaluateAll(images => images.map(img => img.getAttribute('src')));
    assert.ok(sources.length > 0);
    for (const src of sources) assert.match(src, /^https:\/\/pbs\.twimg\.com\/media\/[A-Za-z0-9_-]+\.(jpg|jpeg|png|gif|webp)$/);
    allowed = new Set(sources);
    await page.setContent('<meta charset="utf-8"><style>img{max-width:100%;height:auto}</style>' + html);
    const results = [];
    for (const viewport of [{ width: 1440, height: 1000 }, { width: 390, height: 844 }]) {
      await page.setViewportSize(viewport);
      await page.locator('img').evaluateAll(images => images.forEach(img => { img.loading = 'eager'; }));
      await page.waitForFunction(() => [...document.images].every(img => img.complete && img.naturalWidth > 0 && img.naturalHeight > 0), { timeout: 30000 });
      const dimensions = await page.locator('img').evaluateAll(images => images.map(img => ({ width: img.naturalWidth, height: img.naturalHeight, referrerPolicy: img.referrerPolicy })));
      assert.ok(dimensions.every(image => image.referrerPolicy === 'no-referrer'));
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > innerWidth);
      assert.equal(overflow, false);
      results.push({ viewport, loaded_images: dimensions.length, dimensions });
    }
    assert.deepEqual(errors, []);
    const report = { scope: 'static sanitized cached public Article; not production UI', sources: sources.length, unique_sources: allowed.size, browser_images_loaded: true, locally_archived_image_bytes: false, page_errors: errors.length, results };
    fs.writeFileSync(path.join(__dirname, '../.validation/followup-image-browser.json'), JSON.stringify(report, null, 2) + '\n');
    console.log(JSON.stringify(report));
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
