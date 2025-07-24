(function(wp){
  const { createElement: h, render, useState, useEffect, Component } = wp.element;

  class ErrorBoundary extends Component {
    constructor(props){
      super(props);
      this.state = { hasError:false };
    }
    static getDerivedStateFromError(){ return { hasError:true }; }
    componentDidCatch(err){ console.warn('Widget error', err); }
    render(){
      if(this.state.hasError){
        return h('div', { className: 'ap-widget-error' }, 'Failed to load widget');
      }
      return this.props.children;
    }
  }

  function App(props){
    const [role, setRole] = useState(props.roles[0] || '');
    const [lastRole, setLastRole] = useState(null);
    const [available, setAvailable] = useState([]);
    const [order, setOrder] = useState([]);
    const [enabled, setEnabled] = useState({});
    const [preview, setPreview] = useState({});
    const [layoutReady, setLayoutReady] = useState(false);
    const [fetching, setFetching] = useState(false);

    useEffect(() => {
      if(!role){ return; }
      if(fetching){ return; }
      if(lastRole === role && layoutReady){
        console.log('[DashboardBuilder] Skip fetch, role unchanged:', role);
        return;
      }
      setLastRole(role);
      setLayoutReady(false);
      setFetching(true);
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
          setLayoutReady(true);
          setFetching(false);
        })
        .catch(err => {
          console.warn('[DashboardBuilder] fetch failed', err);
          setFetching(false);
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
          return h(ErrorBoundary, { key:id },
            h('div', { className:'ap-widget-preview', dangerouslySetInnerHTML:{__html: html} })
          );
        })
      )
      );
  }

  document.addEventListener('DOMContentLoaded', function(){
    const root = document.getElementById('dashboard-builder-root');
    if(root){
      window.IS_DASHBOARD_BUILDER_PREVIEW = true;
      console.log('[DashboardBuilder] initial role', (window.APDashboardBuilder||{}).roles?.[0]);
      render(h(App, window.APDashboardBuilder || {}), root);
    }
  });
})(window.wp);
