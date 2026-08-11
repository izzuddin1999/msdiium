@php
    $portalAssistantEnabled = request()->user()
        && !request()->user()->canManagePolicies()
        && app(\App\Services\PortalAssistantService::class)->isConfigured();
@endphp

@if($portalAssistantEnabled)
<style>
    .portal-ai-launcher{position:fixed;right:24px;bottom:78px;z-index:1001;width:58px;height:58px;border:0;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#007e74,#12a99b);color:#fff;box-shadow:0 14px 34px rgba(0,91,83,.32);transition:.2s}.portal-ai-launcher:hover{transform:translateY(-3px) scale(1.03)}.portal-ai-launcher .material-icons{font-size:27px}
    .portal-ai-panel{position:fixed;right:24px;bottom:148px;z-index:1002;width:min(410px,calc(100vw - 28px));height:min(620px,calc(100vh - 180px));display:none;grid-template-rows:auto 1fr auto;border:1px solid #cfe2de;border-radius:18px;background:#fff;box-shadow:0 24px 60px rgba(17,57,50,.24);overflow:hidden}.portal-ai-panel.is-open{display:grid}
    .portal-ai-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 17px;background:linear-gradient(120deg,#00796f,#0aa497);color:#fff}.portal-ai-identity{display:flex;align-items:center;gap:11px}.portal-ai-avatar{width:40px;height:40px;display:grid;place-items:center;border-radius:12px;background:rgba(255,255,255,.18)}.portal-ai-identity strong,.portal-ai-identity small{display:block}.portal-ai-identity small{margin-top:2px;color:#d7f4ef;font-size:11px}.portal-ai-close{width:34px;height:34px;border:0;border-radius:9px;background:rgba(255,255,255,.13);color:#fff}
    .portal-ai-messages{padding:17px;overflow-y:auto;background:linear-gradient(#f4faf8,#fff)}.portal-ai-message{max-width:88%;margin-bottom:12px;padding:11px 13px;border-radius:13px;font-size:13px;line-height:1.55;white-space:pre-wrap}.portal-ai-message.assistant{border:1px solid #d9e9e5;border-bottom-left-radius:4px;background:#fff;color:#274b45}.portal-ai-message.user{margin-left:auto;border-bottom-right-radius:4px;background:#087d73;color:#fff}.portal-ai-message.loading{color:#71847f;font-style:italic}.portal-ai-sources{margin:0 0 14px;padding:10px 12px;border-radius:10px;background:#eaf6f3}.portal-ai-sources strong{display:block;margin-bottom:5px;color:#315f57;font-size:10px;text-transform:uppercase;letter-spacing:.07em}.portal-ai-sources a{display:block;padding:3px 0;color:#087d73;font-size:11px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .portal-ai-form{padding:12px;border-top:1px solid #dde9e6;background:#fff}.portal-ai-input-row{display:flex;gap:8px;align-items:flex-end}.portal-ai-form textarea{min-height:46px!important;max-height:110px;resize:none;padding:11px 12px;font-size:13px}.portal-ai-send{flex:0 0 46px;width:46px;height:46px;min-height:46px;padding:0}.portal-ai-note{display:block;margin-top:6px;color:#83918e;font-size:9px;text-align:center}
    @media(max-width:575px){.portal-ai-launcher{right:14px;bottom:70px}.portal-ai-panel{right:14px;bottom:138px;height:calc(100vh - 155px)}}
</style>

<button class="portal-ai-launcher" id="portalAiLauncher" type="button" aria-label="Open portal assistant" title="Ask the portal assistant">
    <span class="material-icons">smart_toy</span>
</button>

<aside class="portal-ai-panel" id="portalAiPanel" aria-label="Portal assistant">
    <header class="portal-ai-head">
        <div class="portal-ai-identity">
            <span class="portal-ai-avatar material-icons">auto_awesome</span>
            <span><strong>Policy Portal Assistant</strong><small>Answers from records you can access</small></span>
        </div>
        <button class="portal-ai-close" id="portalAiClose" type="button" aria-label="Close assistant">
            <span class="material-icons">close</span>
        </button>
    </header>
    <div class="portal-ai-messages" id="portalAiMessages" aria-live="polite">
        <div class="portal-ai-message assistant">Hello! Ask me about policies, circulars, topics, effective dates, requirements, or documents available in this portal.</div>
    </div>
    <form class="portal-ai-form" id="portalAiForm">
        @csrf
        <div class="portal-ai-input-row">
            <textarea class="form-control" id="portalAiQuestion" rows="1" maxlength="1000" placeholder="Ask about the portal..." required></textarea>
            <button class="btn btn-primary portal-ai-send" type="submit" aria-label="Send question"><span class="material-icons">send</span></button>
        </div>
        <small class="portal-ai-note">Read-only AI answers may need verification against the source document.</small>
    </form>
</aside>

<script>
(() => {
    const launcher = document.getElementById('portalAiLauncher');
    const panel = document.getElementById('portalAiPanel');
    const close = document.getElementById('portalAiClose');
    const form = document.getElementById('portalAiForm');
    const input = document.getElementById('portalAiQuestion');
    const messages = document.getElementById('portalAiMessages');
    const send = form?.querySelector('button[type="submit"]');
    const csrfInput = form?.querySelector('input[name="_token"]');
    const endpoint = @json(route('portal-assistant.ask'));
    const csrfEndpoint = @json(route('csrf-token'));
    const history = [];

    const toggle = (open) => {
        panel.classList.toggle('is-open', open);
        launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) setTimeout(() => input.focus(), 50);
    };
    const message = (text, role, extraClass = '') => {
        const node = document.createElement('div');
        node.className = `portal-ai-message ${role} ${extraClass}`;
        node.textContent = text;
        messages.appendChild(node);
        messages.scrollTop = messages.scrollHeight;
        return node;
    };
    const freshToken = async () => {
        const response = await fetch(csrfEndpoint, {credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});
        if (!response.ok) throw new Error('Unable to initialize a secure chat request.');
        const token = (await response.json()).token;
        if (csrfInput) csrfInput.value = token;
        return token;
    };
    const ask = async (token, question) => fetch(endpoint, {
        method:'POST',
        credentials:'same-origin',
        cache:'no-store',
        headers:{
            Accept:'application/json',
            'Content-Type':'application/json',
            'X-Requested-With':'XMLHttpRequest',
            'X-CSRF-TOKEN':token
        },
        body:JSON.stringify({
            _token:token,
            question,
            history:history.slice(0, -1).slice(-6)
        })
    });

    launcher?.addEventListener('click', () => toggle(!panel.classList.contains('is-open')));
    close?.addEventListener('click', () => toggle(false));
    input?.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); form.requestSubmit(); }
    });
    form?.addEventListener('submit', async event => {
        event.preventDefault();
        const question = input.value.trim();
        if (!question) return;
        message(question, 'user');
        history.push({role:'user',text:question});
        input.value = '';
        send.disabled = true;
        const waiting = message('Searching accessible portal records…', 'assistant', 'loading');
        try {
            let token = await freshToken();
            let response = await ask(token, question);
            if (response.status === 419) response = await ask(await freshToken(), question);
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'I could not answer that question.');
            waiting.remove();
            message(payload.answer, 'assistant');
            history.push({role:'assistant',text:payload.answer});
            if (payload.sources?.length) {
                const sources = document.createElement('div');
                sources.className = 'portal-ai-sources';
                const heading = document.createElement('strong');
                heading.textContent = 'Portal sources';
                sources.appendChild(heading);
                payload.sources.forEach(source => {
                    const link = document.createElement('a');
                    link.href = source.url;
                    link.textContent = `${source.title}${source.reference ? ' · '+source.reference : ''} · v${source.version}`;
                    sources.appendChild(link);
                });
                messages.appendChild(sources);
                messages.scrollTop = messages.scrollHeight;
            }
        } catch (error) {
            waiting.textContent = error.message;
            waiting.classList.remove('loading');
        } finally {
            send.disabled = false;
            input.focus();
        }
    });
})();
</script>
@endif
