document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ap-user-dashboard');
    if (container) {
        Sortable.create(container, {
            animation: 150,
            handle: '.drag-handle',
            onEnd: () => {
                // Save layout via AJAX
            }
        });
    }
});
