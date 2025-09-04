import { test, expect } from '@playwright/test';

const screenshotOpts = { maxDiffPixelRatio: 0.001 };

const roleContent = (role: string) => `
  <style>
    body { margin: 0; font-family: sans-serif; }
    .role { height: 200px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .member { background: #eef; }
    .artist { background: #efe; }
    .org { background: #fee; }
  </style>
  <div class="role ${role}">Role ${role}</div>
`;

['member', 'artist', 'org'].forEach(role => {
  test.describe(`${role} role`, () => {
    test(`${role} desktop`, async ({ page }) => {
      await page.setViewportSize({ width: 1280, height: 720 });
      await page.setContent(roleContent(role));
      await expect(page).toHaveScreenshot(`${role}-desktop.png`, screenshotOpts);
    });

    test(`${role} narrow`, async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 720 });
      await page.setContent(roleContent(role));
      await expect(page).toHaveScreenshot(`${role}-narrow.png`, screenshotOpts);
    });
  });
});

test('tabs first and last active', async ({ page }) => {
  await page.setViewportSize({ width: 800, height: 600 });
  await page.setContent(`
    <style>
      body { margin:0; font-family:sans-serif; }
      .tabs { display:flex; gap:4px; }
      .tab { padding:8px 12px; border:1px solid #333; }
      .tab.active { background:#ddd; }
    </style>
    <div class="tabs">
      <div class="tab active" id="tab1">Tab 1</div>
      <div class="tab" id="tab2">Tab 2</div>
      <div class="tab" id="tab3">Tab 3</div>
    </div>
  `);
  await expect(page).toHaveScreenshot('tabs-first.png', screenshotOpts);
  await page.evaluate(() => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab3')!.classList.add('active');
  });
  await expect(page).toHaveScreenshot('tabs-last.png', screenshotOpts);
});

test('local nav top and mid scroll', async ({ page }) => {
  await page.setViewportSize({ width: 800, height: 600 });
  await page.setContent(`
    <style>
      body { margin:0; font-family:sans-serif; }
      nav { position:sticky; top:0; background:#fff; border-bottom:1px solid #ccc; display:flex; }
      nav a { padding:10px; text-decoration:none; color:#333; }
      nav a.active { color:red; }
      section { height:600px; padding-top:40px; }
    </style>
    <nav>
      <a href="#one" class="active" id="ln1">One</a>
      <a href="#two" id="ln2">Two</a>
    </nav>
    <section id="one">Section One</section>
    <section id="two">Section Two</section>
  `);
  await expect(page).toHaveScreenshot('local-nav-top.png', screenshotOpts);
  await page.evaluate(() => window.scrollTo(0, document.getElementById('two')!.offsetTop));
  await page.evaluate(() => {
    document.getElementById('ln1')!.classList.remove('active');
    document.getElementById('ln2')!.classList.add('active');
  });
  await expect(page).toHaveScreenshot('local-nav-mid.png', screenshotOpts);
});

test('role matrix horizontal scroll', async ({ page }) => {
  await page.setViewportSize({ width: 800, height: 600 });
  const cols = Array.from({length: 10}, (_,i) => `<td>Col${i+1}</td>`).join('');
  await page.setContent(`
    <style>
      body { margin:0; font-family:sans-serif; }
      .matrix { width:400px; overflow-x:scroll; border:1px solid #333; }
      table { border-collapse:collapse; }
      td { border:1px solid #666; padding:8px; min-width:120px; }
    </style>
    <div class="matrix"><table><tr>${cols}</tr></table></div>
  `);
  const matrix = page.locator('.matrix');
  await matrix.evaluate(e => e.scrollLeft = 400);
  await expect(page).toHaveScreenshot('role-matrix-scrolled.png', screenshotOpts);
});

