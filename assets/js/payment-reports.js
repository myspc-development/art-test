(function(){
  const { createElement, useState, useEffect, render } = wp.element;

  function ReportsApp({ apiRoot, nonce }) {
    const [start, setStart] = useState('');
    const [end, setEnd] = useState('');
    const [loading, setLoading] = useState(false);
    const [metrics, setMetrics] = useState(null);

    const fetchReports = (s = start, e = end) => {
      setLoading(true);
      const params = new URLSearchParams();
      if (s) params.append('start_date', s);
      if (e) params.append('end_date', e);
      fetch(apiRoot + 'artpulse/v1/payment-reports?' + params.toString(), {
        headers: { 'X-WP-Nonce': nonce }
      })
        .then(r => r.ok ? r.json() : null)
        .then(data => setMetrics(data ? data.metrics : null))
        .finally(() => setLoading(false));
    };

    useEffect(() => { fetchReports(); }, []);

    const onSubmit = e => { e.preventDefault(); fetchReports(); };

    const renderRows = () => {
      if (!metrics) return null;
      const entries = Object.entries(metrics).filter(([k]) => !Array.isArray(metrics[k]));
      return entries.map(([key, val]) =>
        createElement('tr', { key }, [
          createElement('td', { style:{textTransform:'capitalize'} }, key.replace(/_/g,' ')),
          createElement('td', null, Array.isArray(val) ? JSON.stringify(val) : String(val))
        ])
      );
    };

    return createElement('div', { className:'ap-payment-reports' }, [
      createElement('form', { onSubmit }, [
        createElement('input', { type:'date', value:start, onChange:e=>setStart(e.target.value) }),
        createElement('input', { type:'date', value:end, onChange:e=>setEnd(e.target.value) }),
        createElement('button', { type:'submit', className:'button' }, 'Filter')
      ]),
      loading && createElement('p', null, 'Loading...'),
      metrics && createElement('table', { className:'widefat', style:{marginTop:'20px'} }, [
        createElement('thead', null,
          createElement('tr', null, [
            createElement('th', null, 'Metric'),
            createElement('th', null, 'Value')
          ])
        ),
        createElement('tbody', null, renderRows())
      ])
    ]);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('ap-payment-reports-root');
    if (el && window.APPaymentReports) {
      render(createElement(ReportsApp, APPaymentReports), el);
    }
  });
})();
