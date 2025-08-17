import { describe, it, expect, beforeEach, afterEach, jest } from '@jest/globals';

function setupDom() {
  document.body.innerHTML = `
    <div class="ap-event-chat" data-event-id="1">
      <ul class="ap-chat-list"></ul>
    </div>`;
}

async function loadModule() {
  const listeners = {};
  const origAdd = document.addEventListener.bind(document);
  document.addEventListener = jest.fn((evt, cb) => {
    listeners[evt] = cb;
  });
  await import('../../assets/js/ap-event-chat.js');
  document.addEventListener = origAdd;
  listeners['DOMContentLoaded']?.();
  await flushPromises();
}

const flushPromises = () => Promise.resolve();

describe('ap-event-chat', () => {
  beforeEach(() => {
    jest.resetModules();
    jest.useFakeTimers();
    global.fetch = jest.fn();
    setupDom();
  });

  afterEach(() => {
    jest.clearAllTimers();
    jest.useRealTimers();
  });

  it('renders messages on success', async () => {
    const msgs = [
      { created_at: '2024-01-01T00:00:00Z', avatar: 'a.png', author: 'A', content: 'hi' },
    ];
    global.fetch.mockResolvedValueOnce({ ok: true, json: () => Promise.resolve(msgs) });
    await loadModule();
    await flushPromises();
    await flushPromises();
    await flushPromises();
    const items = document.querySelectorAll('.ap-chat-list li');
    expect(items.length).toBe(1);
  });

  it('stops polling on 404', async () => {
    global.fetch.mockResolvedValue({ ok: false, status: 404 });
    await loadModule();
    await flushPromises();
    await flushPromises();
    await flushPromises();
    jest.advanceTimersByTime(10000);
    await Promise.resolve();
    expect(global.fetch).toHaveBeenCalledTimes(1);
  });

  it('shows fallback when response malformed', async () => {
    global.fetch.mockResolvedValueOnce({ ok: true, json: () => Promise.resolve({}) });
    await loadModule();
    await flushPromises();
    await flushPromises();
    await flushPromises();
    const list = document.querySelector('.ap-chat-list');
    expect(list.textContent).toContain('Unable to load messages');
  });
});
