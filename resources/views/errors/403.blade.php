@php($errorLayout = request()->user() ? 'layouts.app' : 'layouts.public')
@extends($errorLayout)

@section('content')
<style>
    .access-denied-wrap{min-height:calc(100vh - 190px);display:grid;place-items:center;padding:42px 20px}
    .access-denied-card{width:min(620px,100%);padding:42px;border:1px solid #dbe7e5;border-radius:16px;background:rgba(255,255,255,.97);box-shadow:0 14px 38px rgba(25,65,61,.1);text-align:center}
    .access-denied-icon{display:grid;place-items:center;width:68px;height:68px;margin:0 auto 20px;border-radius:18px;background:#e8f6f3;color:#008f86}
    .access-denied-icon .material-icons{font-size:34px}
    .access-denied-code{display:inline-block;margin-bottom:8px;color:#008f86;font-size:12px;font-weight:600;letter-spacing:.08em;text-transform:uppercase}
    .access-denied-card h1{margin:0;color:#173b3a;font-size:28px;font-weight:600}
    .access-denied-card p{max-width:470px;margin:12px auto 26px;color:#667085;font-size:14px;line-height:1.65}
    .access-denied-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap}
    .access-denied-actions a,.access-denied-actions button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:0 16px;border-radius:8px;font:600 13px/1 Poppins,Arial,sans-serif;cursor:pointer;text-decoration:none}
    .access-denied-primary{border:1px solid #008f86;background:#008f86;color:#fff}
    .access-denied-secondary{border:1px solid #cfdcda;background:#fff;color:#31524f}
    .access-denied-actions .material-icons{font-size:18px}
    @media(max-width:575px){.access-denied-card{padding:32px 20px}.access-denied-actions>*{width:100%}}
</style>

<div class="access-denied-wrap">
    <section class="access-denied-card" role="alert" aria-labelledby="accessDeniedTitle">
        <span class="access-denied-icon" aria-hidden="true"><span class="material-icons">lock</span></span>
        <span class="access-denied-code">Access restricted</span>
        <h1 id="accessDeniedTitle">You don’t have permission to view this page</h1>
        <p>This area is limited to authorized roles. You can return to the dashboard or go back to your previous page.</p>
        <div class="access-denied-actions">
            <a class="access-denied-primary" href="{{ request()->user() ? route('dashboard') : route('public-portal') }}"><span class="material-icons">dashboard</span>Go to dashboard</a>
            <button class="access-denied-secondary" type="button" onclick="history.back()"><span class="material-icons">arrow_back</span>Go back</button>
        </div>
    </section>
</div>
@endsection
