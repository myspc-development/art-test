/* eslint-disable no-console */
(function () {
  const restBase = (window.apChat && window.apChat.restBase) || (window.apDashboardData && window.apDashboardData.restBase) || '/wp-json/artpulse/v1';
  const nonce    = (window.apChat && window.apChat.nonce)    || (window.apDashboardData && window.apDashboardData.nonce)    || '';
  const dataEl   = document.querySelector('[data-event-id]');
  const eventId  = Number((window.apChat && window.apChat.eventId) || (dataEl && dataEl.dataset.eventId) || 0);

  if (!eventId) {
    console.warn('[ap-event-chat] No eventId available; aborting poller.');
    return;
  }

  let backoff = 2000; // start 2s
  const MAX_BACKOFF = 60000;
  let stopped = false;

  function renderMessages(messages) {
    // NOOP placeholder – integrate with your UI render.
  }

  async function load() {
    const res = await fetch(`${restBase.replace(/\/$/, '')}/event/${eventId}/chat`, {
      headers: nonce ? { 'X-WP-Nonce': nonce } : {},
      credentials: 'same-origin',
      cache: 'no-store',
    });

    if (!res.ok) {
      if ([401, 403, 404].includes(res.status)) {
        console.warn('[ap-event-chat] Stopping polling due to status', res.status, { eventId });
        stopped = true;
        return;
      }
      backoff = Math.min(backoff * 2, MAX_BACKOFF);
      throw new Error(`Chat load failed: ${res.status}`);
    }

    backoff = 2000;
    const data = await res.json();
    renderMessages(data);
  }

  function tick() {
    if (stopped) return;
    setTimeout(() => {
      load().then(tick).catch((err) => {
        console.error('[ap-event-chat] load error', err);
        tick();
      });
    }, backoff);
  }

  tick();
})();

