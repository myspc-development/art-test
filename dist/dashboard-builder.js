(function(wp){
  const { createElement: h, render, useState, useEffect } = wp.element;

  function App(props){
    const [role, setRole] = useState(props.roles[0] || '');
    const [available, setAvailable] = useState([]);
    const [order, setOrder] = useState([]);
    const [enabled, setEnabled] = useState({});

    useEffect(() => {
      if(!role){ return; }
      fetch(`${props.rest_root}artpulse/v1/dashboard-widgets?role=${role}`, {
        headers: { 'X-WP-Nonce': props.nonce }
      })
        .then(r => r.json())
        .then(data => {
          setAvailable(data.available || []);
          const active = data.active || {};
          const map = {};
          (active.enabledWidgets || []).forEach(id => { map[id] = true; });
          setEnabled(map);
          const ord = (active.layoutOrder && active.layoutOrder.length)
            ? active.layoutOrder
            : (data.available || []).map(w => w.id);
          setOrder(ord);
        });
    }, [role]);

    function toggle(id){
      setEnabled(e => ({ ...e, [id]: !e[id] }));
    }

    let dragIndex = null;
    function onDragStart(i){ dragIndex = i; }
    function onDrop(i){ if(dragIndex===null)return; move(dragIndex, i); dragIndex=null; }
    function move(from,to){ setOrder(o => { const arr=[...o]; const [m]=arr.splice(from,1); arr.splice(to,0,m); return arr; }); }

    function save(){
      fetch(`${props.rest_root}artpulse/v1/dashboard-widgets/save`, {
        method:'POST',
        headers:{ 'Content-Type':'application/json', 'X-WP-Nonce': props.nonce },
        body: JSON.stringify({
          role: role,
          enabledWidgets: order.filter(id => enabled[id]),
          layoutOrder: order
        })
      }).then(r => r.json()).then(() => alert('Saved'));
    }

    return h('div', {},
      h('h2', {}, 'Dashboard Builder'),
      h('select', { value: role, onChange: e => setRole(e.target.value) },
        props.roles.map(r => h('option', { key:r, value:r }, r))
      ),
      h('ul', { className:'ap-builder-list' },
        order.map((id,idx) => {
          const w = available.find(w => w.id === id) || { title:id };
          return h('li', {
              key:id,
              draggable:true,
              onDragStart:()=>onDragStart(idx),
              onDragOver:e=>e.preventDefault(),
              onDrop:()=>onDrop(idx)
            },
            h('input', { type:'checkbox', checked:!!enabled[id], onChange:()=>toggle(id) }),
            ' ', w.title
          );
        })
      ),
      h('button', { onClick: save }, 'Save Layout')
    );
  }

  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('dashboard-builder-root');
    if(root){
      render(h(App, window.APDashboardBuilder || {}), root);
    }
  });
})(window.wp);
