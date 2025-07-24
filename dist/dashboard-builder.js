(function(wp){
  const { createElement: h, render, useState, useEffect } = wp.element;

  function App(props){
    const [role, setRole] = useState(props.roles[0] || '');
    const [lastRole, setLastRole] = useState(null);
    const [available, setAvailable] = useState([]);
    const [order, setOrder] = useState([]);
    const [enabled, setEnabled] = useState({});
    const [preview, setPreview] = useState({});

    useEffect(() => {
      if(!role){ return; }
      if(lastRole === role && available.length){
        console.log('[DashboardBuilder] Skip fetch, role unchanged:', role);
        return;
      }
      setLastRole(role);
      console.log('[DashboardBuilder] fetch widgets', role);
      fetch(`${props.rest_root}artpulse/v1/dashboard-widgets?role=${role}`, {
        headers: { 'X-WP-Nonce': props.nonce }
      })
        .then(r => r.json())
        .then(data => {
          console.log('[DashboardBuilder] response', data);
          setAvailable(data.available || []);
          const prev = {};
          (data.available || []).forEach(w => { prev[w.id] = w.preview || ''; });
          setPreview(prev);
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
      h('button', { onClick: save }, 'Save Layout'),
      h('h3', {}, 'Preview'),
      h('div', { className: 'ap-preview' },
        order.map(id => {
          if(!enabled[id]) return null;
          const html = preview[id] || '';
          return h('div', { key:id, className:'ap-widget-preview', dangerouslySetInnerHTML:{__html: html} });
        })
      )
      );
  }

  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('dashboard-builder-root');
    if(root){
      console.log('[DashboardBuilder] initial role', (window.APDashboardBuilder||{}).roles?.[0]);
      render(h(App, window.APDashboardBuilder || {}), root);
    }
  });
})(window.wp);
