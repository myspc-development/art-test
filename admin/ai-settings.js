(function() {
    function callApi(path, payload) {
        const output = document.getElementById('ap-ai-response');
        output.textContent = 'Loading...';
        fetch(ArtPulseAI.rest + path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': ArtPulseAI.nonce
            },
            body: JSON.stringify(payload)
        })
            .then((r) => r.json())
            .then((data) => {
                output.textContent = JSON.stringify(data, null, 2);
            })
            .catch((err) => {
                output.textContent = 'Error: ' + err;
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('ap-ai-input');
        document.getElementById('ap-ai-tag').addEventListener('click', function () {
            callApi('tag', { text: input.value });
        });
        document.getElementById('ap-ai-summary').addEventListener('click', function () {
            callApi('bio-summary', { bio: input.value });
        });
    });
})();
