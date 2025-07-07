document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('ap-csv-file');
    const mappingStep = document.getElementById('ap-mapping-step');
    const startBtn = document.getElementById('ap-start-import');
    const statusPre = document.getElementById('ap-import-status');
    const headerInput = document.getElementById('ap-csv-has-header');
    const delimiterSelect = document.getElementById('ap-csv-delimiter');
    const delimiterCustom = document.getElementById('ap-csv-delimiter-custom');
    const skipInput = document.getElementById('ap-csv-skip');
    const trimInput = document.getElementById('ap-trim-whitespace');
    const saveTemplateBtn = document.getElementById('ap-save-template');

    if (delimiterSelect) {
        delimiterSelect.addEventListener('change', () => {
            delimiterCustom.style.display = delimiterSelect.value === 'custom' ? 'inline-block' : 'none';
        });
    }
    if (!fileInput) return;

    let parsedData = [];
    let headers = [];
    let rawRows = [];

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        let delim = ',';
        if (delimiterSelect) {
            switch (delimiterSelect.value) {
                case 'tab':
                    delim = '\t';
                    break;
                case 'custom':
                    delim = delimiterCustom.value || ',';
                    break;
                default:
                    delim = delimiterSelect.value;
            }
        }

        Papa.parse(file, {
            header: false,
            delimiter: delim,
            skipEmptyLines: true,
            complete: function (results) {
                const skip = parseInt(skipInput?.value || '0', 10) || 0;
                let rows = results.data.slice(skip);
                if (headerInput?.checked) {
                    headers = rows.shift();
                } else {
                    headers = rows[0].map((_, idx) => 'Column ' + (idx + 1));
                }
                rawRows = rows;
                parsedData = rows.map(r => {
                    const obj = {};
                    headers.forEach((h, idx) => {
                        obj[h] = r[idx];
                    });
                    return obj;
                });
                buildMapping();
                buildPreview();
            }
        });
    });

    function buildMapping() {
        mappingStep.innerHTML = '';
        const table = document.createElement('table');
        headers.forEach((h, idx) => {
            const row = document.createElement('tr');
            const label = document.createElement('td');
            label.textContent = h;
            row.appendChild(label);

            const renameTd = document.createElement('td');
            const renameInput = document.createElement('input');
            renameInput.type = 'text';
            renameInput.className = 'ap-rename-header';
            renameInput.value = h;
            renameInput.addEventListener('input', () => {
                const old = headers[idx];
                const val = renameInput.value || old;
                headers[idx] = val;
                label.textContent = val;
                parsedData.forEach(r => {
                    r[val] = r[old];
                    delete r[old];
                });
                buildPreview();
            });
            renameTd.appendChild(renameInput);
            row.appendChild(renameTd);

            const select = document.createElement('select');
            select.innerHTML = '<option value="">Ignore</option>' +
                '<option value="post_title">Title</option>' +
                '<option value="post_content">Content</option>' +
                '<option value="meta">Meta Field</option>';
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'ap-meta-key';
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
        ptSelect.addEventListener('change', () => loadTemplate(ptSelect.value));
        ptSelectTd.appendChild(ptSelect);
        postTypeRow.appendChild(ptLabel);
        postTypeRow.appendChild(ptSelectTd);
        table.appendChild(postTypeRow);
        mappingStep.appendChild(table);
        startBtn.disabled = false;
        loadTemplate(ptSelect.value);
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
        const inputs = mappingStep.querySelectorAll('.ap-meta-key');
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
                body: JSON.stringify({ post_type: postType, rows: rows, trim_whitespace: trimInput?.checked })
            }).then(r => r.json())
              .then(data => {
                  statusPre.textContent += 'Imported ' + (data.created ? data.created.length : 0) + " posts\n";
              });
        }
    }

    async function loadTemplate(postType) {
        if (!APCSVImport.templateBase) return;
        const resp = await fetch(`${APCSVImport.templateBase}/${postType}`);
        const data = await resp.json();
        if (!data || !data.mapping) return;
        const selects = mappingStep.querySelectorAll('select');
        const inputs = mappingStep.querySelectorAll('.ap-meta-key');
        headers.forEach((h, idx) => {
            const conf = data.mapping[h];
            if (!conf) return;
            selects[idx].value = conf.field;
            if (conf.field === 'meta') {
                inputs[idx].style.display = 'block';
                inputs[idx].value = conf.metaKey || '';
            }
        });
        if (trimInput) trimInput.checked = !!data.trim;
    }

    if (saveTemplateBtn) {
        saveTemplateBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            const selects = mappingStep.querySelectorAll('select');
            const inputs = mappingStep.querySelectorAll('.ap-meta-key');
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
            await fetch(`${APCSVImport.templateBase}/${postType}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': APCSVImport.nonce
                },
                body: JSON.stringify({ mapping: map, trim: trimInput?.checked })
            });
            alert('Template saved');
        });
    }

    startBtn.addEventListener('click', () => {
        startBtn.disabled = true;
        statusPre.textContent = '';
        sendChunks();
    });
});
