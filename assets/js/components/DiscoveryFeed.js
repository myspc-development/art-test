(function (React, ReactDOM) {
  const { createElement, useEffect, useState } = React;

  function DiscoveryFeed() {
    const [items, setItems] = useState([]);

    useEffect(() => {
      fetch('/wp-json/artpulse/v1/trending')
        .then((r) => r.json())
        .then((data) => setItems(Array.isArray(data) ? data : []))
        .catch(() => setItems([]));
    }, []);

    return createElement(
      'div',
      { className: 'ap-discovery-feed grid md:grid-cols-3 gap-4' },
      items.map((item) =>
        createElement(
          'div',
          { key: item.id, className: 'border p-2 rounded bg-white' },
          createElement(
            'a',
            { href: item.link, className: 'font-bold block mb-1' },
            item.title
          ),
          createElement(
            'span',
            { className: 'text-sm text-gray-500' },
            'Score: ',
            item.score
          )
        )
      )
    );
  }

  document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('ap-discovery-feed');
    if (el && ReactDOM && ReactDOM.createRoot) {
      ReactDOM.createRoot(el).render(createElement(DiscoveryFeed));
    }
  });
})(window.React, window.ReactDOM);
