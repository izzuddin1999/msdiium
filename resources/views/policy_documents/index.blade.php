@extends('layouts.app')

@section('content')
<style>
    .policy-index{--pi-teal:#006d70;--pi-border:#dfe7ea;--pi-text:#172b4d;--pi-muted:#667085;padding-top:2px}
    .policy-unit-view{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:16px;padding:13px 16px;border:1px solid #dce7e8;border-radius:11px;background:linear-gradient(90deg,#f4fbfa,#fff);box-shadow:0 4px 14px rgba(22,50,70,.05)}
    .policy-unit-heading{display:flex;align-items:center;gap:10px;color:#075f64;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.policy-unit-heading .material-icons{font-size:22px}
    .policy-unit-options{display:flex;align-items:center;gap:8px}.policy-unit-option{display:flex;align-items:center;gap:9px;min-height:40px;padding:8px 14px;border:1px solid #cadcde;border-radius:8px;background:#fff;color:#344054;font-size:11px;font-weight:750}.policy-unit-option:hover{color:#006d70;border-color:#7dbfc0;text-decoration:none}.policy-unit-option.active{border-color:#007b7d;background:#007b7d;color:#fff;box-shadow:0 4px 10px rgba(0,109,112,.18)}.policy-unit-option b{display:grid;place-items:center;min-width:23px;height:23px;padding:0 6px;border-radius:12px;background:#e8f5f4;color:#087179;font-size:10px}.policy-unit-option.active b{background:rgba(255,255,255,.2);color:#fff}
    .policy-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:16px;margin-bottom:16px}
    .policy-kpi{display:flex;align-items:center;gap:16px;min-height:92px;padding:16px 18px;border:1px solid #e3e8ec;border-radius:11px;background:#fff;box-shadow:0 4px 14px rgba(22,50,70,.06)}
    .policy-kpi-icon{display:grid;place-items:center;flex:0 0 56px;height:56px;border-radius:12px;background:#e8f5f4;color:#007477}.policy-kpi-icon .material-icons{font-size:31px}
    .policy-kpi:nth-child(2) .policy-kpi-icon{background:#e9f7ef;color:#008f48}.policy-kpi:nth-child(3) .policy-kpi-icon{background:#fff3df;color:#f2a000}.policy-kpi:nth-child(4) .policy-kpi-icon{background:#f2eaff;color:#7b35e4}.policy-kpi:nth-child(5) .policy-kpi-icon{background:#ffe9eb;color:#f13345}
    .policy-kpi strong,.policy-kpi small{display:block}.policy-kpi strong{font-size:28px;line-height:1;color:var(--pi-text)}.policy-kpi small{margin-top:9px;color:#344054;font-size:13px}
    .policy-filter{display:grid;grid-template-columns:minmax(270px,1.8fr) repeat(4,minmax(145px,1fr)) auto auto;gap:16px;align-items:end;margin-bottom:14px;padding:16px 18px 18px;border:1px solid #e0e7eb;border-radius:11px;background:#fff;box-shadow:0 4px 14px rgba(22,50,70,.05)}
    .policy-field label{display:block;margin:0 0 6px;color:#152746;font-size:11px;font-weight:750}.policy-field .form-control{height:44px;border:1px solid #d6e0e5;border-radius:7px;background-color:#fff;color:#344054;font-size:12px}
    .policy-search{position:relative}.policy-search .material-icons{position:absolute;left:13px;top:12px;color:#344767;font-size:21px}.policy-search .form-control{padding-left:43px}
    .policy-filter .btn{height:44px;min-width:76px;border-radius:7px;font-size:12px;font-weight:750}.policy-filter .btn-outline-primary{border-color:#00828a;color:#006b73;background:#fff}
    .policy-repository{overflow:hidden;border:1px solid #dce5e8;border-radius:11px;background:#fff;box-shadow:0 4px 16px rgba(22,50,70,.06)}
    .policy-repository-head{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:10px 18px;border-bottom:1px solid var(--pi-border)}
    .policy-repository-title{display:flex;align-items:center;gap:13px;color:#006b73;font-size:17px;font-weight:800}.policy-count{padding:5px 12px;border-radius:18px;background:#e8f5f4;color:#086b70;font-size:10px}
    .policy-view-tools{display:flex;align-items:center;gap:9px;color:#667085;font-size:11px}.policy-view-tools select{width:168px;height:38px;border:1px solid #d7e0e5;border-radius:7px;padding:0 12px;color:#344054;background:#fff;font-size:11px}.repository-toggle{display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 11px;border:1px solid #b9d4d5;border-radius:7px;background:#fff;color:#006b73;font:700 10px/1 Poppins,Arial,sans-serif;white-space:nowrap}.repository-toggle:hover{border-color:#007477;background:#edf8f7}.repository-toggle .material-icons{font-size:17px}.view-toggle{display:grid;place-items:center;width:38px;height:38px;border:1px solid #dce4e8;border-radius:7px;background:#fff;color:#344054}.view-toggle.active{background:#007477;color:#fff;border-color:#007477}.view-toggle .material-icons{font-size:19px}
    .policy-groups{background:#fbfcfd}.policy-category{border-bottom:1px solid #dfe7ea}.policy-category:last-child{border-bottom:0}.policy-category>summary,.policy-topic>summary{list-style:none;cursor:pointer}.policy-category>summary::-webkit-details-marker,.policy-topic>summary::-webkit-details-marker{display:none}
    .policy-category-head{display:flex;align-items:center;justify-content:space-between;min-height:58px;padding:11px 20px;background:linear-gradient(90deg,#edf7f7,#fbfdfd);color:#00676c;font-size:16px;font-weight:800;line-height:1.45}.policy-category-name,.policy-topic-name{display:flex;align-items:center;gap:14px}.policy-category-name .material-icons{font-size:26px}.group-actions{display:flex;align-items:center;gap:20px}.group-count{padding:6px 13px;border-radius:15px;background:#e5f3f3;color:#086b70;font-size:13px}.group-actions>.material-icons{font-size:21px;transition:.2s}.policy-category[open]>.policy-category-head .group-actions>.material-icons,.policy-topic[open]>.policy-topic-head .group-actions>.material-icons{transform:rotate(180deg)}
    .policy-unit-badge{padding:5px 10px;border-radius:6px;background:#007b7d;color:#fff;font-size:13px;letter-spacing:.04em}
    .policy-topic{margin:0 12px 10px;border:1px solid #e1e8eb;border-radius:8px;background:#fff;overflow:hidden}.policy-topic-head{display:flex;align-items:center;justify-content:space-between;min-height:38px;padding:7px 14px;background:#f7fafb;color:#08636a;font-size:11px;font-weight:800}
    .policy-subtopic-head{display:flex;align-items:center;justify-content:space-between;padding:7px 14px;border-top:1px solid #e3eaed;border-bottom:1px solid #e3eaed;background:#f8fafc;color:#344054;font-size:10px;font-weight:750}.policy-subtopic-head span:first-child:before{content:'';display:inline-block;width:6px;height:6px;margin-right:8px;border-radius:50%;background:#159cf5}
    .policy-table-head,.policy-document-row{display:grid;grid-template-columns:110px 155px minmax(250px,1.7fr) 110px 155px 135px 130px 90px;align-items:center}.policy-table-head{min-height:31px;padding:0 14px;border-bottom:1px solid #e4eaed;color:#475467;font-size:9px;font-weight:800;text-transform:uppercase}.policy-document-row{min-height:63px;padding:8px 14px;border-bottom:1px solid #edf1f3;color:#344054;font-size:11px}.policy-document-row:last-child{border-bottom:0}.policy-document-row:hover{background:#f8fcfc}
    .type-pill,.status-pill{justify-self:start;padding:5px 10px;border-radius:6px;background:#e7f3f2;color:#087179;font-size:9px;font-weight:800;text-transform:uppercase}.type-circular{background:#eaf2ff;color:#175fc4}.type-guideline{background:#fff4e4;color:#dc6803}.status-pill{border-radius:7px;background:#e7f6ec;color:#067435}.status-superseded{background:#f0ebff;color:#6938ca}
    .doc-title{min-width:0;color:#172b4d;font-weight:700;line-height:1.35}.doc-title a{color:inherit}.doc-title small{display:block;margin-top:2px;color:#007477;font-size:9px;font-weight:750}.doc-date{display:flex;align-items:center;gap:7px;white-space:nowrap}.doc-date .material-icons{font-size:15px;color:#344767}.doc-actions{display:flex;justify-content:flex-end;gap:8px}.doc-action{display:grid;place-items:center;width:36px;height:36px;border:1px solid #dce5e9;border-radius:7px;background:#fff;color:#233b61}.doc-action .material-icons{font-size:18px}.doc-action:hover{background:#eef8f8;color:#007477}
    .policy-footer{display:flex;justify-content:space-between;align-items:center;padding:10px 18px;color:#475467;font-size:10px}.policy-footer .pagination{margin:0}
    .empty-policy{padding:50px 20px;text-align:center;color:#667085}.empty-policy .material-icons{font-size:44px;color:#98a2b3}
    @media(max-width:1300px){.policy-kpis{grid-template-columns:repeat(3,1fr)}.policy-filter{grid-template-columns:repeat(3,1fr)}.policy-table-scroll{overflow-x:auto}.policy-table-head,.policy-document-row{min-width:1100px}}
    @media(max-width:767px){.policy-unit-view{align-items:flex-start;flex-direction:column}.policy-unit-options{width:100%;overflow-x:auto}.policy-unit-option{white-space:nowrap}.policy-kpis,.policy-filter{grid-template-columns:1fr}.policy-repository-head{align-items:flex-start;flex-direction:column}.policy-view-tools{width:100%;flex-wrap:wrap}.policy-view-tools select{flex:1}}

    /* Streamlined repository workspace: find, add, then browse. */
    .repository-intro{display:flex;align-items:center;justify-content:space-between;gap:24px;margin-bottom:14px;padding:20px 22px;border-radius:12px;background:linear-gradient(115deg,#073b45,#007a78);color:#fff;box-shadow:0 8px 22px rgba(7,59,69,.16)}
    .repository-intro h2{margin:0;color:#fff;font-size:22px}.repository-intro p{margin:4px 0 0;color:#d8f3f1;font-size:12px}.repository-add{display:inline-flex;align-items:center;gap:7px;min-height:42px;padding:0 16px;border-radius:8px;background:#fff;color:#006d70;font-size:11px;font-weight:800;white-space:nowrap}.repository-add:hover{color:#004f52;text-decoration:none}.repository-add .material-icons{font-size:18px}
    .policy-kpis{gap:10px}.policy-kpi{min-height:72px;padding:11px 14px}.policy-kpi-icon{flex-basis:40px;height:40px}.policy-kpi-icon .material-icons{font-size:22px}.policy-kpi strong{font-size:21px}.policy-kpi small{margin-top:4px;font-size:10px}
    .policy-filter{grid-template-columns:minmax(300px,2fr) repeat(4,minmax(125px,1fr)) auto auto;gap:10px;padding:13px 15px}.policy-field label{font-size:9px}.policy-filter .policy-search label{height:14px;color:#006d70}.policy-search .form-control{font-size:12px}.policy-search-hint{float:right;color:#98a2b3;font-weight:500}.policy-repository-head{padding:13px 16px;background:#fff}.policy-repository-title{display:block}.policy-repository-title small{display:block;margin-top:2px;color:#667085;font-size:9px;font-weight:500;text-transform:none}.policy-view-tools{margin-left:auto}.policy-view-tools>span{white-space:nowrap}
    .policy-category{background:#fff}.policy-category-head{min-height:52px;padding:9px 16px}.policy-category-name{gap:10px}.policy-category-name .material-icons{font-size:21px}.policy-unit-badge{font-size:10px}.group-count{font-size:10px}.policy-topic{margin:0 10px 9px}.policy-subtopic-head{background:#fff}.doc-title small{display:none}.policy-document-row{min-height:58px}
    .staff-style-table-wrap{overflow-x:auto;background:#fff}.staff-style-table{width:100%;min-width:1080px;border-collapse:collapse}.staff-style-table th{padding:11px 14px;border-bottom:1px solid #dfe7ea;background:#f7fafb;color:#475467;font-size:9px;font-weight:800;text-align:left;text-transform:uppercase;letter-spacing:.035em;white-space:nowrap}.staff-style-table td{padding:12px 14px;border-bottom:1px solid #e8edf0;color:#344054;font-size:10px;vertical-align:middle}.staff-style-table tbody tr:last-child td{border-bottom:0}.staff-style-table tbody tr:hover{background:#f8fcfc}.staff-doc-title{min-width:260px}.staff-doc-title a{display:block;color:#172b4d;font-size:11px;font-weight:750;line-height:1.45}.staff-doc-title small{display:block;margin-top:3px;color:#667085;font-size:9px}.staff-topic-cell{min-width:175px}.staff-topic-cell strong,.staff-topic-cell small{display:block}.staff-topic-cell strong{color:#075f64;font-size:9px}.staff-topic-cell small{margin-top:3px;color:#667085;font-size:8px}.staff-table-empty{padding:52px 20px!important;text-align:center}.staff-table-empty .material-icons{display:block;margin-bottom:7px;color:#98a2b3;font-size:38px}.staff-style-table .doc-actions{justify-content:flex-start}
    .staff-style-table-wrap{display:none}.topic-document-list{background:#fff}.topic-document-row{display:grid;grid-template-columns:40px minmax(0,1fr) 105px auto;gap:12px;align-items:center;min-height:84px;padding:13px 18px;border-bottom:1px solid #e5eaed}.topic-document-row:last-child{border-bottom:0}.topic-document-row:hover{background:#fbfdfd}.topic-pdf-icon{display:grid;place-items:center;width:36px;height:42px;border-radius:7px;background:#ffe7e9;color:#dc3545;font-size:8px;font-weight:800}.topic-document-copy{min-width:0}.topic-document-copy a{display:block;color:#172b4d;font-size:11px;font-weight:800;line-height:1.45}.topic-document-copy a:hover{color:#007477}.topic-document-meta{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:5px;color:#667085;font-size:10px}.topic-document-meta span+span:before{content:'·';margin-right:6px}.topic-document-date{color:#667085;font-size:9px;white-space:nowrap}.topic-document-actions{display:flex;gap:7px}.topic-view-button,.topic-edit-button{display:inline-flex;align-items:center;justify-content:center;gap:5px;min-width:64px;height:42px;padding:0 12px;border:1px solid #c9dce0;border-radius:7px;background:#fff;color:#007477;font-size:10px;font-weight:800}.topic-view-button:hover,.topic-edit-button:hover{border-color:#008f86;background:#edf8f6;color:#006b70;text-decoration:none}.topic-view-button .material-icons,.topic-edit-button .material-icons{font-size:15px}.topic-edit-button{min-width:42px;padding:0;color:#475467}.topic-list-empty{padding:60px 20px;text-align:center;color:#667085}.topic-list-empty .material-icons{display:block;margin-bottom:8px;color:#91aaa7;font-size:38px}@media(max-width:767px){.topic-document-row{grid-template-columns:36px minmax(0,1fr);padding:13px}.topic-document-date{grid-column:2}.topic-document-actions{grid-column:2}.topic-view-button{flex:1}}
    .document-category-group{border-bottom:1px solid #d7e4e6}.document-category-group:last-child{border-bottom:0}.document-category-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:52px;padding:10px 18px;background:linear-gradient(90deg,#eaf6f5,#f8fcfc);color:#006b70}.document-category-heading>span:first-child{display:flex;align-items:center;gap:9px;font-size:12px;font-weight:800}.document-category-heading .material-icons{font-size:20px}.document-category-heading small,.document-main-heading small,.document-subtopic-heading small{padding:4px 9px;border-radius:999px;background:#fff;color:#087179;font-size:8px;font-weight:800}.document-main-group{margin:9px 12px 12px;overflow:hidden;border:1px solid #dfe7ea;border-radius:8px}.document-main-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 13px;background:#f6f9fa;color:#164e57;font-size:10px;font-weight:800}.document-subtopic-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:7px 14px;border-top:1px solid #e3e9ec;border-bottom:1px solid #e3e9ec;background:#fff;color:#475467;font-size:9px;font-weight:750}.document-subtopic-heading>span:first-child{display:flex;align-items:center;gap:7px}.document-subtopic-heading>span:first-child:before{content:'';width:6px;height:6px;border-radius:50%;background:#159cf5}.document-main-group .topic-document-row{padding-right:13px;padding-left:13px}
    .topic-browser-layout{display:grid;grid-template-columns:300px minmax(0,1fr);gap:14px;padding:14px;background:#f7fafb}.topic-browser-nav,.topic-browser-documents{overflow:hidden;border:1px solid #dfe7ea;border-radius:10px;background:#fff}.topic-browser-head{padding:15px 17px;border-bottom:1px solid #e3e9ec}.topic-browser-head strong{display:block;color:#006d70;font-size:10px;text-transform:uppercase}.topic-browser-head span{display:block;margin-top:4px;color:#667085;font-size:12px}.topic-nav-list{max-height:650px;padding:7px 16px;overflow:auto}.topic-nav-category{padding:12px 0;border-bottom:1px solid #e7ecef}.topic-nav-category:last-child{border-bottom:0}.topic-nav-category>strong{display:flex;align-items:center;gap:7px;color:#075f64;font-size:9px;text-transform:uppercase}.topic-nav-category>strong .material-icons{font-size:15px}.topic-nav-main{margin:8px 0 0 4px;color:#172b4d;font-size:9px;font-weight:800}.topic-nav-subtopics{display:flex;flex-direction:column;margin-top:5px}.topic-nav-link{display:flex;align-items:center;gap:7px;padding:7px 5px;color:#475467;font-size:8px;text-decoration:none}.topic-nav-link:hover{color:#008f86;text-decoration:none}.topic-nav-link .material-icons{color:#00928f;font-size:15px}.topic-browser-documents .topic-document-list{min-height:420px}.topic-browser-documents .topic-document-row{padding-right:16px;padding-left:16px}@media(max-width:900px){.topic-browser-layout{grid-template-columns:1fr}.topic-nav-list{max-height:280px}}
    .topic-nav-category-link{display:flex;align-items:center;gap:7px;color:#075f64;font-size:9px;font-weight:800;text-decoration:none;text-transform:uppercase}.topic-nav-category-link .material-icons{font-size:15px}.topic-nav-main{display:block;text-decoration:none}.topic-nav-category-link:hover,.topic-nav-main:hover{color:#008f86;text-decoration:none}.topic-nav-category-link.active,.topic-nav-main.active,.topic-nav-link.active{color:#006d70;background:#eaf7f5;border-radius:6px}.topic-nav-category-link.active{padding:6px}.topic-nav-main.active{padding:6px;margin-left:0}.topic-nav-empty{padding:6px 5px;color:#98a2b3;font-size:8px;font-style:italic}
    /* Refined managed-topic browser. */
    .topic-browser-layout{gap:18px;padding:18px;background:linear-gradient(145deg,#f4f8f9,#edf5f4)}.topic-browser-nav,.topic-browser-documents{border-color:#d7e4e5;border-radius:14px;box-shadow:0 10px 28px rgba(25,58,66,.08)}
    .topic-browser-nav{position:relative}.topic-browser-nav:before{content:'';position:absolute;z-index:2;top:0;right:0;left:0;height:4px;background:linear-gradient(90deg,#006d70,#19b6a8)}.topic-browser-head{padding:18px 20px;background:linear-gradient(115deg,#f7fcfb,#fff)}.topic-browser-head strong{font-size:11px;letter-spacing:.055em}.topic-browser-head span{margin-top:6px;font-size:11px;line-height:1.55}.topic-browser-documents>.topic-browser-head{display:flex;align-items:center;justify-content:space-between;gap:16px}.topic-browser-documents>.topic-browser-head:after{content:'{{ $documents->total() }} DOCUMENTS';flex:0 0 auto;padding:6px 11px;border-radius:999px;background:#e6f5f2;color:#006d70;font-size:8px;font-weight:850;letter-spacing:.04em}
    .topic-nav-list{padding:12px;scrollbar-color:#8dbbb5 transparent;scrollbar-width:thin}.topic-nav-list>.topic-nav-link{margin-bottom:7px;padding:10px 11px;border:1px solid #d9ebe7;background:linear-gradient(90deg,#e6f6f3,#f2fbf9);color:#006d70;font-weight:800}.topic-nav-category{margin-bottom:8px;padding:10px;border:1px solid transparent;border-radius:9px;transition:.18s}.topic-nav-category:hover{border-color:#d8e8e5;background:#fbfdfd}.topic-nav-category-link{padding:4px;color:#006269;font-size:9px;line-height:1.4}.topic-nav-category-link .material-icons{display:grid;place-items:center;flex:0 0 24px;height:24px;border-radius:6px;background:#e3f3f1;color:#007d77}.topic-nav-main{position:relative;margin:8px 0 0 31px;padding:5px 7px;border-left:2px solid #cfe3df;color:#213d52;font-size:9px;line-height:1.35}.topic-nav-main:before{content:'';position:absolute;top:50%;left:-2px;width:2px;height:14px;background:#00958b;transform:translateY(-50%)}.topic-nav-subtopics{margin:3px 0 0 28px}.topic-nav-link{padding:6px 8px;border-radius:6px;font-size:8px}.topic-nav-link:hover{background:#eef8f6}.topic-nav-category-link.active,.topic-nav-main.active,.topic-nav-link.active{box-shadow:inset 3px 0 0 #00958b;background:#e4f5f2}.topic-nav-empty{margin-left:7px}
    .topic-browser-documents{background:#fff}.topic-document-row{position:relative;grid-template-columns:44px minmax(0,1fr) 100px auto;min-height:92px;padding:16px 18px;transition:background .18s,transform .18s}.topic-document-row:before{content:'';position:absolute;top:15px;bottom:15px;left:0;width:3px;border-radius:0 3px 3px 0;background:#00958b;opacity:0;transition:.18s}.topic-document-row:hover{z-index:1;background:linear-gradient(90deg,#f4fbfa,#fff);box-shadow:0 7px 20px rgba(24,72,68,.07)}.topic-document-row:hover:before{opacity:1}.topic-pdf-icon{width:40px;height:46px;border:1px solid #ffd9dc;border-radius:10px;background:linear-gradient(145deg,#fff0f1,#ffdfe2);box-shadow:0 4px 10px rgba(220,53,69,.08);font-size:8px}.topic-document-copy a{font-size:11px;line-height:1.5}.topic-document-meta{margin-top:7px;font-size:9px}.topic-document-meta .status-pill{padding:4px 9px}.topic-document-date{padding:5px 8px;border-radius:6px;background:#f6f8fa;text-align:center;font-weight:650}.topic-view-button{min-width:72px;border-color:#96cbc6;background:#f7fcfb;color:#006d70;box-shadow:0 3px 9px rgba(0,109,112,.08)}.topic-view-button:hover{background:#007b79;color:#fff}.topic-edit-button:hover{background:#edf3f8;color:#213d52}.topic-list-empty{min-height:380px;display:grid;place-content:center}.policy-footer{background:#fafcfc;border-top:1px solid #e3e9ec}
    @media(max-width:767px){.topic-browser-layout{padding:10px}.topic-browser-documents>.topic-browser-head{align-items:flex-start;flex-direction:column}.topic-document-row{grid-template-columns:40px minmax(0,1fr)}.topic-document-date,.topic-document-actions{grid-column:2}.topic-document-date{justify-self:start}}
    /* Match the approved compact admin repository composition. */
    .repository-intro,.policy-unit-view,.policy-kpis,.policy-filter{display:none}.policy-repository{overflow:visible;border:0;background:transparent;box-shadow:none}.policy-repository-head{padding:18px 0;border:0;background:transparent}.policy-repository-title{color:#111;font-size:24px!important}.policy-repository-title .policy-count{display:block;padding:0;background:none;color:#111;font-size:11px}.policy-repository-title small{margin-top:2px;color:#344054;font-size:10px}.policy-view-tools{align-items:flex-end}.header-add-document{display:inline-flex;align-items:center;gap:5px;height:34px;margin-right:8px;padding:0 13px;border-radius:7px;background:#dceefa;color:#16324a;font-size:10px;font-weight:750;text-decoration:none}.header-add-document .material-icons{font-size:16px}.policy-view-tools select{height:34px}
    .topic-browser-layout{grid-template-columns:280px minmax(0,1fr);gap:12px;padding:0;background:transparent}.topic-browser-nav,.topic-browser-documents{border-radius:10px;box-shadow:none}.topic-browser-nav:before{display:none}.topic-browser-head{padding:13px 15px;background:#fff}.topic-browser-head strong{color:#111}.topic-browser-head span{color:#111;font-size:9px}.topic-tree-search{position:relative;margin:8px 10px 2px}.topic-tree-search .material-icons{position:absolute;top:8px;left:9px;color:#667085;font-size:17px}.topic-tree-search input{width:100%;height:34px;padding:0 34px;border:1px solid #cfd8de;border-radius:7px;background:#fff;color:#344054;font-size:9px}.topic-tree-search button{position:absolute;top:4px;right:4px;display:grid;place-items:center;width:26px;height:26px;border:0;border-radius:5px;background:#f2f4f7;color:#667085}.topic-tree-search button .material-icons{position:static;font-size:15px}.topic-nav-list{max-height:600px;padding:7px 10px}.topic-nav-list>.topic-nav-link{padding:8px 9px;border:0;background:#f0f0f0;color:#222}.topic-nav-category{margin:0;padding:7px 4px;border:0;border-radius:0}.topic-nav-category:hover{border:0;background:#fafafa}.topic-nav-category-link{color:#111}.topic-nav-category-link .material-icons{width:18px;height:18px;flex-basis:18px;background:transparent;color:#333}.topic-nav-main{margin-left:22px;border-left:1px solid #ccc;color:#111}.topic-nav-main:before{display:none}.topic-nav-subtopics{margin-left:22px}.topic-nav-category-link.active,.topic-nav-main.active,.topic-nav-link.active{box-shadow:none;background:#ececec;color:#111}
    .topic-browser-documents{border:0;background:transparent}.topic-browser-documents>.topic-browser-head{min-height:49px;border:1px solid #d7dde1;border-radius:9px;box-shadow:0 2px 5px rgba(20,30,40,.08)}.topic-browser-documents>.topic-browser-head:after{background:#eee;color:#111}.topic-browser-documents .topic-document-list{display:flex;flex-direction:column;gap:8px;min-height:0;margin-top:8px}.topic-document-row{min-height:68px;border:1px solid #d8dde0!important;border-radius:9px;background:#fff;box-shadow:0 2px 5px rgba(20,30,40,.11)}.topic-document-row:before{display:none}.topic-document-row:hover{background:#fff;transform:translateY(-1px);box-shadow:0 5px 12px rgba(20,30,40,.13)}.topic-pdf-icon{width:34px;height:38px;border-color:#ead7d9;border-radius:9px;background:linear-gradient(145deg,#f5e6e7,#ecd9db);color:#772c35;box-shadow:none}.topic-document-copy a{color:#111;font-size:10px}.topic-document-meta{margin-top:3px;color:#111;font-size:8px}.topic-document-date{background:transparent;color:#111;font-size:9px}.topic-view-button,.topic-edit-button{height:34px;border-color:#cbd2d7;background:#f8f8f8;color:#111;box-shadow:none}.topic-view-button:hover,.topic-edit-button:hover{background:#eceff1;color:#111}.policy-footer{margin-top:8px;border:0;background:transparent}
    /* Accessible reading scale and larger interaction targets. */
    .policy-repository-title{font-size:28px!important;line-height:1.2}.policy-repository-title .policy-count{margin-top:4px;font-size:14px}.policy-repository-title small{margin-top:5px;font-size:13px;line-height:1.5}.policy-view-tools,.policy-view-tools select{font-size:13px}.header-add-document{height:42px;padding:0 17px;font-size:13px}.policy-view-tools select{width:190px;height:42px;padding:0 14px}
    .topic-browser-layout{grid-template-columns:360px minmax(0,1fr);gap:16px}.topic-browser-head{padding:18px 20px}.topic-browser-head strong{font-size:14px;line-height:1.35}.topic-browser-head span{font-size:12px;line-height:1.55}.topic-browser-documents>.topic-browser-head{min-height:66px}.topic-browser-documents>.topic-browser-head:after{padding:7px 12px;font-size:11px}
    .topic-tree-search{margin:12px 14px 5px}.topic-tree-search input{height:44px;padding:0 42px;font-size:14px}.topic-tree-search .material-icons{top:12px;left:12px;font-size:20px}.topic-tree-search button{top:5px;right:5px;width:34px;height:34px}.topic-tree-search button .material-icons{font-size:18px}.topic-nav-list{max-height:680px;padding:10px 14px}.topic-nav-list>.topic-nav-link{min-height:42px;padding:11px 12px;font-size:12px}.topic-nav-category{padding:10px 6px}.topic-nav-category-link{font-size:12px;line-height:1.5}.topic-nav-category-link .material-icons{width:26px;height:26px;flex-basis:26px;font-size:19px}.topic-nav-main{margin:10px 0 0 32px;padding:8px 10px;font-size:12px;line-height:1.5}.topic-nav-subtopics{margin:4px 0 0 34px}.topic-nav-link{min-height:36px;padding:8px 10px;font-size:11px;line-height:1.45}.topic-nav-link .material-icons{font-size:18px}.topic-nav-empty{display:block;padding:8px 10px;font-size:10px;line-height:1.45}
    .topic-browser-documents .topic-document-list{gap:10px;margin-top:10px}.topic-document-row{grid-template-columns:52px minmax(0,1fr) 120px auto;min-height:96px;padding:17px 20px}.topic-pdf-icon{width:46px;height:52px;font-size:10px}.topic-document-copy a{font-size:14px;line-height:1.45}.topic-document-meta{gap:8px;margin-top:8px;color:#475467;font-size:11px;line-height:1.5}.topic-document-meta .status-pill{font-size:11px}.topic-document-date{font-size:12px;line-height:1.4}.topic-view-button,.topic-edit-button{height:44px;font-size:13px}.topic-view-button{min-width:88px}.topic-edit-button{min-width:44px}.topic-view-button .material-icons,.topic-edit-button .material-icons{font-size:18px}.policy-footer{padding:14px 4px;font-size:12px}
    @media(max-width:1200px){.topic-browser-layout{grid-template-columns:310px minmax(0,1fr)}.topic-document-row{grid-template-columns:48px minmax(0,1fr) 105px auto}.topic-document-copy a{font-size:13px}}
    @media(max-width:900px){.topic-browser-layout{grid-template-columns:1fr}.topic-nav-list{max-height:360px}}
    .topic-tree-tools{display:flex;gap:7px;margin:8px 14px 4px}.topic-tree-tool{display:inline-flex;align-items:center;justify-content:center;gap:4px;flex:1;height:34px;border:1px solid #d2dde1;border-radius:7px;background:#fff;color:#344054;font-size:10px;font-weight:700}.topic-tree-tool:hover{border-color:#8fc8c2;background:#f1faf8;color:#006d70}.topic-tree-tool .material-icons{font-size:16px}.topic-nav-category-link,.topic-nav-main{position:relative;padding-right:34px}.topic-toggle{position:absolute;top:50%;right:3px;display:grid;place-items:center;width:28px;height:28px;border:0;border-radius:6px;background:transparent;color:#667085;transform:translateY(-50%);cursor:pointer}.topic-toggle:hover{background:#e7f3f1;color:#006d70}.topic-toggle .material-icons{font-size:19px;transition:transform .18s}.topic-nav-category.is-collapsed>.topic-category-children,.topic-main-item.is-collapsed>.topic-nav-subtopics{display:none}.topic-nav-category.is-collapsed>.topic-nav-category-link .topic-toggle .material-icons,.topic-main-item.is-collapsed>.topic-nav-main .topic-toggle .material-icons{transform:rotate(-90deg)}.topic-main-item{display:block}
    .topic-browser-documents>.topic-browser-head{display:none!important}.topic-browser-documents .topic-document-list{margin-top:0!important}.topic-browser-layout{align-items:start}.topic-browser-documents .topic-document-row:first-child{margin-top:0}.policy-repository-head{margin-bottom:14px;padding-bottom:0!important}.topic-browser-nav{box-shadow:0 6px 18px rgba(31,42,55,.07)}.topic-document-row{border-color:#dce4e7!important;box-shadow:0 4px 13px rgba(31,42,55,.07)}
    .document-pagination{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:16px 0 4px;padding:14px 16px;border:1px solid #dce5e8;border-radius:10px;background:#fff;box-shadow:0 4px 12px rgba(31,42,55,.05)}.document-pagination-info{color:#667085;font-size:12px}.document-pagination-pages{display:flex;align-items:center;gap:6px}.document-page-link{display:grid;place-items:center;min-width:38px;height:38px;padding:0 10px;border:1px solid #d4dee2;border-radius:8px;background:#fff;color:#344054;font-size:12px;font-weight:750;text-decoration:none}.document-page-link:hover{border-color:#00958b;background:#eef9f7;color:#006d70;text-decoration:none}.document-page-link.active{border-color:#008f86;background:#008f86;color:#fff;box-shadow:0 4px 10px rgba(0,143,134,.2)}.document-page-link.disabled{color:#98a2b3;pointer-events:none}.document-page-ellipsis{padding:0 4px;color:#98a2b3}.policy-footer{display:none!important}@media(max-width:767px){.document-pagination{align-items:flex-start;flex-direction:column}.document-pagination-pages{max-width:100%;overflow-x:auto}.document-pagination-info{font-size:11px}}
    .topic-nav-category-link>a,.topic-nav-main>a{display:flex;align-items:center;gap:7px;color:inherit;text-decoration:none}.topic-nav-main>a{display:block}.topic-nav-category-link>a:hover,.topic-nav-main>a:hover{color:#008f86;text-decoration:none}
    .policy-repository-head{justify-content:flex-end!important;padding:6px 0 14px!important}.policy-repository-head>.policy-repository-title{display:none!important}.policy-repository-head .policy-view-tools{margin-left:0}
    .topic-pdf-icon{overflow:hidden;padding:0!important;border-color:#cfe3df!important;background:linear-gradient(145deg,#e9f7f4,#dff1ed)!important;color:#007b75!important}.topic-pdf-icon .material-icons{font-size:25px}.topic-pdf-icon.type-circular-icon{border-color:#d6e3f5!important;background:linear-gradient(145deg,#edf5ff,#e2edfc)!important;color:#2367b1!important}.topic-pdf-icon.type-guideline-icon{border-color:#f0dfbd!important;background:linear-gradient(145deg,#fff8e9,#f8edd5)!important;color:#b56b08!important}
    /* Document overview and filters. */
    .policy-kpis{display:grid!important;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin:0 0 16px}.policy-kpi{min-height:94px;padding:16px 18px;border-radius:12px;background:#fff}.policy-kpi-icon{flex-basis:54px;height:54px;border-radius:12px}.policy-kpi-icon .material-icons{font-size:29px}.policy-kpi strong{font-size:27px}.policy-kpi small{margin-top:7px;color:#344054;font-size:13px}
    .policy-filter{display:grid!important;grid-template-columns:minmax(300px,1.8fr) repeat(4,minmax(150px,1fr)) auto auto;gap:14px;align-items:end;margin:0 0 18px;padding:18px;border-radius:12px;background:#fff}.policy-field label{margin-bottom:7px;color:#172b4d;font-size:12px}.policy-field .form-control{height:46px;border-radius:8px;font-size:13px}.policy-search .material-icons{top:37px;font-size:21px}.policy-search .form-control{padding-left:42px}.policy-search-hint{display:none}.policy-filter .btn{height:46px;min-width:78px;font-size:13px}.policy-repository-head{padding-top:4px}
    @media(max-width:1400px){.policy-kpis{grid-template-columns:repeat(5,minmax(145px,1fr));overflow-x:auto}.policy-filter{grid-template-columns:repeat(3,1fr)}.policy-search{grid-column:span 2}}
    @media(max-width:767px){.policy-kpis{grid-template-columns:1fr 1fr;overflow:visible}.policy-filter{grid-template-columns:1fr}.policy-search{grid-column:auto}}
    @media(max-width:1300px){.policy-filter{grid-template-columns:repeat(3,1fr)}.policy-search{grid-column:span 2}}
    @media(max-width:767px){.repository-intro{align-items:flex-start;flex-direction:column}.repository-add{width:100%;justify-content:center}.policy-search{grid-column:auto}.policy-filter{grid-template-columns:1fr}.policy-view-tools{margin-left:0}}
</style>

<div class="policy-index">
    @php
        $unitQuery = fn (?string $unit) => route('policy-documents.index', array_filter(array_merge(request()->except('unit', 'page'), ['unit' => $unit]), fn ($value) => $value !== null && $value !== ''));
    @endphp
    <header class="repository-intro">
        <div><h2>Document repository</h2><p>Search, filter and manage governed policies, circulars and guidelines.</p></div>
        @if($canManageDocuments)<a class="repository-add" href="{{ route('policy-documents.create') }}"><span class="material-icons">add</span>Add document</a>@endif
    </header>
    <nav class="policy-unit-view" aria-label="View documents by organization">
        <div class="policy-unit-heading"><span class="material-icons">account_balance</span><span>View documents by organization</span></div>
        <div class="policy-unit-options">
            <a class="policy-unit-option {{ $selectedUnit === null ? 'active' : '' }}" href="{{ $unitQuery(null) }}">All accessible <b>{{ $unitStats['all'] }}</b></a>
            <a class="policy-unit-option {{ $selectedUnit === 'msd' ? 'active' : '' }}" href="{{ $unitQuery('msd') }}">MSD <b>{{ $unitStats['msd'] }}</b></a>
            <a class="policy-unit-option {{ $selectedUnit === 'kcdiom' ? 'active' : '' }}" href="{{ $unitQuery('kcdiom') }}">AIKOL <b>{{ $unitStats['kcdiom'] }}</b></a>
        </div>
    </nav>
    <section class="policy-kpis" aria-label="Document statistics">
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">description</span></span><div><strong>{{ $repositoryStats['total'] }}</strong><small>Total Documents</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">check_circle</span></span><div><strong>{{ $repositoryStats['published'] }}</strong><small>Active Documents</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">edit</span></span><div><strong>{{ $repositoryStats['draft'] }}</strong><small>Drafts</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">history_toggle_off</span></span><div><strong>{{ $repositoryStats['superseded'] }}</strong><small>Superseded</small></div></div>
        <div class="policy-kpi"><span class="policy-kpi-icon"><span class="material-icons">event</span></span><div><strong>{{ $repositoryStats['expiring'] }}</strong><small>Expiring in 30 days</small></div></div>
    </section>

    <form method="GET" class="policy-filter">
        @if(request('unit'))<input type="hidden" name="unit" value="{{ request('unit') }}">@endif
        <div class="policy-field policy-search"><label>Search documents <span class="policy-search-hint">Press /</span></label><span class="material-icons">search</span><input class="form-control" id="repositorySearch" name="q" value="{{ request('q') }}" placeholder="Title, reference number or keyword..." autocomplete="off"></div>
        <div class="policy-field"><label>Document Type</label><select class="form-control" name="document_type"><option value="">All Types</option>@foreach($documentTypes as $type=>$label)<option value="{{ $type }}" @selected(request('document_type')===$type)>{{ $label }}</option>@endforeach</select></div>
        <div class="policy-field"><label>Category of Topic</label><select class="form-control" name="topic_category"><option value="">All Categories</option>@foreach($topicCategories as $slug=>$label)<option value="{{ $slug }}" @selected(request('topic_category')===$slug)>{{ $label }}</option>@endforeach</select></div>
        <div class="policy-field"><label>Main Topic</label><select class="form-control" name="subtopic_id"><option value="">All Topics</option>@foreach($subtopics as $topic)<option value="{{ $topic->id }}" @selected((string)request('subtopic_id')===(string)$topic->id)>{{ $topic->name }}</option>@endforeach</select></div>
        <div class="policy-field"><label>Status</label><select class="form-control" name="status"><option value="">All Statuses</option>@foreach($documentStatuses as $status=>$label)@if($canManageDocuments || $status !== 'draft')<option value="{{ $status }}" @selected(request('status')===$status)>{{ $label }}</option>@endif @endforeach</select></div>
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-outline-primary" href="{{ route('policy-documents.index', request('unit') ? ['unit'=>request('unit')] : []) }}">Clear</a>
    </form>

    <section class="policy-repository">
        <header class="policy-repository-head">
            <div class="policy-repository-title">Documents <span class="policy-count">{{ $documents->total() }} {{ Str::plural('document',$documents->total()) }}</span><small>Staff-style table view with administrator actions.</small></div>
            <form method="GET" class="policy-view-tools">
                @if($canManageDocuments)<a class="header-add-document" href="{{ route('policy-documents.create') }}"><span class="material-icons">add</span>Add New Document</a>@endif
                @foreach(request()->except('sort','page') as $key=>$value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                <span>Sort by:</span><select name="sort" onchange="this.form.submit()"><option value="" @selected(!request('sort'))>Latest Updated</option><option value="title" @selected(request('sort')==='title')>Title A–Z</option><option value="oldest" @selected(request('sort')==='oldest')>Oldest Updated</option><option value="effective" @selected(request('sort')==='effective')>Effective Date</option></select>
            </form>
        </header>
        @php
            $categorizedDocuments = $documents->getCollection()->groupBy(function ($document) use ($topicCategories) {
                $category = $document->topic_category ? ($topicCategories[$document->topic_category] ?? ucfirst($document->topic_category)) : 'Uncategorized';
                return strtoupper($document->owner_unit ?: 'unassigned').'|'.$category;
            })->sortBy(function ($documents, $key) {
                [$unit, $category] = explode('|', $key, 2);
                $unitOrder = ['MSD' => 0, 'KCDIOM' => 1, 'UNASSIGNED' => 2];
                return sprintf('%02d-%s', $unitOrder[$unit] ?? 9, $category);
            });
        @endphp
        <div class="policy-groups" hidden>
        @forelse($categorizedDocuments as $unitCategory=>$categoryDocuments)
            @php([$ownerUnit, $categoryName] = explode('|', $unitCategory, 2))
            <details class="policy-category" @if(request()->filled('q') || request()->filled('document_type') || request()->filled('topic_category') || request()->filled('subtopic_id') || request()->filled('status')) open @endif>
                <summary class="policy-category-head"><span class="policy-category-name"><span class="material-icons">folder</span><span class="policy-unit-badge">{{ $ownerUnit }}</span>CATEGORY OF TOPIC: {{ $categoryName }}</span><span class="group-actions"><span class="group-count">{{ $categoryDocuments->count() }} {{ Str::plural('document',$categoryDocuments->count()) }}</span><span class="material-icons">expand_more</span></span></summary>
                @foreach($categoryDocuments->groupBy(fn($document) => $document->subtopic?->name ?: 'Main topic not assigned') as $mainTopicName=>$mainTopicDocuments)
                    <details class="policy-topic" @if(request()->filled('q') || request()->filled('document_type') || request()->filled('topic_category') || request()->filled('subtopic_id') || request()->filled('status')) open @endif>
                        <summary class="policy-topic-head"><span class="policy-topic-name">MAIN TOPIC: {{ $mainTopicName }}</span><span class="group-actions"><span class="group-count">{{ $mainTopicDocuments->count() }} {{ Str::plural('document',$mainTopicDocuments->count()) }}</span><span class="material-icons">expand_more</span></span></summary>
                        @foreach($mainTopicDocuments->groupBy(fn($document) => $document->topicDetail?->name ?: 'Subtopic not assigned') as $subtopicName=>$subtopicDocuments)
                            <div class="policy-subtopic-head"><span>SUBTOPIC: {{ $subtopicName }}</span><span>{{ $subtopicDocuments->count() }} {{ Str::plural('record',$subtopicDocuments->count()) }}</span></div>
                            <div class="policy-table-scroll"><div class="policy-table-head"><span>Type</span><span>Reference No.</span><span>Title</span><span>Version</span><span>Effective Date</span><span>Updated</span><span>Status</span><span>Actions</span></div>
                            @foreach($subtopicDocuments as $doc)
                                <article class="policy-document-row">
                                    <span class="type-pill type-{{ $doc->document_type }}">{{ $documentTypes[$doc->document_type] ?? ucfirst($doc->document_type) }}</span>
                                    <span>{{ $doc->reference_number ?: '—' }}</span>
                                    <span class="doc-title"><a href="{{ route('policy-documents.show',$doc) }}">{{ trim($doc->title," -\t\n\r\0\x0B") ?: 'Untitled document' }}</a><small>Show more</small></span>
                                    <span>{{ $doc->version_number }}</span>
                                    <span class="doc-date"><span class="material-icons">event</span>{{ $doc->effective_date?->format('d M Y') ?? '—' }}</span>
                                    <span>{{ $doc->updated_at?->format('d M Y') ?? '—' }}</span>
                                    <span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span>
                                    <span class="doc-actions"><a class="doc-action" href="{{ route('policy-documents.show',$doc) }}" title="View"><span class="material-icons">visibility</span></a>@if($canManageDocuments)<a class="doc-action" href="{{ route('policy-documents.edit',$doc) }}" title="Edit"><span class="material-icons">more_vert</span></a>@endif</span>
                                </article>
                            @endforeach</div>
                        @endforeach
                    </details>
                @endforeach
            </details>
        @empty
            <div class="empty-policy"><span class="material-icons">folder_off</span><h5>No documents found</h5><p>Try changing the filters.</p></div>
        @endforelse
        </div>
        <div class="staff-style-table-wrap"><table class="staff-style-table">
            <thead><tr><th>Document</th><th>Reference No.</th><th>Type</th><th>Topic</th><th>Version</th><th>Effective date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($documents as $doc)
                <tr>
                    <td class="staff-doc-title"><a href="{{ route('policy-documents.show',$doc) }}">{{ trim($doc->title," -\t\n\r\0\x0B") ?: 'Untitled document' }}</a><small>Updated {{ $doc->updated_at?->format('d M Y') ?? '—' }} · {{ strtoupper($doc->owner_unit ?: 'Unassigned') }}</small></td>
                    <td>{{ $doc->reference_number ?: '—' }}</td>
                    <td><span class="type-pill type-{{ $doc->document_type }}">{{ $documentTypes[$doc->document_type] ?? ucfirst($doc->document_type) }}</span></td>
                    <td class="staff-topic-cell"><strong>{{ $doc->subtopic?->name ?: 'Main topic not assigned' }}</strong><small>{{ $doc->topicDetail?->name ?: ($doc->topic_category ? ($topicCategories[$doc->topic_category] ?? ucfirst($doc->topic_category)) : 'Uncategorized') }}</small></td>
                    <td>{{ $doc->version_number }}</td>
                    <td><span class="doc-date"><span class="material-icons">event</span>{{ $doc->effective_date?->format('d M Y') ?? '—' }}</span></td>
                    <td><span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span></td>
                    <td><span class="doc-actions"><a class="doc-action" href="{{ route('policy-documents.show',$doc) }}" title="View document" aria-label="View document"><span class="material-icons">visibility</span></a>@if($canManageDocuments)<a class="doc-action" href="{{ route('policy-documents.edit',$doc) }}" title="Edit document" aria-label="Edit document"><span class="material-icons">edit</span></a>@endif</span></td>
                </tr>
            @empty
                <tr><td colspan="8" class="staff-table-empty"><span class="material-icons">folder_off</span><strong>No documents found</strong><div>Try changing the search or filters.</div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        <div class="topic-document-list" hidden>
            @forelse($categorizedDocuments as $unitCategory => $categoryDocuments)
                @php([$ownerUnit, $categoryName] = explode('|', $unitCategory, 2))
                <section class="document-category-group">
                    <header class="document-category-heading"><span><span class="material-icons">folder</span><span class="policy-unit-badge">{{ $ownerUnit }}</span>Category of Topic: {{ $categoryName }}</span><small>{{ $categoryDocuments->count() }} {{ Str::plural('document',$categoryDocuments->count()) }}</small></header>
                    @foreach($categoryDocuments->groupBy(fn($document) => $document->subtopic?->name ?: 'Main topic not assigned') as $mainTopicName => $mainTopicDocuments)
                        <section class="document-main-group">
                            <header class="document-main-heading"><span>Main Topic: {{ $mainTopicName }}</span><small>{{ $mainTopicDocuments->count() }} {{ Str::plural('document',$mainTopicDocuments->count()) }}</small></header>
                            @foreach($mainTopicDocuments->groupBy(fn($document) => $document->topicDetail?->name ?: 'Subtopic not assigned') as $subtopicName => $subtopicDocuments)
                                <div class="document-subtopic-heading"><span>Subtopic: {{ $subtopicName }}</span><small>{{ $subtopicDocuments->count() }} {{ Str::plural('record',$subtopicDocuments->count()) }}</small></div>
                                @foreach($subtopicDocuments as $doc)
                <article class="topic-document-row">
                    <span class="topic-pdf-icon type-{{ $doc->document_type }}-icon" aria-hidden="true"><span class="material-icons">{{ $doc->document_type === 'circular' ? 'campaign' : ($doc->document_type === 'guideline' ? 'menu_book' : 'description') }}</span></span>
                    <div class="topic-document-copy">
                        <a href="{{ route('policy-documents.show',$doc) }}">{{ trim($doc->title," -\t\n\r\0\x0B") ?: 'Untitled document' }}</a>
                        <div class="topic-document-meta">
                            <span>{{ $doc->reference_number ?: 'No reference number' }}</span>
                            <span>{{ $doc->topicDetail?->name ?: ($doc->subtopic?->name ?: 'Topic not assigned') }}</span>
                            <span>Version {{ $doc->version_number }}</span>
                            <span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span>
                        </div>
                    </div>
                    <time class="topic-document-date" datetime="{{ $doc->effective_date?->format('Y-m-d') }}">{{ $doc->effective_date?->format('d M Y') ?? 'Date not set' }}</time>
                    <div class="topic-document-actions">
                        <a class="topic-view-button" href="{{ route('policy-documents.show',$doc) }}">View <span class="material-icons">arrow_forward</span></a>
                        @if($canManageDocuments)<a class="topic-edit-button" href="{{ route('policy-documents.edit',$doc) }}" title="Edit document" aria-label="Edit {{ $doc->title }}"><span class="material-icons">edit</span></a>@endif
                    </div>
                </article>
                                @endforeach
                            @endforeach
                        </section>
                    @endforeach
                </section>
            @empty
                <div class="topic-list-empty"><span class="material-icons">folder_off</span><strong>No documents found</strong><p>Try changing the search or filters.</p></div>
            @endforelse
        </div>
        <div class="topic-browser-layout">
            <aside class="topic-browser-nav">
                <header class="topic-browser-head"><strong>Category Topics</strong><span>Browse by category, main topic and subtopic</span></header>
                @if($managedTopics->isNotEmpty())
                <div class="topic-tree-search"><span class="material-icons">search</span><input type="search" id="managedTopicSearch" placeholder="Search category topics..." aria-label="Search category topics"><button type="button" id="clearManagedTopicSearch" title="Clear topic search"><span class="material-icons">close</span></button></div>
                <div class="topic-tree-tools"><button type="button" class="topic-tree-tool" id="expandManagedTopics"><span class="material-icons">unfold_more</span>Expand all</button><button type="button" class="topic-tree-tool" id="collapseManagedTopics"><span class="material-icons">unfold_less</span>Collapse all</button></div>
                <div class="topic-nav-list">
                    <a class="topic-nav-link {{ !request()->filled('topic_category') && !request()->filled('subtopic_id') && !request()->filled('topic_detail_id') ? 'active' : '' }}" href="{{ route('policy-documents.index', request()->except('page','topic_category','subtopic_id','topic_detail_id','q')) }}"><span class="material-icons">view_list</span>All category topics</a>
                    @forelse($managedTopics as $category)
                        <section class="topic-nav-category is-collapsed" data-topic-key="category-{{ $category->id }}">
                            <div class="topic-nav-category-link {{ request('topic_category') === $category->slug ? 'active' : '' }}"><a href="{{ route('policy-documents.index', array_merge(request()->except('page','subtopic_id','topic_detail_id','q'), ['topic_category' => $category->slug])) }}"><span class="material-icons">folder</span>{{ $category->name }}</a><button type="button" class="topic-toggle" aria-label="Expand {{ $category->name }}" aria-expanded="false"><span class="material-icons">expand_more</span></button></div>
                            <div class="topic-category-children">
                            @foreach($category->subtopics as $mainTopic)
                                <div class="topic-main-item is-collapsed" data-topic-key="main-{{ $mainTopic->id }}"><div class="topic-nav-main {{ (string)request('subtopic_id') === (string)$mainTopic->id ? 'active' : '' }}"><a href="{{ route('policy-documents.index', array_merge(request()->except('page','topic_category','topic_detail_id','q'), ['subtopic_id' => $mainTopic->id])) }}">{{ $mainTopic->name }}</a><button type="button" class="topic-toggle" aria-label="Expand {{ $mainTopic->name }}" aria-expanded="false"><span class="material-icons">expand_more</span></button></div>
                                <div class="topic-nav-subtopics">
                                    @forelse($mainTopic->details as $detail)
                                        <a class="topic-nav-link {{ (string)request('topic_detail_id') === (string)$detail->id ? 'active' : '' }}" href="{{ route('policy-documents.index', array_merge(request()->except('page','topic_category','subtopic_id','q'), ['topic_detail_id' => $detail->id])) }}"><span class="material-icons">subdirectory_arrow_right</span>{{ $detail->name }}</a>
                                    @empty
                                        <span class="topic-nav-empty">No subtopics configured</span>
                                    @endforelse
                                </div>
                                </div>
                            @endforeach
                            </div>
                        </section>
                    @empty
                        <p class="topic-list-empty">No category topics found.</p>
                    @endforelse
                </div>
                @else
                    <div class="topic-list-empty"><span class="material-icons">folder_off</span><strong>No category topics</strong><p>No category topics have been configured for AIKOL.</p></div>
                @endif
            </aside>
            <section class="topic-browser-documents">
                <div class="topic-document-list">
                    @forelse($documents as $doc)
                        <article class="topic-document-row">
                            <span class="topic-pdf-icon type-{{ $doc->document_type }}-icon" aria-hidden="true"><span class="material-icons">{{ $doc->document_type === 'circular' ? 'campaign' : ($doc->document_type === 'guideline' ? 'menu_book' : 'description') }}</span></span>
                            <div class="topic-document-copy"><a href="{{ route('policy-documents.show',$doc) }}">{{ trim($doc->title," -\t\n\r\0\x0B") ?: 'Untitled document' }}</a><div class="topic-document-meta"><span>{{ $doc->reference_number ?: 'No reference number' }}</span><span>{{ $doc->topicDetail?->name ?: ($doc->subtopic?->name ?: 'Topic not assigned') }}</span><span>Version {{ $doc->version_number }}</span><span class="status-pill status-{{ $doc->status }}">{{ $documentStatuses[$doc->status] ?? ucfirst($doc->status) }}</span></div></div>
                            <time class="topic-document-date" datetime="{{ $doc->effective_date?->format('Y-m-d') }}">{{ $doc->effective_date?->format('d M Y') ?? 'Date not set' }}</time>
                            <div class="topic-document-actions"><a class="topic-view-button" href="{{ route('policy-documents.show',$doc) }}">View <span class="material-icons">arrow_forward</span></a>@if($canManageDocuments)<a class="topic-edit-button" href="{{ route('policy-documents.edit',$doc) }}" title="Edit document"><span class="material-icons">edit</span></a>@endif</div>
                        </article>
                    @empty
                        <div class="topic-list-empty"><span class="material-icons">folder_off</span><strong>No documents found</strong><p>Try changing the search or filters.</p></div>
                    @endforelse
                </div>
                @if($documents->hasPages())
                    <nav class="document-pagination" aria-label="Document pages">
                        <span class="document-pagination-info">Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }} documents</span>
                        <span class="document-pagination-pages">
                            @if($documents->onFirstPage())<span class="document-page-link disabled">Previous</span>@else<a class="document-page-link" href="{{ $documents->previousPageUrl() }}" rel="prev">Previous</a>@endif
                            @foreach($documents->getUrlRange(1, $documents->lastPage()) as $page => $url)
                                <a class="document-page-link {{ $page === $documents->currentPage() ? 'active' : '' }}" href="{{ $url }}" @if($page === $documents->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                            @endforeach
                            @if($documents->hasMorePages())<a class="document-page-link" href="{{ $documents->nextPageUrl() }}" rel="next">Next</a>@else<span class="document-page-link disabled">Next</span>@endif
                        </span>
                    </nav>
                @endif
            </section>
        </div>
        <footer class="policy-footer"><span>Showing {{ $documents->firstItem() ?? 0 }} to {{ $documents->lastItem() ?? 0 }} of {{ $documents->total() }} records</span>@if($documents->hasPages()){{ $documents->links() }}@endif</footer>
    </section>
</div>
<script>
    (() => {
        const search = document.getElementById('repositorySearch');
        const topicSearch = document.getElementById('managedTopicSearch');
        const topicGroups = Array.from(document.querySelectorAll('.topic-nav-category'));
        const filterTopics = () => {
            const term = topicSearch?.value.trim().toLowerCase() ?? '';
            topicGroups.forEach((group) => { group.hidden = term !== '' && !group.textContent.toLowerCase().includes(term); });
        };
        topicSearch?.addEventListener('input', filterTopics);
        document.getElementById('clearManagedTopicSearch')?.addEventListener('click', () => {
            topicSearch.value = '';
            filterTopics();
            topicSearch.focus();
        });
        const treeItems = Array.from(document.querySelectorAll('.topic-nav-category, .topic-main-item'));
        const treeStorageKey = 'policy-document-topic-tree-open';
        const saveTreeState = () => {
            const openKeys = treeItems.filter((item) => !item.classList.contains('is-collapsed')).map((item) => item.dataset.topicKey).filter(Boolean);
            localStorage.setItem(treeStorageKey, JSON.stringify(openKeys));
        };
        const setCollapsed = (item, collapsed) => {
            item.classList.toggle('is-collapsed', collapsed);
            const toggle = item.querySelector(':scope > .topic-nav-category-link > .topic-toggle, :scope > .topic-nav-main > .topic-toggle');
            toggle?.setAttribute('aria-expanded', String(!collapsed));
            if (toggle) toggle.setAttribute('aria-label', `${collapsed ? 'Expand' : 'Collapse'} ${toggle.getAttribute('aria-label')?.replace(/^(Expand|Collapse)\s+/, '') ?? 'topic'}`);
        };
        try {
            const openKeys = JSON.parse(localStorage.getItem(treeStorageKey) || '[]');
            treeItems.forEach((item) => setCollapsed(item, !openKeys.includes(item.dataset.topicKey)));
        } catch (_) {
            treeItems.forEach((item) => setCollapsed(item, true));
        }
        document.querySelectorAll('.topic-nav-category-link.active, .topic-nav-main.active, .topic-nav-link.active').forEach((active) => {
            active.closest('.topic-main-item') && setCollapsed(active.closest('.topic-main-item'), false);
            active.closest('.topic-nav-category') && setCollapsed(active.closest('.topic-nav-category'), false);
        });
        document.querySelectorAll('.topic-toggle').forEach((toggle) => toggle.addEventListener('click', () => {
            const item = toggle.closest('.topic-nav-category, .topic-main-item');
            if (item) { setCollapsed(item, !item.classList.contains('is-collapsed')); saveTreeState(); }
        }));
        document.getElementById('expandManagedTopics')?.addEventListener('click', () => { treeItems.forEach((item) => setCollapsed(item, false)); saveTreeState(); });
        document.getElementById('collapseManagedTopics')?.addEventListener('click', () => { treeItems.forEach((item) => setCollapsed(item, true)); saveTreeState(); });
        document.addEventListener('keydown', (event) => {
            if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey && !['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement?.tagName)) {
                event.preventDefault();
                search?.focus();
                search?.select();
            }
        });
    })();
</script>
@endsection
