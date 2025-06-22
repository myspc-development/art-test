document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('ap-csv-file');
    const mappingStep = document.getElementById('ap-mapping-step');
    const startBtn = document.getElementById('ap-start-import');
    const statusPre = document.getElementById('ap-import-status');
    if (!fileInput) return;

    let parsedData = [];
    let headers = [];

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            complete: function (results) {
                parsedData = results.data;
                headers = results.meta.fields;
                buildMapping();
                buildPreview();
            }
        });
    });

    function buildMapping() {
        mappingStep.innerHTML = '';
        const table = document.createElement('table');
        headers.forEach(h => {
            const row = document.createElement('tr');
            const label = document.createElement('td');
            label.textContent = h;
            row.appendChild(label);
            const select = document.createElement('select');
            select.innerHTML = '<option value="">Ignore</option>' +
                '<option value="post_title">Title</option>' +
                '<option value="post_content">Content</option>' +
                '<option value="meta">Meta Field</option>';
            const input = document.createElement('input');
            input.type = 'text';
            input.value = h;
            input.style.display = 'none';
            select.addEventListener('change', () => {
                input.style.display = select.value === 'meta' ? 'block' : 'none';
            });
            const tdSelect = document.createElement('td');
            tdSelect.appendChild(select);
            tdSelect.appendChild(input);
            row.appendChild(tdSelect);
            table.appendChild(row);
        });
        const postTypeRow = document.createElement('tr');
        const ptLabel = document.createElement('td');
        ptLabel.textContent = 'Post Type';
        const ptSelectTd = document.createElement('td');
        const ptSelect = document.createElement('select');
        ptSelect.id = 'ap-post-type';
        ['artpulse_org','artpulse_event','artpulse_artist','artpulse_artwork'].forEach(pt => {
            const opt = document.createElement('option');
            opt.value = pt;
            opt.textContent = pt;
            ptSelect.appendChild(opt);
        });
        ptSelectTd.appendChild(ptSelect);
        postTypeRow.appendChild(ptLabel);
        postTypeRow.appendChild(ptSelectTd);
        table.appendChild(postTypeRow);
        mappingStep.appendChild(table);
        startBtn.disabled = false;
    }

    function buildPreview() {
        let preview = document.getElementById('ap-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.id = 'ap-preview';
            mappingStep.appendChild(preview);
        }
        preview.innerHTML = '';
        const table = document.createElement('table');
        const headRow = document.createElement('tr');
        headers.forEach(h => {
            const th = document.createElement('th');
            th.textContent = h;
            headRow.appendChild(th);
        });
        table.appendChild(headRow);
        parsedData.slice(0,5).forEach(row => {
            const tr = document.createElement('tr');
            headers.forEach(h => {
                const td = document.createElement('td');
                td.textContent = row[h];
                tr.appendChild(td);
            });
            table.appendChild(tr);
        });
        preview.appendChild(table);
    }

    async function sendChunks() {
        const selects = mappingStep.querySelectorAll('select');
        const inputs = mappingStep.querySelectorAll('input[type="text"]');
        const map = {};
        headers.forEach((h, idx) => {
            const val = selects[idx].value;
            if (!val) return;
            if (val === 'meta') {
                map[h] = { field: 'meta', metaKey: inputs[idx].value };
            } else {
                map[h] = { field: val };
            }
        });
        const postType = document.getElementById('ap-post-type').value;
        const chunkSize = 20;
        for (let i = 0; i < parsedData.length; i += chunkSize) {
            const rows = parsedData.slice(i, i + chunkSize).map(r => {
                const obj = { post_type: postType };
                Object.keys(map).forEach(col => {
                    const conf = map[col];
                    if (conf.field === 'meta') {
                        obj[conf.metaKey] = r[col];
                    } else {
                        obj[conf.field] = r[col];
                    }
                });
                return obj;
            });
            await fetch(APCSVImport.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': APCSVImport.nonce
                },
                body: JSON.stringify({ post_type: postType, rows: rows })
            }).then(r => r.json())
              .then(data => {
                  statusPre.textContent += 'Imported ' + (data.created ? data.created.length : 0) + " posts\n";
              });
        }
    }

    startBtn.addEventListener('click', () => {
        startBtn.disabled = true;
        statusPre.textContent = '';
        sendChunks();
    });
});
