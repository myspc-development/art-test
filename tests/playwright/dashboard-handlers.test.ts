import { test, expect } from '@playwright/test';

// Ensure tabs update active class when clicked
test('tab click updates active state', async ({ page }) => {
  await page.setContent(`
    <style>
      .tab { padding:8px 12px; }
      .tab.active { color: red; }
    </style>
    <div class="tabs">
      <button class="tab active" id="tab1">Tab 1</button>
      <button class="tab" id="tab2">Tab 2</button>
    </div>
    <script>
      document.querySelectorAll('.tab').forEach(btn => {
        btn.addEventListener('click', () => {
          document.querySelector('.tab.active')?.classList.remove('active');
          btn.classList.add('active');
        });
      });
    </script>
  `);

  await page.click('#tab2');
  await expect(page.locator('#tab2')).toHaveClass(/active/);
  await expect(page.locator('#tab1')).not.toHaveClass(/active/);
});

// Ensure local nav updates active link on scroll
test('scroll updates local nav active link', async ({ page }) => {
  await page.setViewportSize({ width: 800, height: 600 });
  await page.setContent(`
    <style>
      body { margin:0; font-family:sans-serif; }
      nav { position:sticky; top:0; background:#fff; border-bottom:1px solid #ccc; display:flex; gap:4px; }
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
    <script>
      const links = document.querySelectorAll('nav a');
      const sections = Array.from(links).map(l => document.querySelector(l.getAttribute('href')));
      window.addEventListener('scroll', () => {
        sections.forEach((sec, i) => {
          const link = links[i];
          const rect = sec.getBoundingClientRect();
          if (rect.top <= 0 && rect.bottom > 0) {
            document.querySelector('nav a.active')?.classList.remove('active');
            link.classList.add('active');
          }
        });
      });
    </script>
  `);

  await page.evaluate(() => window.scrollTo(0, document.getElementById('two')!.offsetTop));
  await expect(page.locator('#ln2')).toHaveClass(/active/);
  await expect(page.locator('#ln1')).not.toHaveClass(/active/);
});

