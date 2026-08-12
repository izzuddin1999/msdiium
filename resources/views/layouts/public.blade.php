<!doctype html>
<html lang="en" data-theme="iium-directory">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="IIUM public policy, guideline and circular directory">
    <title>@yield('title', 'Policy & Circular Directory') | IIUM</title>
    <link rel="shortcut icon" type="image/png" href="https://style.iium.edu.my/images/iium/iium-logo.png">
    <link href="https://style.iium.edu.my/css/style.css" rel="stylesheet">
    <link href="https://style.iium.edu.my/css/iium.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://style.iium.edu.my/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public-portal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-theme.css') }}">
</head>
<body>
@php($publicPageTitle = request()->routeIs('public.documents.show')
    ? 'Document Details'
    : (request()->routeIs('public-portal') && request()->user()
        ? 'Dashboard'
        : ($pageMeta['title'] ?? 'Public Directory')))
<div class="background-image" aria-hidden="true"></div>
<div class="portal-shell iium-portal-template" id="portalShell">
    <div class="sidebar-scrim" data-close-menu></div>
    <aside class="portal-sidebar" aria-label="Public portal navigation">
        <a class="portal-brand" href="{{ route('public-portal') }}" aria-label="IIUM Policy and Circular Directory home">
            <img src="https://style.iium.edu.my/images/iium/iium-logo-v2.png" alt="International Islamic University Malaysia">
        </a>
        <div class="portal-name">{{ request()->user() ? 'Policy Modules' : 'Navigation' }}</div>
        <nav class="sidebar-nav">
            <a class="{{ (request()->user() ? request()->routeIs('public-portal', 'public.msd', 'public.kcdiom') : request()->routeIs('public-portal')) ? 'active' : '' }}" href="{{ route('public-portal') }}"><span class="material-icons">home</span><span>Dashboard</span></a>
            @if(request()->user())
                <a href="{{ route('policy-documents.index') }}"><span class="material-icons">description</span><span>Documents</span></a>
                @if(request()->user()?->canManagePolicies())
                    <a href="{{ route('topic-categories.index') }}"><span class="material-icons">account_tree</span><span>Topics</span></a>
                    <a href="{{ route('lookup-values.index') }}"><span class="material-icons">tune</span><span>Lookup Values</span></a>
                    @if(config('features.form_builder'))<a href="{{ route('form-templates.index') }}"><span class="material-icons">dashboard_customize</span><span>Form Builder</span></a>@endif
                    @if(request()->user()?->canAdministerAccess())
                        <a href="{{ route('roles.index') }}"><span class="material-icons">admin_panel_settings</span><span>User Roles</span></a>
                        <a href="{{ route('document-activity-logs.index') }}"><span class="material-icons">fact_check</span><span>Document Audit Log</span></a>
                        <a href="{{ route('directory-sync.index') }}"><span class="material-icons">sync</span><span>CAS/HURIS Sync</span></a>
                    @endif
                @endif
                <div class="nav-label">Insights</div>
                @if(request()->user()?->canManagePolicies())
                    @if(request()->user()?->canAdministerAccess())<a href="{{ route('reports.user-access') }}"><span class="material-icons">manage_accounts</span><span>User Access Report</span></a>@endif
                @endif
                <a href="{{ route('reports.versions') }}"><span class="material-icons">history</span><span>Versioning Report</span></a>
                <a href="{{ route('notifications.index') }}"><span class="material-icons">notifications</span><span>Notifications</span></a>
            @else
                <a href="{{ route('public-portal') }}#directory"><span class="material-icons">description</span><span>Browse Documents</span></a>
                <a href="{{ route('public-portal') }}#advanced-search"><span class="material-icons">search</span><span>Advanced Search</span></a>
                <div class="nav-label">Browse by directory</div>
                <a class="{{ request()->routeIs('public.msd') ? 'active' : '' }}" href="{{ route('public.msd') }}"><span class="material-icons">account_balance</span><span>MSD Directory</span></a>
                <a class="{{ request()->routeIs('public.kcdiom') ? 'active' : '' }}" href="{{ route('public.kcdiom') }}"><span class="material-icons">domain</span><span>KCDIOM Directory</span></a>
                <a href="{{ route('public-portal') }}#about"><span class="material-icons">info_outline</span><span>About This Portal</span></a>
                <a href="{{ route('public-portal') }}#help"><span class="material-icons">help_outline</span><span>Help &amp; FAQ</span></a>
            @endif
        </nav>
        <form class="portal-viewer-switch" action="{{ route('viewer-session.store') }}" method="POST">
            @csrf
            <label for="portal_viewer_user_id">Demo viewer</label>
            <div><select id="portal_viewer_user_id" name="user_id" aria-label="Demo viewer"><option value="public" @selected(!request()->user())>Public View</option>@foreach(\App\Models\User::query()->where('is_active', true)->orderBy('name')->get() as $option)<option value="{{ $option->id }}" @selected(request()->user()?->id === $option->id)>{{ $option->name }}</option>@endforeach</select><button type="submit" aria-label="Switch viewer"><span class="material-icons">swap_horiz</span></button></div>
        </form>
    </aside>

    <div class="portal-main">
        <header class="portal-header">
            <button class="menu-button" type="button" data-toggle-menu aria-label="Toggle navigation" aria-expanded="false"><span class="material-icons">menu</span></button>
            @if(request()->user())
                <div class="unified-header-tools">
                    <a class="header-notification" href="{{ route('notifications.index') }}" aria-label="Notifications"><span class="material-icons">mail_outline</span></a>
                    <div class="dropdown public-header-profile">
                        <button class="staff-shortcut staff-profile-link" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open profile menu">
                            <span class="unified-avatar">{{ strtoupper(substr(request()->user()->name, 0, 1)) }}</span>
                            <span><strong>{{ request()->user()->name }}</strong><small>{{ request()->user()->actorLabel() }}</small></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end public-profile-menu">
                            <div class="public-profile-summary"><span class="public-profile-initial">{{ strtoupper(substr(request()->user()->name, 0, 1)) }}</span><span><strong>{{ request()->user()->name }}</strong><small>{{ request()->user()->actorLabel() }}</small></span></div>
                            @if(request()->user()->canManagePolicies())
                                <a href="{{ route('organization-profile.show') }}"><span class="material-icons">domain</span>{{ request()->user()->organizationCode() }} Profile</a>
                            @endif
                            <a href="{{ route('notifications.index') }}"><span class="material-icons">notifications</span>Notifications</a>
                            <a href="{{ route('dashboard') }}"><span class="material-icons">dashboard</span>Dashboard</a>
                        </div>
                    </div>
                    <form class="header-signout" action="{{ route('staff-portal.logout') }}" method="POST">
                        @csrf
                        <button type="submit" aria-label="Sign out" title="Sign out"><span class="material-icons">logout</span></button>
                    </form>
                </div>
            @endif
        </header>
        <div class="page-bar {{ request()->routeIs('public-portal') && request()->user()?->canManagePolicies() ? 'admin-page-bar' : '' }}">
            <h1>{{ $publicPageTitle }}</h1>
            <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('public-portal') }}"><span class="material-icons">home</span>Home</a><span>/</span><strong>{{ $publicPageTitle }}</strong></nav>
        </div>

        <main>@yield('content')</main>

        <div class="iium-footer-links"><a href="https://www.iium.edu.my" target="_blank" rel="noopener">IIUM Website</a><span aria-hidden="true">||</span><a href="https://www.iium.edu.my/disclaimer" target="_blank" rel="noopener">Disclaimers</a></div>
        <footer class="iium-footer"><p>Copyright &copy; {{ now()->year }} International Islamic University Malaysia, Realized by Information Technology Division</p></footer>
    </div>
