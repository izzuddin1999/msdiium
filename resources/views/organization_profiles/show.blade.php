@extends('layouts.app')

@section('content')
<style>
    .org-profile-hero{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:24px;padding:26px 28px;border-radius:16px;background:linear-gradient(125deg,#075f56,#00988d 58%,#31b8a9);color:#fff;box-shadow:0 14px 34px rgba(17,84,73,.18)}.org-profile-hero:after{content:'';position:absolute;right:-70px;top:-115px;width:260px;height:260px;border:46px solid rgba(255,255,255,.08);border-radius:50%}.org-profile-identity{position:relative;z-index:1;display:flex;align-items:center;gap:16px}.org-profile-mark{display:grid;place-items:center;flex:0 0 72px;height:72px;border:1px solid rgba(255,255,255,.3);border-radius:18px;background:rgba(255,255,255,.16);font-size:21px;font-weight:900}.org-profile-identity h2{margin:2px 0 4px;color:#fff}.org-profile-identity p{max-width:720px;margin:0;color:#d8f4ef}.org-profile-state{position:relative;z-index:1;padding:7px 11px;border-radius:20px;background:rgba(255,255,255,.17);font-size:11px;font-weight:750}
    .org-metrics{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin:18px 0}.org-metric{display:flex;align-items:center;gap:10px;padding:15px;border:1px solid #dce9e6;border-radius:11px;background:#fff}.org-metric .material-icons{display:grid;place-items:center;width:38px;height:38px;border-radius:9px;background:#e7f7f3;color:#008f85}.org-metric strong,.org-metric small{display:block}.org-metric strong{color:#143e37;font-size:20px}.org-metric small{color:#748680;font-size:10px}
    .org-profile-grid{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(300px,.75fr);gap:18px;align-items:start}.org-card{overflow:hidden;border:1px solid #dce8e5;border-radius:13px;background:#fff;box-shadow:0 7px 22px rgba(20,65,57,.06)}.org-card-head{padding:15px 18px;border-bottom:1px solid #e2ece9;background:linear-gradient(90deg,#eef8f6,#fff)}.org-card-head h5,.org-card-head p{margin:0}.org-card-head p{margin-top:3px;color:#71847f;font-size:11px}.org-form{padding:18px}.org-form .form-label{font-size:11px;font-weight:700}.org-form .form-control{min-height:42px}.org-contact-list{display:grid;gap:10px;padding:17px}.org-contact-item{display:flex;align-items:flex-start;gap:10px;padding:11px;border:1px solid #e2ece9;border-radius:9px;background:#f8fbfa}.org-contact-item>.material-icons{color:#008f85}.org-contact-item strong,.org-contact-item small{display:block}.org-contact-item small{margin-top:2px;color:#758782}.org-manager-list{display:grid;gap:8px;padding:0 17px 17px}.org-manager{display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #e3ecea;border-radius:9px}.org-manager .avatar-circle{width:35px;height:35px}.org-manager strong,.org-manager small{display:block}.org-manager small{color:#768782;font-size:10px}
    @media(max-width:1350px){.org-profile-grid{grid-template-columns:1fr}.org-metrics{grid-template-columns:repeat(3,1fr)}}@media(max-width:767px){.org-profile-hero{align-items:flex-start;flex-direction:column;padding:21px}.org-profile-identity{align-items:flex-start}.org-profile-mark{flex-basis:54px;height:54px;border-radius:13px}.org-metrics{grid-template-columns:1fr}}
</style>

<div class="breadcrumb-flow"><a href="{{ route('dashboard') }}">Dashboard</a><span class="material-icons">chevron_right</span><span>Organization profile</span></div>
<section class="org-profile-hero">
    <div class="org-profile-identity">
        <span class="org-profile-mark">{{ $profile->short_name }}</span>
        <div><span class="eyebrow">Organization profile</span><h2>{{ $profile->name }}</h2><p>{{ $profile->description ?: 'No organization description has been provided.' }}</p></div>
    </div>
    <span class="org-profile-state">{{ $profile->is_active ? 'Active organization' : 'Inactive organization' }}</span>
</section>

<div class="org-metrics">
    @foreach([
        ['Documents', $metrics['documents'], 'description'],
        ['Active', $metrics['active'], 'verified'],
        ['Topic categories', $metrics['topics'], 'account_tree'],
        ['Form templates', $metrics['templates'], 'dashboard_customize'],
        ['Active users', $metrics['users'], 'groups'],
    ] as [$label, $value, $icon])
        <div class="org-metric"><span class="material-icons">{{ $icon }}</span><div><strong>{{ $value }}</strong><small>{{ $label }}</small></div></div>
    @endforeach
</div>

<div class="org-profile-grid">
    <section class="org-card">
        <div class="org-card-head"><h5>Profile details</h5><p>{{ $canEdit ? 'Maintain the official identity and contact information for your organization.' : 'Official organization information.' }}</p></div>
        <form action="{{ route('organization-profile.update') }}" method="POST" class="org-form">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-8"><label class="form-label">Official name</label><input name="name" class="form-control" value="{{ old('name', $profile->name) }}" required @disabled(!$canEdit)></div>
                <div class="col-md-4"><label class="form-label">Short name</label><input name="short_name" class="form-control" value="{{ old('short_name', $profile->short_name) }}" required @disabled(!$canEdit)></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" maxlength="2000" @disabled(!$canEdit)>{{ old('description', $profile->description) }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Contact email</label><input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $profile->contact_email) }}" @disabled(!$canEdit)></div>
                <div class="col-md-6"><label class="form-label">Contact phone</label><input name="contact_phone" class="form-control" value="{{ old('contact_phone', $profile->contact_phone) }}" @disabled(!$canEdit)></div>
                <div class="col-md-6"><label class="form-label">Office location</label><input name="office_location" class="form-control" value="{{ old('office_location', $profile->office_location) }}" @disabled(!$canEdit)></div>
                <div class="col-md-6"><label class="form-label">Website</label><input type="url" name="website" class="form-control" value="{{ old('website', $profile->website) }}" placeholder="https://" @disabled(!$canEdit)></div>
            </div>
            @if($canEdit)<div class="mt-3"><button class="btn btn-primary"><span class="material-icons align-middle me-1" style="font-size:18px">save</span>Save organization profile</button></div>@endif
        </form>
    </section>

    <aside class="org-card">
        <div class="org-card-head"><h5>Organization contacts</h5><p>Profile and assigned management contacts</p></div>
        <div class="org-contact-list">
            <div class="org-contact-item"><span class="material-icons">mail</span><span><strong>Email</strong><small>{{ $profile->contact_email ?: 'Not provided' }}</small></span></div>
            <div class="org-contact-item"><span class="material-icons">call</span><span><strong>Phone</strong><small>{{ $profile->contact_phone ?: 'Not provided' }}</small></span></div>
            <div class="org-contact-item"><span class="material-icons">location_on</span><span><strong>Office</strong><small>{{ $profile->office_location ?: 'Not provided' }}</small></span></div>
        </div>
        <div class="org-card-head"><h5>Assigned managers</h5><p>Active management accounts for {{ $profile->short_name }}</p></div>
        <div class="org-manager-list">
            @forelse($managers as $manager)
                <div class="org-manager"><span class="avatar-circle">{{ strtoupper(substr($manager->name, 0, 1)) }}</span><span><strong>{{ $manager->name }}</strong><small>{{ $manager->email }} · {{ $manager->actorLabel() }}</small></span></div>
            @empty
                <div class="text-muted small">No active manager is assigned.</div>
            @endforelse
        </div>
    </aside>
</div>
@endsection
