import { JSDOM } from 'jsdom';

// Mocks for dependencies imported by the router
jest.mock('../assets/js/ap-core.js', () => ({ apiFetch: jest.fn(), __: (s: string) => s }));
const toastShow = jest.fn();
jest.mock('../assets/js/ap-ui.js', () => ({ Toast: { show: toastShow } }));

const calendarMock = jest.fn();
jest.mock('../assets/js/ap-event-calendar.js', () => ({ __esModule: true, default: calendarMock }));
const favoritesMock = jest.fn();
jest.mock('../assets/js/ap-favorites.js', () => ({ __esModule: true, default: favoritesMock }));
const rsvpsListMock = jest.fn();
jest.mock('../assets/js/ap-rest-lists.js', () => ({ __esModule: true, default: rsvpsListMock }));
const portfolioMock = jest.fn();
jest.mock('../assets/js/ap-portfolio-builder.js', () => ({ __esModule: true, default: portfolioMock }));
const eventEditorMock = jest.fn();
jest.mock('../assets/js/ap-event-editor.js', () => ({ __esModule: true, default: eventEditorMock }));
const rsvpAdminMock = jest.fn();
jest.mock('../assets/js/ap-rsvp-admin.js', () => ({ __esModule: true, default: rsvpAdminMock }));
const analyticsMock = jest.fn();
jest.mock('../assets/js/ap-analytics.js', () => ({ __esModule: true, default: analyticsMock }));

const moduleMap: Record<string, jest.Mock> = {
  calendar: calendarMock,
  favorites: favoritesMock,
  'my-rsvps': rsvpsListMock,
  portfolio: portfolioMock,
  events: eventEditorMock,
  rsvps: rsvpAdminMock,
  analytics: analyticsMock,
};

function setupDom() {
  const dom = new JSDOM('<main id="ap-view"></main><ul id="ap-nav-list"></ul>', { url: 'http://localhost' });
  (global as any).window = dom.window as any;
  (global as any).document = dom.window.document as any;
  (global as any).localStorage = dom.window.localStorage as any;
  (global as any).navigator = dom.window.navigator as any;
  (global as any).Event = dom.window.Event as any;
  (global as any).requestIdleCallback = (cb: any) => cb();
}

function flush() {
  return new Promise(resolve => setTimeout(resolve, 0));
}

describe('dashboard router', () => {
  const routes = [
    { route: 'overview', roles: [] as string[] },
    { route: 'calendar', roles: [] as string[] },
    { route: 'favorites', roles: [] as string[] },
    { route: 'my-rsvps', roles: [] as string[] },
    { route: 'settings', roles: [] as string[] },
    { route: 'portfolio', roles: ['artist'] },
    { route: 'artworks', roles: ['artist'] },
    { route: 'events', roles: ['organization'] },
    { route: 'rsvps', roles: ['organization'] },
    { route: 'analytics', roles: ['organization'] },
  ];

  beforeEach(() => {
    setupDom();
    Object.values(moduleMap).forEach(m => m.mockReset());
    toastShow.mockReset();
  });

  it.each(routes)('loads route #%s', async ({ route, roles }) => {
    (global as any).ARTPULSE_BOOT = { currentUser: { roles } };
    window.location.hash = '#' + route;
    await jest.isolateModulesAsync(async () => {
      await import('../assets/js/ap-user-dashboard.js');
      document.dispatchEvent(new Event('DOMContentLoaded'));
    });
    await flush();

    if (moduleMap[route]) {
      expect(moduleMap[route]).toHaveBeenCalled();
    } else {
      Object.values(moduleMap).forEach(m => expect(m).not.toHaveBeenCalled());
    }
  });

  test('falls back to overview on unknown hash', async () => {
    setupDom();
    (global as any).ARTPULSE_BOOT = { currentUser: { roles: [] as string[] } };
    window.location.hash = '#unknown';
    await jest.isolateModulesAsync(async () => {
      await import('../assets/js/ap-user-dashboard.js');
      document.dispatchEvent(new Event('DOMContentLoaded'));
    });
    await flush();
    expect(toastShow).toHaveBeenCalled();
    expect(window.location.hash).toBe('#overview');
  });
});

