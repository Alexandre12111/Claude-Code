const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 900, height: 1200 }, deviceScaleFactor: 2 });
  await p.goto('file://' + process.argv[2]); await p.waitForTimeout(500);
  const o = await p.evaluate(() => { const pg = document.querySelector('.page'); const b = document.querySelector('.body');
    return { page: pg.scrollHeight, client: pg.clientHeight, bodyScroll: b.scrollHeight, bodyClient: b.clientHeight }; });
  console.log(JSON.stringify(o));
  await p.locator('.page').screenshot({ path: process.argv[3] });
  await p.emulateMedia({ media: 'print' });
  await p.pdf({ path: process.argv[4], format: 'A4', printBackground: true, margin: { top: 0, bottom: 0, left: 0, right: 0 } });
  await b.close();
})();
