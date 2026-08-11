<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IIUM Template</title>

    <link rel="shortcut icon" type="image/png" href="https://style.iium.edu.my/images/iium/iium-logo.png">
    <link href="https://style.iium.edu.my/css/style.css" rel="stylesheet">
    <link href="https://style.iium.edu.my/css/iium.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://style.iium.edu.my/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
</head>
<body>
    @php($viewer = auth()->user())
    <div class="background-image"></div>

    <div id="preloader">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
    </div>

    <div id="main-wrapper">
        <div class="nav-header">
            <a href="{{ url('/') }}" class="brand-logo">
                <img src="https://style.iium.edu.my/images/iium/iium-logo-v2.png" class="user_img" style="max-width: 75%" alt="IIUM Logo">
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span>
                    <span class="line"></span>
                    <span class="line"></span>
                </div>
            </div>
        </div>

        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left"></div>
                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown notification_dropdown">
                                <a class="nav-link bell-link" href="javascript:void(0);">
                                    <svg width="20" height="22" viewBox="0 0 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M16.9026 6.85114L12.4593 10.4642C11.6198 11.1302 10.4387 11.1302 9.59922 10.4642L5.11844 6.85114" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9089 19C18.9502 19.0084 21 16.5095 21 13.4384V6.57001C21 3.49883 18.9502 1 15.9089 1H6.09114C3.04979 1 1 3.49883 1 6.57001V13.4384C1 16.5095 3.04979 19.0084 6.09114 19H15.9089Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </li>
                            <li class="nav-item ps-3">
                                <div class="dropdown header-profile2">
                                    <a class="nav-link" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="header-info2 d-flex align-items-center">
                                            <div class="header-media">
                                                <img src="https://style.iium.edu.my/images/iium/profile.png" alt="Profile">
                                            </div>
                                            <div class="header-info">
                                                <h6>Ali Bin Rauf</h6>
                                                <p>alirauf@live.iium.edu.my</p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <div class="deznav">
            <div class="deznav-scroll">
                <ul class="metismenu" id="menu">
                    <li class="menu-title" style="font-size: 16px">IIUM Template</li>
                    <li>
                        <a href="{{ url('/') }}" class="mm-active" aria-expanded="false">
                            <div class="menu-icon">
                                <img src="https://style.iium.edu.my/images/iconly/light/Home.svg" alt="Home Icon">
                            </div>
                            <span class="nav-text">Home Page</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="content-body">
            <div class="page-titles">
                <ol class="breadcrumb">
                    <li>
                        <h5 class="bc-title">Dashboard</h5>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)">
                            <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.125 6.375L8.5 1.41667L14.875 6.375V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5833 13.4583 15.5833H3.54167C3.16594 15.5833 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.375Z" stroke="#2C2C2C" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="#2C2C2C" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Home
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
                </ol>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <h1>Policy & Circular Management</h1>
                                <p>This workspace now supports viewer switching for browser-based testing of FRS access rules.</p>
                                <p>
                                    Current viewer: {{ $viewer?->name ?? 'Guest User' }}
                                    | Actor: {{ $viewer ? $viewer->actorLabel() : 'Staff/Public' }}
                                    | Unit: {{ $viewer ? strtoupper($viewer->unit) : 'ALL' }}
                                </p>

                                <div class="mb-3 d-flex flex-wrap gap-2">
                                    <a href="{{ route('policy-documents.index') }}" class="btn btn-primary">Open Policy Module</a>
                                    <a href="{{ route('reports.circulars') }}" class="btn btn-outline-primary">Open Circular Report</a>
                                </div>

                                <form action="{{ route('viewer-session.store') }}" method="POST" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-8">
                                        <label class="form-label">Switch viewer</label>
                                        <select name="user_id" class="form-control">
                                            @foreach($viewerOptions as $option)
                                                <option value="{{ $option->id }}" @selected($viewer?->id === $option->id)>
                                                    {{ $option->name }} | {{ $option->actorLabel() }} | {{ strtoupper($option->unit) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex gap-2">
                                        <button class="btn btn-primary w-100" @disabled($viewerOptions->isEmpty())>Apply</button>
                                    </div>
                                </form>

                                @if($viewer)
                                    <form action="{{ route('viewer-session.destroy') }}" method="POST" class="mt-2">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-secondary">Use Guest View</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="row mt-2 mb-2">
                <div class="col-md-6 iium-footer">
                    <h4>
                        <a href="https://www.iium.edu.my" target="_blank" class="iium-href">IIUM Website</a> ||
                        <a href="https://www.iium.edu.my/v2/disclaimer" target="_blank" class="iium-href">Disclaimers</a>
                    </h4>
                </div>
            </div>
            <div class="copyright">
                <p>Copyright &copy; 2026 International Islamic University Malaysia, Realized by Information Technology Division</p>
            </div>
        </div>
    </div>

    <script src="https://style.iium.edu.my/vendor/global/global.min.js"></script>
    <script src="https://style.iium.edu.my/js/custom.js"></script>
    <script src="https://style.iium.edu.my/js/deznav-init.js"></script>
    <script src="https://style.iium.edu.my/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
</body>
</html>
