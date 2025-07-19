document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ap-event-chat').forEach(container => {
        const eventId = container.dataset.eventId || (typeof ArtPulseChatVars !== 'undefined' ? ArtPulseChatVars.event_id : 0);
        const list = container.querySelector('.ap-chat-list');
        const form = container.querySelector('.ap-chat-form');
        let autoScroll = true;

        container.addEventListener('mouseenter', () => { autoScroll = false; });
        container.addEventListener('mouseleave', () => { autoScroll = true; list.scrollTop = list.scrollHeight; });

        function render(msgs) {
            list.innerHTML = '';
            msgs.forEach(msg => {
                const li = document.createElement('li');
                const time = new Intl.DateTimeFormat('en', { timeStyle: 'short' }).format(new Date(msg.created_at));
                li.innerHTML = `<img class="ap-chat-avatar" src="${msg.avatar}" alt=""> <span class="ap-chat-author">${msg.author}</span> <span class="ap-chat-time">${time}</span> <p class="ap-chat-content">${msg.content}</p>`;
                list.appendChild(li);
            });
            if (autoScroll) list.scrollTop = list.scrollHeight;
        }

        function load() {
            fetch(`/wp-json/artpulse/v1/event/${eventId}/chat`, {
                headers: { 'X-WP-Nonce': typeof APChat !== 'undefined' ? APChat.nonce : '' }
            })
                .then(r => r.json())
                .then(render)
                .catch(err => {
                    console.error('Chat load error', err);
                });
        }

        let pollTimer;
        function poll() {
            load();
            pollTimer = setTimeout(() => {
                if (document.body.contains(container)) {
                    poll();
                }
            }, 10000);
        }

        poll();

        const cleanup = () => pollTimer && clearTimeout(pollTimer);
        window.addEventListener('beforeunload', cleanup);

        if (form) {
            form.addEventListener('submit', e => {
                e.preventDefault();
                const txt = form.querySelector('input[name="content"]').value.trim();
                if (!txt) return;
                fetch(`/wp-json/artpulse/v1/event/${eventId}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': APChat.nonce
                    },
                    body: JSON.stringify({ content: txt })
                }).then(() => { form.reset(); load(); });
            });
        }
    });
});
