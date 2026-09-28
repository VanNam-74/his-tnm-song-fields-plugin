/* TNM Song Fields — admin screen */
(function () {
    'use strict';

    const form = document.getElementById('tsf-editor');
    if (!form) return;

    const tbody = form.querySelector('#tsf-rows tbody');
    const template = document.getElementById('tsf-template');
    const preview = document.getElementById('tsf-preview');
    const dirty = document.getElementById('tsf-dirty');
    let counter = 1000;
    let changed = false;

    const slug = (s) => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');

    function markDirty() {
        changed = true;
        if (dirty) dirty.hidden = false;
    }

    /* Live preview of what customers see */
    function renderPreview() {
        if (!preview) return;
        preview.textContent = '';
        const rows = [...tbody.querySelectorAll('.tsf-row')].filter((r) => r.querySelector('.tsf-enabled').checked);
        const chips = [];
        rows.forEach((r) => {
            const label = r.querySelector('.tsf-label').value.trim();
            if (!label) return;
            const extra = r.querySelector('.tsf-extra');
            const chip = document.createElement('span');
            chip.className = 'tsf-chip';
            chip.textContent = label;
            if (extra && extra.value.trim()) {
                const small = document.createElement('small');
                small.textContent = extra.value.trim();
                chip.appendChild(small);
            }
            chips.push(chip);
        });
        if (!chips.length) {
            const empty = document.createElement('p');
            empty.className = 'tsf-preview__empty';
            empty.textContent = 'No choices are shown. Turn on at least one so customers can answer this question.';
            preview.appendChild(empty);
            return;
        }
        chips.forEach((c) => preview.appendChild(c));
    }

    function syncRow(row) {
        row.classList.toggle('is-hidden', !row.querySelector('.tsf-enabled').checked);
        const value = row.querySelector('.tsf-value');
        const label = row.querySelector('.tsf-label');
        if (value && label) value.placeholder = slug(label.value) || 'from label';
    }

    /* Add */
    document.getElementById('tsf-add').addEventListener('click', () => {
        tbody.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__i__/g, 'n' + (counter++)));
        tbody.lastElementChild.querySelector('input, textarea').focus();
        markDirty();
        renderPreview();
    });

    /* Remove */
    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.tsf-remove');
        if (!btn) return;
        if (btn.dataset.new !== '1' && !window.confirm('Delete this choice? Orders that already used it will show its saved value instead of the label. To stop offering it but keep the label, turn off "Shown" instead.')) {
            return;
        }
        btn.closest('.tsf-row').remove();
        markDirty();
        renderPreview();
    });

    const onEdit = (e) => {
        const row = e.target.closest('.tsf-row');
        if (row) syncRow(row);
        markDirty();
        renderPreview();
    };
    tbody.addEventListener('input', onEdit);
    tbody.addEventListener('change', onEdit);

    /* Reorder: drag and drop (only from the handle, so text inputs keep normal selection) */
    let dragging = null;
    let fromHandle = false;
    tbody.addEventListener('mousedown', (e) => { fromHandle = !!e.target.closest('.tsf-move'); });
    tbody.addEventListener('dragstart', (e) => {
        const row = e.target.closest('.tsf-row');
        if (!row || !fromHandle) {
            e.preventDefault();
            return;
        }
        dragging = row;
        row.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    tbody.addEventListener('dragover', (e) => {
        if (!dragging) return;
        e.preventDefault();
        const over = e.target.closest('.tsf-row');
        if (!over || over === dragging) return;
        const box = over.getBoundingClientRect();
        tbody.insertBefore(dragging, e.clientY > box.top + box.height / 2 ? over.nextSibling : over);
    });
    tbody.addEventListener('dragend', () => {
        if (!dragging) return;
        dragging.classList.remove('is-dragging');
        dragging = null;
        markDirty();
        renderPreview();
    });

    /* Reorder: arrow keys on the handle */
    tbody.addEventListener('keydown', (e) => {
        const handle = e.target.closest('.tsf-move');
        if (!handle || (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;
        e.preventDefault();
        const row = handle.closest('.tsf-row');
        const sibling = e.key === 'ArrowUp' ? row.previousElementSibling : row.nextElementSibling;
        if (!sibling) return;
        tbody.insertBefore(row, e.key === 'ArrowUp' ? sibling : sibling.nextSibling);
        handle.focus();
        markDirty();
        renderPreview();
    });

    /* Confirm destructive buttons, warn before leaving with unsaved edits */
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-confirm]');
        if (btn && !window.confirm(btn.dataset.confirm)) e.preventDefault();
    });
    form.addEventListener('submit', () => { changed = false; });
    window.addEventListener('beforeunload', (e) => {
        if (changed) { e.preventDefault(); e.returnValue = ''; }
    });

    tbody.querySelectorAll('.tsf-row').forEach(syncRow);
    renderPreview();
})();
