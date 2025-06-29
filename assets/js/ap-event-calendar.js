document.addEventListener('DOMContentLoaded', function() {
  if (!window.FullCalendar) return;
  var el = document.getElementById('ap-event-calendar');
  if (!el) return;

  var events = (window.APCalendar && window.APCalendar.events) ? window.APCalendar.events : [];
  var restRoot = (window.APCalendar && window.APCalendar.rest_root) || (window.wpApiSettings && window.wpApiSettings.root) || '/wp-json/';

  var popover, popContent, closeBtn, lastFocus;

  function createPopover() {
    if (popover) return;
    popover = document.createElement('div');
    popover.id = 'ap-event-popover';
    popover.className = 'ap-event-popover';
    popover.setAttribute('role', 'dialog');
    popover.setAttribute('aria-modal', 'true');
    popover.tabIndex = -1;
    closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'ap-event-popover-close ap-form-button';
    closeBtn.textContent = 'Close';
    closeBtn.addEventListener('click', hidePopover);
    popContent = document.createElement('div');
    popover.appendChild(closeBtn);
    popover.appendChild(popContent);
    document.body.appendChild(popover);
    document.addEventListener('click', function(e){ if (popover.classList.contains('open') && !popover.contains(e.target)) hidePopover(); });
    document.addEventListener('keydown', function(e){ if (e.key === 'Escape') hidePopover(); });
  }

  function showPopover(html, x, y, url) {
    createPopover();
    popContent.innerHTML = html;
    if (window.innerWidth < 500) {
      var link = document.createElement('p');
      link.className = 'ap-event-view-link';
      link.innerHTML = '<a href="' + url + '">View event</a>';
      popContent.appendChild(link);
    }
    popover.style.left = x + 'px';
    popover.style.top = y + 'px';
    popover.classList.add('open');
    lastFocus = document.activeElement;
    popover.focus();
  }

  function hidePopover() {
    if (!popover) return;
    popover.classList.remove('open');
    popContent.innerHTML = '';
    if (lastFocus) lastFocus.focus();
  }

  var calendar = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    events: events,
    eventClick: function(info) {
      info.jsEvent.preventDefault();
      fetch(restRoot + 'artpulse/v1/event-card/' + info.event.id)
        .then(function(res){ return res.text(); })
        .then(function(html){ showPopover(html, info.jsEvent.pageX, info.jsEvent.pageY, info.event.url); })
        .catch(function(){ window.open(info.event.url, '_blank'); });
    },
    eventDidMount: function(info) {
      var title = info.event.title;
      var venue = info.event.extendedProps.venue;
      var address = info.event.extendedProps.address;
      var tooltip = title;
      if (venue) tooltip += '\nVenue: ' + venue;
      if (address) tooltip += '\nAddress: ' + address;
      info.el.title = tooltip;
    }
  });

  calendar.render();
});
