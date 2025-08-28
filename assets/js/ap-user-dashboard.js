import { apiFetch, __ } from './ap-core.js';
import { Toast } from './ap-ui.js';

const roleTabs = {
  member: ['overview', 'calendar', 'favorites', 'my-rsvps', 'settings'],
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

export function getRoutes() {
  return tabModules;
}

const labels = {
  overview: __('Overview'),
  calendar: __('Calendar'),
  favorites: __('Favorites'),
  'my-rsvps': __('My RSVPs'),
  settings: __('Settings'),
  portfolio: __('Portfolio'),
  artworks: __('Artworks'),
  events: __('Events'),
  rsvps: __('RSVPs'),
  analytics: __('Analytics'),
};

const main = document.getElementById('ap-view');
main.setAttribute('role', 'main');
main.setAttribute('aria-live', 'polite');
main.tabIndex = -1;
const navList = document.getElementById('ap-nav-list');
navList.setAttribute('role', 'tablist');
const roles = ARTPULSE_BOOT.currentUser.roles || [];
let currentTab = '';
const baseTitle = document.title;

function allowedTabs() {
  if (roles.includes('organization')) return roleTabs.organization;
  if (roles.includes('artist')) return roleTabs.artist;
  return roleTabs.member;
}

function renderNav(tabs) {
  navList.innerHTML = '';
  tabs.forEach((t, i) => {
    const li = document.createElement('li');
    li.setAttribute('role', 'presentation');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('role', 'tab');
    btn.dataset.tab = t;
    btn.id = `ap-tab-${t}`;
    btn.textContent = labels[t] || t;
    btn.setAttribute('aria-selected', 'false');
    btn.tabIndex = -1;
    btn.addEventListener('click', () => {
      window.location.hash = '#' + t;
    });
    btn.addEventListener('keydown', (e) => {
      let target;
      if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        target = navList.querySelector(`[data-tab="${tabs[(i + 1) % tabs.length]}"]`);
      } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        e.preventDefault();
        target = navList.querySelector(`[data-tab="${tabs[(i - 1 + tabs.length) % tabs.length]}"]`);
      }
      target?.focus();
    });
    li.appendChild(btn);
    navList.appendChild(li);
  });
}

async function loadTab(tab) {
  currentTab = tab;
  localStorage.setItem('ap-last-tab', tab);
  main.textContent = '';
  const container = document.createElement('div');
  container.textContent = __('Loading...');
  container.setAttribute('aria-busy', 'true');
  main.appendChild(container);
  try {
    if (tabModules[tab]) {
      const mod = await tabModules[tab]();
      container.textContent = '';
      await mod.default(container);
    } else {
      container.textContent = labels[tab] || tab;
    }
  } catch (e) {
    Toast.show({ type: 'error', message: e.message || 'Error loading panel' });
    container.textContent = __('Nothing to display');
  }
  container.removeAttribute('aria-busy');
  updateSelection();
  document.title = `${labels[tab] || tab} – ${baseTitle}`;
  main.focus();
}

function onHashChange() {
  const hash =
    window.location.hash.replace('#', '') ||
    localStorage.getItem('ap-last-tab') ||
    allowedTabs()[0];
  if (!allowedTabs().includes(hash)) {
    Toast.show({ type: 'warning', message: __('Unknown panel') });
    window.location.hash = '#overview';
    return;
  }
  if (hash !== currentTab) {
    loadTab(hash);
  }
}

function updateSelection() {
  navList.querySelectorAll('[role="tab"]').forEach((btn) => {
    btn.setAttribute('aria-selected', btn.dataset.tab === currentTab ? 'true' : 'false');
    btn.tabIndex = btn.dataset.tab === currentTab ? '0' : '-1';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const tabs = allowedTabs();
  renderNav(tabs);
  window.addEventListener('hashchange', onHashChange);
  onHashChange();
});

