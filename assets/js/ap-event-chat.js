function loadChat() {
    const eventId =
        typeof ArtPulseChatVars !== 'undefined' ? ArtPulseChatVars.event_id : 2312;

    fetch(`/wp-json/artpulse/v1/event/${eventId}/chat`, {
        headers: {
            'X-WP-Nonce': typeof APChat !== 'undefined' ? APChat.nonce : ''
        }
    })
        .then(res => {
            if (!res.ok) throw new Error(`Failed to load chat: HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!Array.isArray(data)) throw new Error('Invalid chat format');

            const container = document.getElementById('ap-event-chat');
            if (!container) return;

            container.innerHTML = data.map(entry => `
                <div class="chat-message">
                    <strong>${entry.user}:</strong> ${entry.msg}
                </div>
            `).join('');
        })
        .catch(err => {
            console.error('Chat load error:', err.message);
            const container = document.getElementById('ap-event-chat');
            if (container) container.innerHTML = `<p class="error">${err.message}</p>`;
        });
}

document.addEventListener('DOMContentLoaded', () => {
    loadChat();
    setInterval(loadChat, 10000); // poll every 10s
});
