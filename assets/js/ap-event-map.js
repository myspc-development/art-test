document.addEventListener('DOMContentLoaded', function(){
  if(!window.L) return;
  var el = document.getElementById('ap-event-map');
  if(!el) return;
  var map = L.map(el).setView([0,0], 2);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);
  var url = (window.APEventMap && window.APEventMap.rest) || '';
  if(!url) return;
  fetch(url)
    .then(function(r){ return r.json(); })
    .then(function(events){
      if(!Array.isArray(events)) return;
      events.forEach(function(ev){
        if(!ev.lat || !ev.lng) return;
        var m = L.marker([ev.lat, ev.lng]).addTo(map);
        var html = ev.link ? '<a href="'+ev.link+'">'+ev.title+'</a>' : ev.title;
        m.bindPopup(html);
      });
    });
});
