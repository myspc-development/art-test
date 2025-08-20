(function () {
    function render() {
        var panel = window.location.hash.substring(1) || 'overview';
        document.querySelectorAll('.ap-dashboard-panel').forEach(function (el) {
            el.style.display = el.dataset.panel === panel ? 'block' : 'none';
        });
    }
    window.addEventListener('hashchange', render);
    document.addEventListener('DOMContentLoaded', render);
})();