</div>
@include('partials.portal-assistant')
<button class="back-to-top" type="button" aria-label="Back to top"><span class="material-icons">arrow_upward</span></button>
<script src="https://style.iium.edu.my/vendor/global/global.min.js"></script>
<script src="https://style.iium.edu.my/js/custom.js"></script>
<script src="https://style.iium.edu.my/js/deznav-init.js"></script>
<script src="https://style.iium.edu.my/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script>
(() => {
    const shell = document.getElementById('portalShell');
    const toggle = document.querySelector('[data-toggle-menu]');
    const closeMenu = () => { shell.classList.remove('menu-open'); toggle.setAttribute('aria-expanded', 'false'); };
    toggle.addEventListener('click', () => { const open = shell.classList.toggle('menu-open'); toggle.setAttribute('aria-expanded', String(open)); });
    document.querySelector('[data-close-menu]').addEventListener('click', closeMenu);
    document.querySelectorAll('.sidebar-nav a').forEach(link => link.addEventListener('click', closeMenu));
    const topButton = document.querySelector('.back-to-top');
    window.addEventListener('scroll', () => topButton.classList.toggle('visible', window.scrollY > 500), { passive: true });
    topButton.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    document.querySelector('.portal-viewer-switch')?.addEventListener('submit', () => sessionStorage.setItem('portal-viewer-reset-scroll', '1'));
    if (sessionStorage.getItem('portal-viewer-reset-scroll') === '1') {
        sessionStorage.removeItem('portal-viewer-reset-scroll');
        history.scrollRestoration = 'manual';
        window.scrollTo(0, 0);
    }
})();
</script>
</body>
</html>
