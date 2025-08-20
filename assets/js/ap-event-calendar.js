document.addEventListener('DOMContentLoaded', function() {
  if (!window.FullCalendar) return;
  var el = document.getElementById('ap-event-calendar');
  if (!el) return;

  var restRoot = (window.APCalendar && window.APCalendar.apiRoot) || (window.wpApiSettings && window.wpApiSettings.root) || '/wp-json/';
  var nonce    = (window.APCalendar && window.APCalendar.nonce) || '';

  function initCalendar(lat, lng) {
    var calendar = new FullCalendar.Calendar(el, {
      initialView: 'dayGridMonth',
      events: function(info, success, failure) {
        var params = new URLSearchParams({ start: info.startStr, end: info.endStr });
        if (lat != null && lng != null) {
          params.set('lat', lat);
          params.set('lng', lng);
        }
        fetch(restRoot + 'artpulse/v1/calendar?' + params.toString(), {
          headers: nonce ? { 'X-WP-Nonce': nonce } : {}
        })
          .then(r => r.json())
          .then(data => success(data))
          .catch(failure);
      },
      eventClick: function(info) {
        window.location.href = info.event.url;
      }
    });
    calendar.render();
  }

  function promptForLocation() {
    var input = prompt('Enter your location as "lat,lng"');
    if (input) {
      var parts = input.split(',');
      var lat = parseFloat(parts[0]);
      var lng = parseFloat(parts[1]);
      if (!isNaN(lat) && !isNaN(lng)) {
        localStorage.setItem('ap_last_location', JSON.stringify({lat:lat,lng:lng}));
        initCalendar(lat, lng);
        return;
      }
    }
    initCalendar(null, null);
  }

  function obtainLocation() {
    var stored = localStorage.getItem('ap_last_location');
    if (stored) {
      try {
        var loc = JSON.parse(stored);
        if (loc.lat != null && loc.lng != null) {
          initCalendar(loc.lat, loc.lng);
          return;
        }
      } catch(e){}
    }
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(pos){
        var loc = { lat: pos.coords.latitude, lng: pos.coords.longitude };
        localStorage.setItem('ap_last_location', JSON.stringify(loc));
        initCalendar(loc.lat, loc.lng);
      }, function(){
        promptForLocation();
      });
    } else {
      promptForLocation();
    }
  }

  obtainLocation();
});
