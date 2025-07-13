function loadChat() {
    const wrapper = document.querySelector('.ap-event-chat');
    if (!wrapper) return;

    const eventId = parseInt(wrapper.dataset.eventId || 0, 10);
    if (!eventId) return;

    fetch(`/wp-json/artpulse/v1/event/${eventId}/chat`)
        .then(res => {
            if (!res.ok) throw new Error(`Failed to load chat: HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!Array.isArray(data)) throw new Error('Invalid chat format');

            const list = wrapper.querySelector('.ap-chat-list');
            if (!list) return;

            list.innerHTML = data.map(entry => `
                <li class="chat-message"><strong>${entry.author}:</strong> ${entry.content}</li>
            `).join('');
        })
        .catch(err => {
            console.error('Chat load error:', err.message);
            const list = wrapper.querySelector('.ap-chat-list');
            if (list) list.innerHTML = `<li class="error">${err.message}</li>`;
        });
}

document.addEventListener('DOMContentLoaded', () => {
    loadChat();
    setInterval(loadChat, 10000); // poll every 10s

    document.addEventListener('submit', e => {
        if (!e.target.matches('.ap-chat-form')) return;
        e.preventDefault();

        const wrapper = e.target.closest('.ap-event-chat');
        if (!wrapper) return;
        const eventId = parseInt(wrapper.dataset.eventId || 0, 10);
        const input = e.target.querySelector('input[name="content"]');
        const content = input.value.trim();
        if (!content || !eventId) return;

        fetch(`/wp-json/artpulse/v1/event/${eventId}/chat`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ content })
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                input.value = '';
                loadChat();
            })
            .catch(err => alert('Chat send error: ' + err.message));
    });
});
