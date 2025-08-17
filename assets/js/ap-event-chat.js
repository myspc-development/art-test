document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.ap-event-chat').forEach(container => {
        const eventId = container.dataset.eventId || (typeof ArtPulseChatVars !== 'undefined' ? ArtPulseChatVars.event_id : 0);
        const list = container.querySelector('.ap-chat-list');
        const form = container.querySelector('.ap-chat-form');
        let autoScroll = true;

        container.addEventListener('mouseenter', () => { autoScroll = false; });
        container.addEventListener('mouseleave', () => { autoScroll = true; list.scrollTop = list.scrollHeight; });

        function render(msgs) {
            requestAnimationFrame(() => {
                if (!Array.isArray(msgs)) {
                    const li = document.createElement('li');
                    li.textContent = 'Unable to load messages';
                    list.replaceChildren(li);
                    return;
                }
                const frag = document.createDocumentFragment();
                msgs.forEach(msg => {
                    const li = document.createElement('li');
                    const time = new Intl.DateTimeFormat('en', { timeStyle: 'short' }).format(new Date(msg.created_at));
                    li.innerHTML = `<img class="ap-chat-avatar" src="${msg.avatar}" alt=""> <span class="ap-chat-author">${msg.author}</span> <span class="ap-chat-time">${time}</span> <p class="ap-chat-content">${msg.content}</p>`;
                    frag.appendChild(li);
                });
                list.replaceChildren(frag);
                if (autoScroll) list.scrollTop = list.scrollHeight;
            });
        }

        let pollTimer;
        let stopped = false;
        function load() {
            fetch(`/wp-json/artpulse/v1/event/${eventId}/chat`, {
                headers: { 'X-WP-Nonce': typeof APChat !== 'undefined' ? APChat.nonce : '' }
            })
                .then(r => {
                    if (!r.ok) {
                        if (r.status === 404 || (r.status >= 400 && r.status < 500)) {
                            stopped = true;
                            if (pollTimer) clearTimeout(pollTimer);
                        }
                        throw new Error(`Chat load failed: ${r.status}`);
                    }
                    return r.json();
                })
                .then(render)
                .catch(err => {
                    console.error('Chat load error', err);
                });
        }

        function poll() {
            load();
            pollTimer = setTimeout(() => {
                if (!stopped && document.body.contains(container)) {
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
