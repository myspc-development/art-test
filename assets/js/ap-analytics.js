import { apiFetch, __, on } from './ap-core.js';

/**
 * Simple analytics dashboard for organizations.
 */
export default async function render(container) {
  const ranges = [7, 30, 90];
  const rangeWrap = document.createElement('div');
  const customStart = document.createElement('input');
  customStart.type = 'date';
  const customEnd = document.createElement('input');
  customEnd.type = 'date';
  const applyCustom = document.createElement('button');
  applyCustom.textContent = __('Apply');

  let currentRange = 7;
  ranges.forEach((r) => {
    const b = document.createElement('button');
    b.textContent = `${r}`;
    b.addEventListener('click', () => {
      currentRange = r;
      load();
    });
    rangeWrap.appendChild(b);
  });
  rangeWrap.append(customStart, customEnd, applyCustom);
  applyCustom.addEventListener('click', () => {
    currentRange = `${customStart.value},${customEnd.value}`;
    load();
  });

  const tiles = document.createElement('div');
  tiles.className = 'grid grid-2';
  const canvas = document.createElement('canvas');
  canvas.width = 400; canvas.height = 200;
  const table = document.createElement('table');
  table.className = 'ap-table';
  const tbody = document.createElement('tbody');
  table.appendChild(tbody);
  table.setAttribute('aria-describedby', 'ap-analytics-chart');
  canvas.id = 'ap-analytics-chart';

  const barCanvas = document.createElement('canvas');
  barCanvas.width = 400; barCanvas.height = 200;
  barCanvas.id = 'ap-analytics-top';
  const topTable = document.createElement('table');
  topTable.className = 'ap-table';
  const topBody = document.createElement('tbody');
  topTable.appendChild(topBody);
  topTable.setAttribute('aria-describedby', 'ap-analytics-top');

  container.append(rangeWrap, tiles, canvas, table, barCanvas, topTable);

  on('rsvps:changed', () => {
    sessionStorage.removeItem(cacheKey(currentRange));
    load();
  });

  async function load() {
    tiles.textContent = '';
    tbody.textContent = '';
    const data = await apiFetch(`/ap/v1/analytics/events/summary?range=${currentRange}`, {
      cacheKey: cacheKey(currentRange),
      ttlMs: 600000,
    });
    if (!data) return;
    renderTiles(data);
    renderChart(data.trend || []);
    renderTable(data.trend || []);
    renderBar(data.top_events || []);
    renderTopTable(data.top_events || []);
  }

  function renderTiles(data) {
    const makeTile = (label, value) => {
      const div = document.createElement('div');
      div.className = 'tile';
      div.innerHTML = `<strong>${label}</strong><br>${value}`;
      return div;
    };
    tiles.append(
      makeTile(__('Total RSVPs'), data.total_rsvps || 0),
      makeTile(__('Unique attendees'), data.unique_attendees || 0),
      makeTile(__('Confirmed %'), data.confirmed_percent || 0),
      makeTile(__('Top event'), data.top_event || __('n/a')),
    );
  }

  function renderChart(points) {
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if (!points.length) return;
    const max = Math.max(...points.map((p) => p.count));
    const step = canvas.width / points.length;
    ctx.beginPath();
    points.forEach((p, i) => {
      const x = i * step;
      const y = canvas.height - (p.count / max) * canvas.height;
      ctx.lineTo(x, y);
    });
    ctx.strokeStyle = '#3366cc';
    ctx.stroke();
  }

  function renderTable(points) {
    points.forEach((p) => {
      const tr = document.createElement('tr');
      const d = document.createElement('td');
      d.textContent = p.date;
      const c = document.createElement('td');
      c.textContent = p.count;
      tr.append(d, c);
      tbody.appendChild(tr);
    });
  }

  function renderBar(rows) {
    const ctx = barCanvas.getContext('2d');
    ctx.clearRect(0, 0, barCanvas.width, barCanvas.height);
    if (!rows.length) return;
    const max = Math.max(...rows.map((r) => r.count));
    const barWidth = barCanvas.width / rows.length;
    rows.forEach((r, i) => {
      const h = (r.count / max) * barCanvas.height;
      ctx.fillStyle = '#8899cc';
      ctx.fillRect(i * barWidth, barCanvas.height - h, barWidth - 4, h);
    });
  }

  function renderTopTable(rows) {
    topBody.textContent = '';
    rows.forEach((r) => {
      const tr = document.createElement('tr');
      const t = document.createElement('td');
      t.textContent = r.title;
      const c = document.createElement('td');
      c.textContent = r.count;
      tr.append(t, c);
      topBody.appendChild(tr);
    });
  }

  function cacheKey(range) {
    return `analytics-${range}`;
  }

  load();
}

