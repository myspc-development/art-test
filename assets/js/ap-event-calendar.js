document.addEventListener('DOMContentLoaded', function() {
  if (!window.FullCalendar) return;
  var el = document.getElementById('ap-event-calendar');
  if (!el) return;

  var events = (window.APCalendar && window.APCalendar.events) ? window.APCalendar.events : [];

  var calendar = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    events: events,
    eventClick: function(info) {
      info.jsEvent.preventDefault();
      window.open(info.event.url, '_blank');
    },
    eventDidMount: function(info) {
      var title = info.event.title;
      var venue = info.event.extendedProps.venue;
      var address = info.event.extendedProps.address;
      var tooltip = title;
      if (venue) tooltip += "\nVenue: " + venue;
      if (address) tooltip += "\nAddress: " + address;
      info.el.title = tooltip;
    }
  });

  calendar.render();
});
