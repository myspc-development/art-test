(function(){
  function debounce(fn, delay){
    let to;
    return function(...args){
      clearTimeout(to);
      to = setTimeout(() => fn.apply(this, args), delay);
    };
  }
  async function fetchSuggestions(query){
    if(!window.APLocation || !APLocation.googleEndpoint) return [];
    const resp = await fetch(APLocation.googleEndpoint + '?query=' + encodeURIComponent(query));
    if(!resp.ok) return [];
    try{
      const data = await resp.json();
      if(Array.isArray(data)){
        return data.map(p => p.description).filter(Boolean);
      }
    }catch(e){}
    return [];
  }

  function attach(input){
    const listId = input.id ? input.id + '-list' : 'ap-places-list-' + Math.random().toString(36).slice(2);
    let list = document.getElementById(listId);
    if(!list){
      list = document.createElement('datalist');
      list.id = listId;
      document.body.appendChild(list);
    }
    input.setAttribute('list', listId);
    input.addEventListener('input', debounce(async () => {
      const q = input.value.trim();
      if(q.length < 3) return;
      const suggestions = await fetchSuggestions(q);
      requestAnimationFrame(() => {
        list.innerHTML = '';
        suggestions.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s;
          list.appendChild(opt);
        });
      });
    }, 300));
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ap-google-autocomplete').forEach(attach);
  });
})();
