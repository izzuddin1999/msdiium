<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Policy & Circular Management' }}</title>

    <link rel="shortcut icon" type="image/png" href="https://style.iium.edu.my/images/iium/iium-logo.png">
    <link href="https://style.iium.edu.my/css/style.css" rel="stylesheet">
    <link href="https://style.iium.edu.my/css/iium.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://style.iium.edu.my/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <style>
        :root { --flow-primary:#008f86; --flow-primary-dark:#087064; --flow-accent:#159cf5; --flow-dark:#12372f; --flow-soft:#eef8f6; --flow-border:#d8e6e2; --flow-text:#263b36; --flow-muted:#6f817c; --flow-shadow:0 12px 32px rgba(20,74,63,.08); }
        * { scrollbar-width:thin; scrollbar-color:#b7cfca transparent; }
        html { scroll-behavior:smooth; }
        body { color:var(--flow-text); }
        body:before{display:none}
        .header { background:#00928f!important; }
        .nav-header { background:#fff!important; }
        .content-body { background:transparent; min-height:calc(100vh - 80px); height:auto!important; overflow:visible; }
        .content-body .container-fluid { width:100%; max-width:none; padding:1.875rem 1.875rem 0; }
        .content-body .container-fluid::after { content:""; display:block; clear:both; }
        .content-body .card { height:auto!important; min-height:0!important; }
        .footer { position:relative!important; inset:auto!important; clear:both; margin-top:auto; }
        .footer .copyright { padding:16px 24px; }.footer .copyright p { margin:0; text-align:center; }
        .nav-header .brand-logo{padding:12px 18px}.nav-header .brand-logo img{width:100%;max-width:150px!important;height:auto;object-fit:contain}
        .nav-header .nav-control{cursor:pointer}.header .header-content{padding-left:20px}.header-left{display:flex;align-items:center;gap:15px}
        .header-tools{display:flex;align-items:center;gap:14px}.header-mail{display:grid;place-items:center;width:37px;height:37px;border:2px solid rgba(255,255,255,.9);border-radius:10px;color:#fff}.header-mail .material-icons{font-size:20px}.header-profile{position:relative}.header-profile>a{display:flex;align-items:center;gap:10px;color:#fff}.header-profile .dropdown-menu{min-width:250px;padding:0;border:0;border-radius:0 0 8px 8px;box-shadow:0 10px 30px rgba(21,48,43,.18)}.profile-summary{display:flex;align-items:center;gap:12px;padding:15px}.profile-summary strong,.profile-summary small{display:block}.profile-summary small{color:#788680}.profile-menu-link{display:flex;align-items:center;gap:10px;padding:12px 16px;border-top:1px solid #edf0ef;color:#40544f}.profile-menu-link:hover{background:#f4faf8;color:#008f85}.profile-menu-link .material-icons{font-size:20px}
        .deznav .metismenu a { border-radius:0; margin:0; padding:11px 26px; display:flex; align-items:center; gap:12px; color:#323b39; }
        .deznav .metismenu a.active { background:#eef8f6; color:#0096e8; font-weight:650; }
        .deznav .metismenu a.active:before{content:"";position:absolute;left:0;width:3px;height:28px;border-radius:0 3px 3px 0;background:#009c92}
        .deznav .menu-icon{width:24px;height:24px;display:grid;place-items:center;flex:0 0 24px}.deznav .menu-icon .material-icons { font-size:23px; color:#202b39 }.deznav .metismenu a.active .material-icons{color:#0096e8}.deznav .nav-text{font-size:14px}
        #main-wrapper.menu-toggle .deznav .nav-text{display:none!important}
        #main-wrapper.menu-toggle .deznav .metismenu>li>a{justify-content:center;padding-left:0;padding-right:0;gap:0}
        #main-wrapper.menu-toggle .deznav .menu-title,#main-wrapper.menu-toggle .viewer-switch{display:none!important}
        #main-wrapper.menu-toggle .deznav .menu-icon{margin:0!important}
        .deznav .menu-title{color:#1294ed!important;font-weight:500!important;letter-spacing:0!important;text-transform:none!important}
        .header-context { display:flex; align-items:center; gap:10px; color:#fff; }
        .header-context small { display:block; opacity:.75; }
        .avatar-circle { width:38px; height:38px; display:grid; place-items:center; border-radius:50%; background:rgba(255,255,255,.16); font-weight:800; }
        .page-heading { display:flex; justify-content:space-between; align-items:flex-end; gap:20px; margin-bottom:24px; }
        .page-heading h2 { margin:3px 0 4px; color:var(--flow-dark); font-weight:750; }
        .page-heading p { margin:0; color:#6c7f79; }
        .eyebrow { color:var(--flow-primary); text-transform:uppercase; letter-spacing:.12em; font-weight:700; font-size:11px; }
        .action-primary { display:flex; gap:7px; align-items:center; white-space:nowrap; }
        .action-primary .material-icons { font-size:19px; }
        .metric-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:24px; }
        .metric-grid-3 { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .metric-card { position:relative; isolation:isolate; background:#fff; border:1px solid var(--flow-border); border-radius:16px; padding:20px; display:flex; align-items:center; gap:15px; box-shadow:var(--flow-shadow); overflow:hidden; transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease; }
        .metric-card:after { content:""; position:absolute; z-index:-1; width:90px; height:90px; right:-38px; top:-42px; border-radius:50%; background:var(--flow-soft); }
        .metric-card:hover { transform:translateY(-3px); border-color:#bddbd4; box-shadow:0 16px 38px rgba(20,74,63,.12); }
        .metric-card>.material-icons { width:48px; height:48px; display:grid; place-items:center; border-radius:12px; background:var(--flow-soft); color:var(--flow-primary); }
        .metric-card strong { display:block; font-size:25px; line-height:1; color:var(--flow-dark); }
        .metric-card small { display:block; margin-top:7px; color:#73837e; }
        .metric-card.green>.material-icons { background:#eaf7ec; color:#278343; }.metric-card.amber>.material-icons{background:#fff5df;color:#a46a00}.metric-card.blue>.material-icons{background:#eaf2ff;color:#3569b7}
        .flow-card { height:auto!important; min-height:0!important; border:1px solid var(--flow-border); border-radius:16px; overflow:hidden; box-shadow:var(--flow-shadow); background:#fff; }
        .flow-card .card-header { padding:18px 20px; background:#fff; border-bottom:1px solid var(--flow-border); }
        .flow-card .card-header h5 { margin:0 0 3px; color:var(--flow-dark); }.flow-card .card-header small{color:#7b8b86}
        .card-header-row { display:flex; justify-content:space-between; align-items:center; }.card-header-row>a{display:flex;align-items:center;gap:4px;font-weight:650}.card-header-row>a .material-icons{font-size:17px}
        .document-row { display:grid; grid-template-columns:44px minmax(0,1fr) auto 24px; align-items:center; gap:12px; padding:15px 20px; border-bottom:1px solid #edf1ef; color:inherit; transition:.15s; }
        .document-row:hover { background:#f5faf8; color:inherit; }.document-row:last-child{border-bottom:0}
        .document-icon { width:40px;height:40px;display:grid;place-items:center;border-radius:10px;background:#edf5ff;color:#4774ad}.document-icon.circular{background:#fff1e8;color:#b35b20}
        .document-main strong,.document-main small{display:block}.document-main small{color:#7a8984;margin-top:3px}.row-arrow{color:#a4b0ac}
        .status-pill { font-size:11px; font-weight:750; padding:5px 9px; border-radius:999px; background:#eef1f0; }.status-published{background:#e6f5e9;color:#26753b}.status-draft{background:#fff3d9;color:#8b650d}.status-superseded{background:#eee9fb;color:#6546a8}.status-archived{background:#ebedef;color:#5e656b}.status-inactive{background:#fde9e8;color:#9b3d36}
        .quick-actions a { display:flex; gap:13px; padding:15px 18px; border-bottom:1px solid #edf1ef; color:inherit; }.quick-actions a:last-child{border-bottom:0}.quick-actions a:hover{background:#f5faf8}.quick-actions .material-icons{color:var(--flow-primary)}.quick-actions strong,.quick-actions small{display:block}.quick-actions small{color:#7c8a86;margin-top:3px}
        .compact-list a { display:flex;justify-content:space-between;gap:12px;padding:13px 18px;border-bottom:1px solid #edf1ef;color:inherit}.compact-list a:last-child{border-bottom:0}.compact-list small{color:#a36c0b}.empty-state{text-align:center;padding:45px;color:#84928e}.empty-state .material-icons{font-size:38px}.empty-compact{padding:22px;color:#84928e}
        .chart-list{padding:12px 20px 20px}.chart-row{margin-top:14px}.chart-meta{display:flex;justify-content:space-between;gap:15px;margin-bottom:7px;font-size:13px}.chart-meta strong{color:var(--flow-dark)}.chart-track{height:9px;background:#edf2f0;border-radius:99px;overflow:hidden}.chart-track span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,var(--flow-primary),#47a58f)}
        .flow-toolbar { background:#fff; border:1px solid var(--flow-border); border-radius:16px; padding:18px; margin-bottom:22px; box-shadow:var(--flow-shadow); }
        .table-responsive { overflow-x:auto; border-radius:inherit; }
        .flow-table { margin-bottom:0; min-width:760px; }
        .flow-table thead th { position:sticky; top:0; z-index:1; background:#edf6f4; color:#506963; font-size:11px; text-transform:uppercase; letter-spacing:.055em; border-bottom:1px solid var(--flow-border); white-space:nowrap; }.flow-table td{vertical-align:middle;padding-top:14px;padding-bottom:14px}.flow-table tbody tr{transition:background .15s ease}.flow-table tbody tr:hover{background:#f7fbfa}
        .pagination{margin:0;display:flex;flex-wrap:wrap;gap:4px;align-items:center}.pagination .page-item{margin:0}.pagination .page-link{min-width:36px;height:36px;padding:7px 11px;display:grid;place-items:center;border:1px solid var(--flow-border);border-radius:7px!important;color:var(--flow-primary);background:#fff}.pagination .page-item.active .page-link{border-color:var(--flow-primary);background:var(--flow-primary);color:#fff}.pagination .page-item.disabled .page-link{color:#a5afac;background:#f4f6f5}.pagination svg{width:16px!important;height:16px!important;max-width:16px!important;max-height:16px!important}.card-footer nav{width:100%;display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}.card-footer nav>div{margin:0!important}
        .breadcrumb-flow { display:none!important; }
        .page-titles .breadcrumb-item a{display:flex;align-items:center;gap:7px}.page-titles .breadcrumb-item.active a{color:#159cf5}
        .form-section { border-top:1px solid var(--flow-border); padding-top:20px; margin-top:8px; }.form-section-title{margin-bottom:14px}.form-section-title h6{margin:0;color:var(--flow-dark)}.form-section-title small{color:#7c8b87}
        .form-control,.form-select,.bootstrap-select>.dropdown-toggle { min-height:46px; border-color:#cfded9; border-radius:10px; background-color:#fff; transition:border-color .15s ease,box-shadow .15s ease; }
        textarea.form-control { min-height:110px; }
        .form-control:focus,.form-select:focus,.bootstrap-select>.dropdown-toggle:focus { border-color:var(--flow-primary); box-shadow:0 0 0 3px rgba(0,143,134,.13)!important; }
        label,.form-label { color:var(--flow-dark); font-weight:650; }
        .btn { min-height:42px; border-radius:9px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; gap:7px; transition:transform .15s ease,box-shadow .15s ease,filter .15s ease; }
        .btn:hover { transform:translateY(-1px); box-shadow:0 7px 16px rgba(20,74,63,.13); }
        .btn-primary { background:linear-gradient(135deg,var(--flow-accent),#118ee1); border-color:transparent; }
        .btn-success { background:linear-gradient(135deg,#31c879,#21aa63); border-color:transparent; }
        .alert { border:0; border-left:4px solid currentColor; border-radius:10px; box-shadow:0 5px 18px rgba(20,74,63,.06); }
        .card { border-color:var(--flow-border); }.card-header { min-height:64px; }
        .page-actions { display:flex; flex-wrap:wrap; gap:9px; align-items:center; }
        .viewer-switch { margin:20px 14px; padding:14px; border:1px solid var(--flow-border); border-radius:10px; background:#f7fbf9; }
        .viewer-switch label { display:block; margin-bottom:7px; color:#557068; font-size:11px; font-weight:750; text-transform:uppercase; letter-spacing:.05em; }
        .viewer-switch-row { display:flex; gap:7px; }
        .viewer-switch select { min-width:0; height:36px; padding:5px 8px; font-size:12px; }
        .viewer-switch button { width:38px; height:36px; padding:0; display:grid; place-items:center; }
        .viewer-switch button .material-icons { font-size:18px; }
        .back-to-top { position:fixed; right:20px; bottom:20px; z-index:99; width:44px; height:44px; padding:0; border:0; border-radius:50%; background:var(--flow-primary); color:#fff; box-shadow:0 10px 25px rgba(0,109,99,.28); opacity:0; visibility:hidden; transform:translateY(10px); transition:.2s ease; }
        .back-to-top.is-visible { opacity:1; visibility:visible; transform:none; }.back-to-top .material-icons { font-size:21px; }
        @media(max-width:1199px){.content-body .container-fluid{max-width:none}.flow-table{min-width:900px}}
        @media(max-width:991px){.metric-grid{grid-template-columns:repeat(2,1fr)}.page-heading{align-items:flex-start;flex-direction:column}.header-context small{display:none}}
        @media(max-width:767px){.breadcrumb-flow{overflow-x:auto;white-space:nowrap;margin-top:-20px}.header-tools{gap:7px}.header-profile .header-context{display:none}.page-heading h2{font-size:25px}.page-heading .btn{width:100%}.page-actions{width:100%}.page-actions .btn{flex:1}.flow-toolbar{padding:14px}.card-header-row{align-items:flex-start;gap:10px}.content-body .container-fluid{padding-left:12px;padding-right:12px}.footer .copyright p{font-size:12px}}
        @media(max-width:575px){.metric-grid{grid-template-columns:1fr}.metric-card{padding:16px}.document-row{grid-template-columns:40px minmax(0,1fr);}.document-row .status-pill,.document-row .row-arrow{display:none}.nav-header .brand-logo{padding:10px}.btn{white-space:normal}.content-body .container-fluid{padding:1rem}}
        @media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}*,*:before,*:after{transition-duration:.01ms!important;animation-duration:.01ms!important;animation-iteration-count:1!important}}
    </style>
</head>
<body>
<div class="background-image" aria-hidden="true"></div>
@php
    $portalPageTitle = match (true) {
        request()->routeIs('dashboard') => 'Dashboard',
        request()->routeIs('policy-documents.create') => 'Register Document',
        request()->routeIs('policy-documents.edit') => 'Edit Document',
        request()->routeIs('policy-documents.show') => 'Document Details',
        request()->routeIs('policy-documents.*') => 'Documents',
        request()->routeIs('topic-categories.*') => 'Topics',
        request()->routeIs('lookup-values.*') => 'Lookup Values',
        request()->routeIs('roles.*') => 'User Roles',
        request()->routeIs('document-activity-logs.*') => 'Document Audit Log',
        request()->routeIs('directory-sync.*') => 'CAS/HURIS Sync',
        request()->routeIs('reports.dashboard') => 'Reporting Dashboard',
        request()->routeIs('reports.user-access*') => 'User Access Report',
        request()->routeIs('reports.circulars') => 'Circular Report',
        request()->routeIs('reports.versions') => 'Versioning Report',
        request()->routeIs('notifications.*') => 'Notifications',
        default => 'Policy & Circular Management',
    };
@endphp
<div id="main-wrapper" class="show">
    <div class="nav-header">
        <a href="{{ route('policy-documents.index') }}" class="brand-logo">
            <img src="https://style.iium.edu.my/images/iium/iium-logo-v2.png" class="user_img" style="max-width: 75%" alt="IIUM">
        </a>
        <div class="nav-control" aria-label="Toggle navigation" role="button" tabindex="0"><div class="hamburger"><span class="line"></span><span class="line"></span><span class="line"></span></div></div>
    </div>

    <div class="header">
        <div class="header-content">
            <nav class="navbar navbar-expand">
                <div class="collapse navbar-collapse justify-content-between">
                    <div class="header-left"></div>
                    @if(request()->user())
                        <div class="header-tools">
                            <a class="header-mail" href="{{ route('notifications.index') }}" aria-label="Notifications"><span class="material-icons">mail_outline</span></a>
                            <div class="dropdown header-profile">
                                <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="avatar-circle">{{ strtoupper(substr(request()->user()->name, 0, 1)) }}</span>
                                    <span class="header-context"><span><strong>{{ request()->user()->name }}</strong><small>{{ request()->user()->actorLabel() }}</small></span></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <div class="profile-summary"><span class="avatar-circle">{{ strtoupper(substr(request()->user()->name, 0, 1)) }}</span><span><strong>{{ request()->user()->name }}</strong><small>{{ request()->user()->actorLabel() }}</small></span></div>
                                    <a class="profile-menu-link" href="{{ route('notifications.index') }}"><span class="material-icons">notifications</span>Notifications</a>
                                    <a class="profile-menu-link" href="{{ route('dashboard') }}"><span class="material-icons">dashboard</span>Dashboard</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </nav>
        </div>
    </div>

    <div class="deznav">
        <div class="deznav-scroll">
            <ul class="metismenu" id="menu">
                <li class="menu-title" style="font-size: 16px">Policy Modules</li>
                <li><a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="menu-icon"><span class="material-icons">home</span></span><span class="nav-text">Dashboard</span></a></li>
                <li><a class="{{ request()->routeIs('policy-documents.*') ? 'active' : '' }}" href="{{ route('policy-documents.index') }}"><span class="menu-icon"><span class="material-icons">description</span></span><span class="nav-text">Documents</span></a></li>
                @if(request()->user()?->canManagePolicies())
                    <li><a class="{{ request()->routeIs('topic-categories.*') ? 'active' : '' }}" href="{{ route('topic-categories.index') }}"><span class="menu-icon"><span class="material-icons">account_tree</span></span><span class="nav-text">Topics</span></a></li>
                    <li><a class="{{ request()->routeIs('lookup-values.*') ? 'active' : '' }}" href="{{ route('lookup-values.index') }}"><span class="menu-icon"><span class="material-icons">tune</span></span><span class="nav-text">Lookup Values</span></a></li>
                    @if(config('features.form_builder'))
                        <li><a class="{{ request()->routeIs('form-templates.*') ? 'active' : '' }}" href="{{ route('form-templates.index') }}"><span class="menu-icon"><span class="material-icons">dashboard_customize</span></span><span class="nav-text">Form Builder</span></a></li>
                    @endif
                    @if(request()->user()?->canAdministerAccess())
                        <li><a class="{{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><span class="menu-icon"><span class="material-icons">admin_panel_settings</span></span><span class="nav-text">User Roles</span></a></li>
                        <li><a class="{{ request()->routeIs('document-activity-logs.*') ? 'active' : '' }}" href="{{ route('document-activity-logs.index') }}"><span class="menu-icon"><span class="material-icons">fact_check</span></span><span class="nav-text">Document Audit Log</span></a></li>
                        <li><a class="{{ request()->routeIs('directory-sync.*') ? 'active' : '' }}" href="{{ route('directory-sync.index') }}"><span class="menu-icon"><span class="material-icons">sync</span></span><span class="nav-text">CAS/HURIS Sync</span></a></li>
                    @endif
                @endif
                <li class="menu-title" style="font-size: 13px">Insights</li>
                @if(request()->user()?->canManagePolicies())
                    <li><a class="{{ request()->routeIs('reports.dashboard') ? 'active' : '' }}" href="{{ route('reports.dashboard') }}"><span class="menu-icon"><span class="material-icons">insights</span></span><span class="nav-text">Reporting Dashboard</span></a></li>
                    @if(request()->user()?->canAdministerAccess())
                        <li><a class="{{ request()->routeIs('reports.user-access*') ? 'active' : '' }}" href="{{ route('reports.user-access') }}"><span class="menu-icon"><span class="material-icons">manage_accounts</span></span><span class="nav-text">User Access Report</span></a></li>
                    @endif
                @endif
                <li><a class="{{ request()->routeIs('reports.circulars') ? 'active' : '' }}" href="{{ route('reports.circulars') }}"><span class="menu-icon"><span class="material-icons">campaign</span></span><span class="nav-text">Circular Report</span></a></li>
                <li><a class="{{ request()->routeIs('reports.versions') ? 'active' : '' }}" href="{{ route('reports.versions') }}"><span class="menu-icon"><span class="material-icons">history</span></span><span class="nav-text">Versioning Report</span></a></li>
                <li><a class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}"><span class="menu-icon"><span class="material-icons">notifications</span></span><span class="nav-text">Notifications</span></a></li>
            </ul>
            <form class="viewer-switch" action="{{ route('viewer-session.store') }}" method="POST">
                @csrf
                <label for="viewer_user_id">Demo viewer</label>
                <div class="viewer-switch-row">
                    <select id="viewer_user_id" name="user_id" class="form-control" aria-label="Demo viewer">
                        @foreach(\App\Models\User::query()->where('is_active', true)->orderBy('name')->get() as $viewerOption)
                            <option value="{{ $viewerOption->id }}" @selected(request()->user()?->id === $viewerOption->id)>
                                {{ $viewerOption->name }}
                            </option>
                        @endforeach
                    </select>
                    <button class="btn btn-primary" type="submit" aria-label="Switch viewer" title="Switch viewer">
                        <span class="material-icons">swap_horiz</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="content-body">
        <div class="page-titles">
            <ol class="breadcrumb">
                <li><h5 class="bc-title">{{ $portalPageTitle }}</h5></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><span class="material-icons" style="font-size:18px">home</span>Home</a>
                </li>
                <li class="breadcrumb-item active"><a href="{{ url()->current() }}">{{ $portalPageTitle }}</a></li>
            </ol>
        </div>
        <div class="container-fluid">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <div class="footer">
        <div class="copyright">
            <p>Copyright &copy; 2026 International Islamic University Malaysia, Realized by Information Technology Division</p>
        </div>
    </div>
    <button class="back-to-top" id="backToTop" type="button" aria-label="Back to top" title="Back to top"><span class="material-icons">arrow_upward</span></button>
</div>

<script src="https://style.iium.edu.my/vendor/global/global.min.js"></script>
<script src="https://style.iium.edu.my/js/custom.js"></script>
<script src="https://style.iium.edu.my/js/deznav-init.js"></script>
<script src="https://style.iium.edu.my/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script>
    (() => {
        const backToTop = document.getElementById('backToTop');
        window.addEventListener('scroll', () => backToTop?.classList.toggle('is-visible', window.scrollY > 420), { passive: true });
        backToTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        document.querySelectorAll('.alert').forEach((alert) => alert.setAttribute('role', 'status'));
    })();
</script>
</body>
</html>
