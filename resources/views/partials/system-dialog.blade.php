<div class="system-dialog" id="systemDialog" hidden>
    <button class="system-dialog-backdrop" type="button" data-dialog-cancel tabindex="-1" aria-label="Close dialog"></button>
    <section class="system-dialog-panel" role="dialog" aria-modal="true" aria-labelledby="systemDialogTitle" aria-describedby="systemDialogMessage" tabindex="-1">
        <header class="system-dialog-header">
            <span class="system-dialog-icon material-icons" aria-hidden="true">warning_amber</span>
            <div>
                <h2 id="systemDialogTitle">Please confirm</h2>
                <small id="systemDialogCaption">Policy &amp; Circular Management</small>
            </div>
            <button class="system-dialog-close" type="button" data-dialog-cancel aria-label="Close dialog"><span class="material-icons">close</span></button>
        </header>
        <div class="system-dialog-body"><p id="systemDialogMessage"></p></div>
        <footer class="system-dialog-footer">
            <button class="btn system-dialog-cancel" type="button" data-dialog-cancel>Cancel</button>
            <button class="btn system-dialog-confirm" type="button" data-dialog-confirm>Confirm</button>
        </footer>
    </section>
</div>
<script>
(() => {
    const root = document.getElementById('systemDialog');
    if (!root || window.PortalDialog) return;

    const panel = root.querySelector('.system-dialog-panel');
    const title = root.querySelector('#systemDialogTitle');
    const message = root.querySelector('#systemDialogMessage');
    const icon = root.querySelector('.system-dialog-icon');
    const cancelButton = root.querySelector('.system-dialog-cancel');
    const confirmButton = root.querySelector('.system-dialog-confirm');
    let resolver = null;
    let previousFocus = null;

    const close = (value) => {
        if (root.hidden) return;
        root.hidden = true;
        document.body.classList.remove('system-dialog-open');
        const resolve = resolver;
        resolver = null;
        previousFocus?.focus?.();
        resolve?.(value);
    };

    const open = (body, options = {}) => new Promise(resolve => {
        if (resolver) close(false);
        resolver = resolve;
        previousFocus = document.activeElement;
        const mode = options.mode === 'alert' ? 'alert' : 'confirm';
        const tone = options.tone || (mode === 'alert' ? 'info' : 'danger');
        root.dataset.tone = tone;
        title.textContent = options.title || (mode === 'alert' ? 'Notice' : 'Please confirm');
        message.textContent = String(body || '');
        icon.textContent = options.icon || (tone === 'danger' ? 'warning_amber' : tone === 'success' ? 'check_circle' : 'info');
        confirmButton.textContent = options.confirmText || (mode === 'alert' ? 'OK' : 'Confirm');
        cancelButton.hidden = mode === 'alert';
        root.hidden = false;
        document.body.classList.add('system-dialog-open');
        requestAnimationFrame(() => confirmButton.focus());
    });

    root.querySelectorAll('[data-dialog-cancel]').forEach(button => button.addEventListener('click', () => close(false)));
    confirmButton.addEventListener('click', () => close(true));
    root.addEventListener('keydown', event => {
        if (event.key === 'Escape') { event.preventDefault(); close(false); return; }
        if (event.key !== 'Tab') return;
        const focusable = [...panel.querySelectorAll('button:not([hidden]):not([disabled]),a[href],input,select,textarea,[tabindex]:not([tabindex="-1"])')];
        if (!focusable.length) return;
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    });

    window.PortalDialog = {
        confirm: (body, options = {}) => open(body, { ...options, mode: 'confirm' }),
        alert: (body, options = {}) => open(body, { ...options, mode: 'alert' }).then(() => undefined),
    };

    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
        const handler = form.getAttribute('onsubmit') || '';
        const match = handler.match(/confirm\((['"])(.*?)\1\)/);
        if (match) form.dataset.confirm = match[2];
        form.removeAttribute('onsubmit');
    });

    const approvedForms = new WeakSet();
    document.addEventListener('submit', async event => {
        const form = event.target.closest?.('form[data-confirm]');
        if (!form) return;
        if (approvedForms.has(form)) { approvedForms.delete(form); return; }
        event.preventDefault();
        const approved = await window.PortalDialog.confirm(form.dataset.confirm, {
            title: form.dataset.confirmTitle || 'Please confirm',
            confirmText: form.dataset.confirmButton || 'Confirm',
            tone: form.dataset.confirmTone || 'danger',
        });
        if (!approved) return;
        approvedForms.add(form);
        if (form.requestSubmit) event.submitter ? form.requestSubmit(event.submitter) : form.requestSubmit();
        else form.submit();
    }, true);
})();
</script>
