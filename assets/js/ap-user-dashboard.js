import { apiFetch, __ } from './ap-core.js';
import { Toast } from './ap-ui.js';

const roleTabs = {
  member: ['overview', 'calendar', 'favorites', 'my-rsvps', 'settings', 'upgrade-artist'],
  artist: ['overview', 'portfolio', 'artworks', 'calendar', 'settings'],
  organization: ['overview', 'events', 'rsvps', 'analytics', 'settings'],
};

const tabModules = {
  calendar: () => import('./ap-event-calendar.js'),
  favorites: () => import('./ap-favorites.js'),
  'my-rsvps': () => import('./ap-rest-lists.js'),
  portfolio: () => import('./ap-portfolio-builder.js'),
  events: () => import('./ap-event-editor.js'),
  rsvps: () => import('./ap-rsvp-admin.js'),
  analytics: () => import('./ap-analytics.js'),
};

const labels = {
  overview: __('Overview'),
  calendar: __('Calendar'),
  favorites: __('Favorites'),
  'my-rsvps': __('My RSVPs'),
  settings: __('Settings'),
  'upgrade-artist': __('Upgrade to Artist'),
  portfolio: __('Portfolio'),
  artworks: __('Artworks'),
  events: __('Events'),
  rsvps: __('RSVPs'),
  analytics: __('Analytics'),
};

const main = document.getElementById('ap-view');
const navList = document.getElementById('ap-nav-list');
const roles = ARTPULSE_BOOT.currentUser.roles || [];
let currentTab = '';

function allowedTabs() {
  if (roles.includes('organization')) return roleTabs.organization;
  if (roles.includes('artist')) return roleTabs.artist;
  return roleTabs.member;
}

function renderNav(tabs) {
  navList.innerHTML = '';
  tabs.forEach((t) => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.href = '#' + t;
    a.textContent = labels[t] || t;
    li.appendChild(a);
    navList.appendChild(li);
  });
}

async function loadTab(tab) {
  currentTab = tab;
  localStorage.setItem('ap-last-tab', tab);
  main.textContent = '';
  const container = document.createElement('div');
  main.appendChild(container);
  try {
    if (tabModules[tab]) {
      const mod = await tabModules[tab]();
      await mod.default(container);
    } else {
      container.textContent = labels[tab] || tab;
    }
  } catch (e) {
    Toast.show({ type: 'error', message: e.message || 'Error loading panel' });
    container.textContent = __('Nothing to display');
  }
}

function onHashChange() {
  const hash = window.location.hash.replace('#', '') || localStorage.getItem('ap-last-tab') || allowedTabs()[0];
  if (!allowedTabs().includes(hash)) {
    window.location.hash = allowedTabs()[0];
    return;
  }
  if (hash !== currentTab) {
    loadTab(hash);
  }
}

function prefetch(tabs) {
  if (window.requestIdleCallback) {
    requestIdleCallback(() => {
      tabs.forEach((t) => {
        if (tabModules[t]) {
          tabModules[t]().catch(() => {});
        }
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const tabs = allowedTabs();
  renderNav(tabs);
  prefetch(tabs.slice(1));
  window.addEventListener('hashchange', onHashChange);
  onHashChange();
});

