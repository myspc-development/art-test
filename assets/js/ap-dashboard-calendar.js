document.addEventListener('DOMContentLoaded', function() {
  if (!window.FullCalendar) return;
  var el = document.getElementById('artist-events-calendar');
  if (!el) return;
  var calendar = new FullCalendar.Calendar(el, {
    initialView: 'dayGridMonth',
    events: APArtistCalendar.rest_root + 'artpulse/v1/artist-events',
    eventClick: function(info) {
      if (info.event.extendedProps.edit) {
        window.location.href = info.event.extendedProps.edit;
      }
    }
  });
  calendar.render();
});
