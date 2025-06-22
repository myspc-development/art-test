(function(){
  function populate(select, items, valueKey, textKey) {
    if(!select) return;
    const current = select.dataset.selected || select.value;
    select.innerHTML = '';
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = '';
    select.appendChild(empty);
    items.forEach(item => {
      const opt = document.createElement('option');
      opt.value = item[valueKey];
      opt.textContent = item[textKey];
      select.appendChild(opt);
    });
    if(current){
      select.value = current;
    }
  }

  async function loadData() {
    if(!window.APLocation) return {countries:[],states:[],cities:[]};
    const res = await fetch(APLocation.datasetUrl);
    return res.json();
  }

  function updateComponents(form) {
    const c = form.querySelector('.ap-address-country');
    const s = form.querySelector('.ap-address-state');
    const ci = form.querySelector('.ap-address-city');
    const hidden = form.querySelector('[name="address_components"]');
    if(hidden) {
      hidden.value = JSON.stringify({
        country: c ? c.value : '',
        state: s ? s.value : '',
        city: ci ? ci.value : ''
      });
    }
  }

  document.addEventListener('DOMContentLoaded', async function(){
    const data = await loadData();
    const countrySelects = document.querySelectorAll('.ap-address-country');
    const stateSelects = document.querySelectorAll('.ap-address-state');
    const citySelects = document.querySelectorAll('.ap-address-city');

    countrySelects.forEach(sel => {
      populate(sel, data.countries, 'code', 'name');
      if(sel.dataset.selected){
        sel.value = sel.dataset.selected;
        sel.dispatchEvent(new Event('change'));
      }
      sel.addEventListener('change', async () => {
        sel.dataset.selected = sel.value;
        const form = sel.closest('form');
        const stateSel = form.querySelector('.ap-address-state');
        const citySel = form.querySelector('.ap-address-city');
        if(stateSel){
          stateSel.dataset.selected = '';
          const states = data.states.filter(s=>s.country===sel.value);
          if(states.length===0 && APLocation.geonamesEndpoint){
            const resp = await fetch(APLocation.geonamesEndpoint+'?type=states&country='+sel.value);
            const json = await resp.json();
            if(Array.isArray(json)) {
              data.states = data.states.concat(json);
              json.forEach(s=>{s.country=sel.value});
              populate(stateSel, json, 'code','name');
            }
          } else {
            populate(stateSel, states,'code','name');
          }
        }
        if(citySel){
          citySel.dataset.selected = '';
          citySel.innerHTML = '';
        }
        updateComponents(form);
      });
    });

    stateSelects.forEach(sel => {
      if(sel.dataset.selected){
        sel.value = sel.dataset.selected;
        sel.dispatchEvent(new Event('change'));
      }
      sel.addEventListener('change', async () => {
        sel.dataset.selected = sel.value;
        const form = sel.closest('form');
        const countrySel = form.querySelector('.ap-address-country');
        const citySel = form.querySelector('.ap-address-city');
        if(citySel){
          citySel.dataset.selected = '';
          const cities = data.cities.filter(c=>c.country===countrySel.value && c.state===sel.value);
          if(cities.length===0 && APLocation.geonamesEndpoint){
            const resp = await fetch(APLocation.geonamesEndpoint+'?type=cities&country='+countrySel.value+'&state='+sel.value);
            const json = await resp.json();
            if(Array.isArray(json)) {
              json.forEach(ci=>{ci.country=countrySel.value;ci.state=sel.value});
              data.cities = data.cities.concat(json);
              populate(citySel, json,'name','name');
            }
          } else {
            populate(citySel, cities,'name','name');
          }
        }
        updateComponents(form);
      });
    });

    citySelects.forEach(sel => {
      if(sel.dataset.selected){
        sel.value = sel.dataset.selected;
        updateComponents(sel.closest('form'));
      }
      sel.addEventListener('change', () => {
        sel.dataset.selected = sel.value;
        const form = sel.closest('form');
        updateComponents(form);
      });
    });
  });
})();
