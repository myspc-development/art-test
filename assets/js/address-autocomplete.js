(function(){
  async function loadData(){
    if(!window.APLocation) return {countries:[],states:[],cities:[]};
    const [countriesRes, statesRes, citiesRes] = await Promise.all([
      fetch(APLocation.countriesUrl),
      fetch(APLocation.statesUrl),
      fetch(APLocation.citiesUrl)
    ]);
    const countries = countriesRes.ok ? await countriesRes.json() : [];
    const states = statesRes.ok ? await statesRes.json() : [];
    const cities = citiesRes.ok ? await citiesRes.json() : [];
    return {countries, states, cities};
  }

  function createList(input){
    const id = input.id ? input.id + '-list' : 'ap-list-' + Math.random().toString(36).slice(2);
    let list = document.getElementById(id);
    if(!list){
      list = document.createElement('datalist');
      list.id = id;
      document.body.appendChild(list);
    }
    input.setAttribute('list', id);
    return list;
  }

  function filterItems(items, key, q){
    const query = q.toLowerCase();
    return items.filter(i => (i[key] || '').toLowerCase().startsWith(query));
  }

  function findItem(items, value){
    const v = value.toLowerCase();
    return items.find(i => (i.name && i.name.toLowerCase()===v) || (i.code && i.code.toLowerCase()===v));
  }

  function updateComponents(form){
    const c = form.querySelector('.ap-address-country');
    const s = form.querySelector('.ap-address-state');
    const ci = form.querySelector('.ap-address-city');
    const hidden = form.querySelector('[name="address_components"]');
    if(hidden){
      hidden.value = JSON.stringify({
        country: c ? (c.dataset.code || c.value) : '',
        state: s ? (s.dataset.code || s.value) : '',
        city: ci ? ci.value : ''
      });
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    const data = await loadData();

    document.querySelectorAll('form').forEach(form => {
      const country = form.querySelector('.ap-address-country');
      const state = form.querySelector('.ap-address-state');
      const city = form.querySelector('.ap-address-city');

      if(country) setupCountry(country, state, city);
      if(state) setupState(state, country, city);
      if(city) setupCity(city, country, state);
    });

    function setupCountry(input, stateInput, cityInput){
      const list = createList(input);
      if(input.dataset.selected){
        const item = findItem(data.countries, input.dataset.selected);
        if(item){
          input.value = item.name;
          input.dataset.code = item.code;
        }else{
          input.value = input.dataset.selected;
        }
      }
      input.addEventListener('input', () => {
        const suggestions = filterItems(data.countries, 'name', input.value);
        list.innerHTML = '';
        suggestions.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.name;
          list.appendChild(opt);
        });
      });
      input.addEventListener('change', () => {
        const item = findItem(data.countries, input.value);
        input.dataset.code = item ? item.code : input.value;
        if(stateInput){
          stateInput.value = '';
          stateInput.dataset.code = '';
        }
        if(cityInput){
          cityInput.value = '';
        }
        updateComponents(input.closest('form'));
      });
    }

    async function ensureStates(countryCode){
      let states = data.states.filter(s => s.country === countryCode);
      if(states.length === 0 && APLocation.geonamesEndpoint && countryCode){
        const resp = await fetch(APLocation.geonamesEndpoint + '?type=states&country=' + countryCode);
        const json = await resp.json();
        if(Array.isArray(json)){
          json.forEach(s => {s.country = countryCode;});
          data.states = data.states.concat(json);
          states = json;
        }
      }
      return states;
    }

    function setupState(input, countryInput, cityInput){
      const list = createList(input);
      if(input.dataset.selected){
        input.value = input.dataset.selected;
        input.dataset.code = input.dataset.selected;
      }
      input.addEventListener('input', async () => {
        const cCode = countryInput ? (countryInput.dataset.code || countryInput.value) : '';
        const states = await ensureStates(cCode);
        const suggestions = filterItems(states, 'name', input.value);
        list.innerHTML = '';
        suggestions.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.name;
          list.appendChild(opt);
        });
      });
      input.addEventListener('change', () => {
        const cCode = countryInput ? (countryInput.dataset.code || countryInput.value) : '';
        const item = data.states.find(s => s.country === cCode && (s.name.toLowerCase()===input.value.toLowerCase() || (s.code && s.code.toLowerCase()===input.value.toLowerCase())));
        input.dataset.code = item ? item.code : input.value;
        if(cityInput){
          cityInput.value = '';
        }
        updateComponents(input.closest('form'));
      });
    }

    async function ensureCities(countryCode, stateCode){
      let cities = data.cities.filter(c => c.country === countryCode && c.state === stateCode);
      if(cities.length === 0 && APLocation.geonamesEndpoint && countryCode && stateCode){
        const resp = await fetch(APLocation.geonamesEndpoint + '?type=cities&country=' + countryCode + '&state=' + stateCode);
        const json = await resp.json();
        if(Array.isArray(json)){
          json.forEach(ci => {ci.country = countryCode; ci.state = stateCode;});
          data.cities = data.cities.concat(json);
          cities = json;
        }
      }
      return cities;
    }

    function setupCity(input, countryInput, stateInput){
      const list = createList(input);
      if(input.dataset.selected){
        input.value = input.dataset.selected;
      }
      input.addEventListener('input', async () => {
        const cCode = countryInput ? (countryInput.dataset.code || countryInput.value) : '';
        const sCode = stateInput ? (stateInput.dataset.code || stateInput.value) : '';
        const cities = await ensureCities(cCode, sCode);
        const suggestions = filterItems(cities, 'name', input.value);
        list.innerHTML = '';
        suggestions.forEach(ci => {
          const opt = document.createElement('option');
          opt.value = ci.name;
          list.appendChild(opt);
        });
      });
      input.addEventListener('change', () => {
        updateComponents(input.closest('form'));
      });
    }
  });
})();
