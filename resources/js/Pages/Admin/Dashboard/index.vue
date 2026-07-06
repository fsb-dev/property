<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="./support.js"></script>
</head>
<body>
<x-dc>
<helmet>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{ --accent:#5B3DF5; --accent-2:#7C5CFF; --accent-soft:#F1ECFF; }
  *{ box-sizing:border-box; }
  html,body{ margin:0; padding:0; }
  body{ font-family:'Inter',system-ui,sans-serif; -webkit-font-smoothing:antialiased; }
  ::-webkit-scrollbar{ width:8px; height:8px; }
  ::-webkit-scrollbar-thumb{ background:#D6DBE6; border-radius:8px; }
  ::-webkit-scrollbar-track{ background:transparent; }
  @keyframes hvUp{ from{ opacity:0; transform:translateY(12px); } to{ opacity:1; transform:translateY(0); } }
  @keyframes hvDash{ from{ stroke-dashoffset:var(--len); } }
  @keyframes hvGrow{ from{ transform:scaleX(0); } to{ transform:scaleX(1); } }
</style>
</helmet>
<div style="display:flex; height:100vh; width:100%; background:#F8F9FC; color:#151B2E; overflow:hidden; font-family:'Inter',sans-serif;">

  <!-- ============ SIDEBAR ============ -->
  <aside style="flex:0 0 260px; width:260px; height:100vh; background:linear-gradient(178deg,#0A1B36 0%,#07162D 60%,#061224 100%); color:#fff; display:flex; flex-direction:column; padding:22px 16px 18px; overflow-y:auto;">
    <div style="display:flex; align-items:center; gap:12px; padding:4px 8px 22px;">
      <div style="width:42px; height:42px; border-radius:12px; background:linear-gradient(145deg,var(--accent),var(--accent-2)); display:flex; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(91,61,245,0.45);">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-7 9 7"/><path d="M5 10v9h5v-5h4v5h5v-9"/></svg>
      </div>
      <div style="line-height:1.1;">
        <div style="font-size:19px; font-weight:800; letter-spacing:-0.3px;">HomeVerse</div>
        <div style="font-size:10px; font-weight:600; letter-spacing:2px; color:#6E7C95;">ADMIN PANEL</div>
      </div>
    </div>

    <nav style="display:flex; flex-direction:column; gap:4px; flex:1;">
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; background:linear-gradient(135deg,var(--accent),var(--accent-2)); color:#fff; font-size:14px; font-weight:600; box-shadow:0 8px 20px rgba(91,61,245,0.4); cursor:pointer;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        <span>Dashboard</span>
      </div>

      <a href="HomeVerse%20Projects.dc.html" style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M19 21V11l-4-2"/><path d="M9 7h2M9 11h2M9 15h2"/></svg>
        <span>Projects</span>
      </a>
      <a href="HomeVerse%20Inventory.dc.html" style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8"/></svg>
        <span>Unit Inventory</span>
      </a>
      <a href="HomeVerse%20CRM.dc.html" style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/></svg>
        <span>Buyers / CRM</span>
      </a>
      <a href="HomeVerse%20Sales.dc.html" style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3a1 1 0 011-1h4a1 1 0 011 1v1M9 11h6M9 15h6"/></svg>
        <span>Reservations &amp; Sales</span>
      </a>
      <a href="HomeVerse%20Payments.dc.html" style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/></svg>
        <span>Payments &amp; Installments</span>
      </a>
      <a href="HomeVerse%20Mortgage.dc.html" style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M4 21V10M9 21V10M15 21V10M20 21V10M12 3l8 5H4l8-5z"/></svg>
        <span>Mortgage &amp; Financing</span>
      </a>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 7-7M14 8h6v6"/></svg>
        <span>Investment Data</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16a8 8 0 0116 0M2 16h20M9 9V6a3 3 0 016 0v3"/></svg>
        <span>Construction Updates</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 010 18 14 14 0 010-18z"/></svg>
        <span>Community &amp; Future Vision</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M9 13h6M9 17h6"/></svg>
        <span>Documents</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="M4.9 4.9l3.5 3.5M15.6 15.6l3.5 3.5M19.1 4.9l-3.5 3.5M8.4 15.6l-3.5 3.5"/></svg>
        <span>Support Tickets</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.8 4.6L18 9l-4.2 1.4L12 15l-1.8-4.6L6 9l4.2-1.4L12 3z"/><path d="M19 14l.6 1.6 1.6.6-1.6.6-.6 1.6-.6-1.6-1.6-.6 1.6-.6z"/></svg>
        <span>Sara AI Knowledge</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M7 21V10M12 21V4M17 21v-7"/></svg>
        <span>Analytics &amp; Reports</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1.5A4.5 4.5 0 017.5 14h3A4.5 4.5 0 0115 18.5V20"/><circle cx="18" cy="9" r="2.2"/></svg>
        <span>Users &amp; Roles</span>
      </div>
      <div style="display:flex; align-items:center; gap:12px; padding:11px 14px; border-radius:12px; color:#9AA5BC; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.06); color:#fff;">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg>
        <span>Settings</span>
      </div>
    </nav>

    <div style="margin-top:18px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:14px; padding:14px;">
      <div style="font-size:13px; font-weight:700; margin-bottom:8px;">System Status</div>
      <div style="display:flex; align-items:center; gap:7px; font-size:12px; color:#C7D0E0; margin-bottom:10px;">
        <span style="width:8px; height:8px; border-radius:50%; background:#22C55E; box-shadow:0 0 0 3px rgba(34,197,94,0.18);"></span>
        All Systems Operational
      </div>
      <div style="font-size:11px; color:#6E7C95; line-height:1.5;">Last Backup<br><span style="color:#AEB8CC; font-weight:600;">15 May 2026, 02:30 AM</span></div>
      <button style="margin-top:11px; width:100%; padding:9px; border:none; border-radius:10px; background:rgba(255,255,255,0.08); color:#fff; font-family:inherit; font-size:12px; font-weight:600; cursor:pointer; transition:all .18s;" style-hover="background:rgba(255,255,255,0.16);">View System Logs</button>
    </div>

    <div style="margin-top:14px; display:flex; align-items:center; gap:11px; padding:8px 6px;">
      <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#3B4C6B,#1F2C45); display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; color:#fff;">FA</div>
      <div style="flex:1; line-height:1.2;">
        <div style="font-size:13px; font-weight:700;">Faisal Ahmed</div>
        <div style="font-size:11px; color:#6E7C95;">Super Admin</div>
      </div>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6E7C95" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
    </div>
  </aside>

  <!-- ============ MAIN ============ -->
  <main style="flex:1; height:100vh; overflow-y:auto; display:flex; flex-direction:column;">

    <!-- Header -->
    <header style="position:sticky; top:0; z-index:20; background:rgba(248,249,252,0.82); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); padding:22px 32px 16px; display:flex; align-items:flex-start; justify-content:space-between; gap:24px;">
      <div>
        <div style="font-size:30px; font-weight:800; letter-spacing:-0.6px; display:flex; align-items:center; gap:8px;">Dashboard Overview <span style="font-size:26px;">👋</span></div>
        <div style="font-size:14px; color:#697386; margin-top:4px;">Welcome back, Faisal Ahmed! Here's what's happening with your projects today.</div>
      </div>
      <div style="display:flex; align-items:center; gap:14px; flex-shrink:0;">
        <div style="display:flex; align-items:center; gap:10px; width:380px; max-width:34vw; background:#fff; border:1px solid #EEF2F8; border-radius:12px; padding:11px 14px; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#A0A8B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
          <input placeholder="Search by project, unit, buyer..." style="border:none; outline:none; flex:1; font-family:inherit; font-size:14px; color:#151B2E; background:transparent;" />
          <span style="font-size:11px; color:#A0A8B8; border:1px solid #E6EAF2; border-radius:6px; padding:2px 6px;">⌘K</span>
        </div>
        <div style="position:relative; width:42px; height:42px; border-radius:12px; background:#fff; border:1px solid #EEF2F8; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#3A4256" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/></svg>
          <span style="position:absolute; top:-5px; right:-5px; min-width:18px; height:18px; padding:0 4px; border-radius:9px; background:#EF4444; color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid #F8F9FC;">5</span>
        </div>
        <div style="position:relative; width:42px; height:42px; border-radius:12px; background:#fff; border:1px solid #EEF2F8; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#3A4256" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/></svg>
          <span style="position:absolute; top:-5px; right:-5px; min-width:18px; height:18px; padding:0 4px; border-radius:9px; background:var(--accent); color:#fff; font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; border:2px solid #F8F9FC;">12</span>
        </div>
        <div style="display:flex; align-items:center; gap:10px; background:#fff; border:1px solid #EEF2F8; border-radius:12px; padding:6px 10px 6px 6px; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
          <div style="width:34px; height:34px; border-radius:9px; background:linear-gradient(135deg,var(--accent),var(--accent-2)); display:flex; align-items:center; justify-content:center; color:#fff; font-size:13px; font-weight:700;">FA</div>
          <div style="line-height:1.2;">
            <div style="font-size:13px; font-weight:700;">Faisal Ahmed</div>
            <div style="font-size:11px; color:#697386;">Super Admin</div>
          </div>
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </div>
    </header>

    <!-- Content -->
    <div style="padding:8px 32px 0; display:flex; align-items:center; justify-content:flex-end;">
      <div style="display:flex; align-items:center; gap:10px; background:#fff; border:1px solid #EEF2F8; border-radius:12px; padding:10px 14px; font-size:13px; font-weight:600; color:#3A4256; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.03);">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
        01 May 2026 - 15 May 2026
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
      </div>
    </div>

    <div style="padding:18px 32px 0; display:flex; flex-direction:column; gap:24px;">

      <!-- KPI ROW -->
      <section style="display:grid; grid-template-columns:repeat(6,1fr); gap:18px; animation:hvUp .5s ease both;">
        <!-- card template -->
        <div style="background:#fff; border-radius:18px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:20px; transition:transform .2s,box-shadow .2s; cursor:default;" style-hover="transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,0.08);">
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:11px; background:var(--accent-soft); display:flex; align-items:center; justify-content:center; color:var(--accent);">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M19 21V11l-4-2"/></svg>
            </div>
            <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Projects</span>
          </div>
          <div style="font-size:32px; font-weight:800; letter-spacing:-1px; line-height:1;">8</div>
          <div style="font-size:12px; color:#697386; margin-top:8px;">Active: <b style="color:#151B2E;">6</b> &nbsp;·&nbsp; Completed: <b style="color:#151B2E;">2</b></div>
        </div>

        <div style="background:#fff; border-radius:18px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:20px; transition:transform .2s,box-shadow .2s;" style-hover="transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,0.08);">
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:11px; background:#E8F0FF; display:flex; align-items:center; justify-content:center; color:#3B82F6;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8"/></svg>
            </div>
            <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Units</span>
          </div>
          <div style="font-size:32px; font-weight:800; letter-spacing:-1px; line-height:1;">1,248</div>
          <div style="font-size:12px; color:#697386; margin-top:8px;">Sold: <b style="color:#151B2E;">632</b> &nbsp;·&nbsp; Available: <b style="color:#151B2E;">468</b></div>
        </div>

        <div style="background:#fff; border-radius:18px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:20px; transition:transform .2s,box-shadow .2s;" style-hover="transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,0.08);">
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:11px; background:#E6F7EE; display:flex; align-items:center; justify-content:center; color:#22C55E;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/></svg>
            </div>
            <span style="font-size:12.5px; font-weight:600; color:#697386;">Total Buyers</span>
          </div>
          <div style="display:flex; align-items:flex-end; gap:8px;">
            <div style="font-size:32px; font-weight:800; letter-spacing:-1px; line-height:1;">856</div>
            <span style="display:inline-flex; align-items:center; gap:2px; font-size:12px; font-weight:700; color:#22C55E; margin-bottom:3px;">▲ 18%</span>
          </div>
          <div style="font-size:12px; color:#697386; margin-top:8px;">This Month: <b style="color:#151B2E;">48</b> new</div>
        </div>

        <div style="background:#fff; border-radius:18px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:20px; transition:transform .2s,box-shadow .2s;" style-hover="transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,0.08);">
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:11px; background:#FFF3E0; display:flex; align-items:center; justify-content:center; color:#F59E0B;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10h18M16 14h2"/></svg>
            </div>
            <span style="font-size:12.5px; font-weight:600; color:#697386;">Monthly Collection</span>
          </div>
          <div style="font-size:28px; font-weight:800; letter-spacing:-1px; line-height:1;">BDT 24.75M</div>
          <div style="font-size:12px; margin-top:8px; display:flex; align-items:center; gap:5px;"><span style="color:#22C55E; font-weight:700;">▲ 22%</span><span style="color:#697386;">from last month</span></div>
        </div>

        <div style="background:#fff; border-radius:18px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:20px; transition:transform .2s,box-shadow .2s;" style-hover="transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,0.08);">
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:11px; background:#FDE8E8; display:flex; align-items:center; justify-content:center; color:#EF4444;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </div>
            <span style="font-size:12.5px; font-weight:600; color:#697386;">Pending Payments</span>
          </div>
          <div style="font-size:28px; font-weight:800; letter-spacing:-1px; line-height:1;">BDT 8.45M</div>
          <div style="font-size:12px; color:#697386; margin-top:8px;">Overdue: <b style="color:#EF4444;">BDT 2.15M</b></div>
        </div>

        <div style="background:#fff; border-radius:18px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:20px; transition:transform .2s,box-shadow .2s;" style-hover="transform:translateY(-3px); box-shadow:0 14px 32px rgba(0,0,0,0.08);">
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
            <div style="width:40px; height:40px; border-radius:11px; background:#E6F4FB; display:flex; align-items:center; justify-content:center; color:#0EA5E9;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H8l-4 4V5a2 2 0 012-2h13a2 2 0 012 2z"/></svg>
            </div>
            <span style="font-size:12.5px; font-weight:600; color:#697386;">Open Tickets</span>
          </div>
          <div style="font-size:32px; font-weight:800; letter-spacing:-1px; line-height:1;">32</div>
          <div style="font-size:12px; color:#697386; margin-top:8px;">Urgent: <b style="color:#F59E0B;">6</b></div>
        </div>
      </section>

      <!-- ANALYTICS ROW -->
      <section style="display:grid; grid-template-columns:2fr 1fr; gap:24px; animation:hvUp .5s ease .06s both;">
        <!-- combined donut + line -->
        <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); display:grid; grid-template-columns:1fr 1.25fr;">
          <!-- donut -->
          <div style="padding:24px; border-right:1px solid #F1F4F9; display:flex; flex-direction:column;">
            <div style="font-size:17px; font-weight:700;">Sales &amp; Inventory</div>
            <div style="display:flex; align-items:center; gap:18px; margin-top:18px; flex:1;">
              <div style="position:relative; width:170px; height:170px; flex-shrink:0;">
                <svg width="170" height="170" viewBox="0 0 180 180" style="transform:rotate(-90deg);">
                  <circle cx="90" cy="90" r="70" fill="none" stroke="#F1F4F9" stroke-width="22"/>
                  <circle cx="90" cy="90" r="70" fill="none" stroke="#22C55E" stroke-width="22" stroke-linecap="round" stroke-dasharray="224.3 215.5" stroke-dashoffset="0" style="--len:224.3; animation:hvDash 1s ease both;"/>
                  <circle cx="90" cy="90" r="70" fill="none" stroke="#F59E0B" stroke-width="22" stroke-linecap="round" stroke-dasharray="52.8 387" stroke-dashoffset="-228" style="--len:52.8; animation:hvDash 1s ease .15s both;"/>
                  <circle cx="90" cy="90" r="70" fill="none" stroke="#93C5FD" stroke-width="22" stroke-linecap="round" stroke-dasharray="162.7 277.1" stroke-dashoffset="-285" style="--len:162.7; animation:hvDash 1s ease .3s both;"/>
                </svg>
                <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                  <div style="font-size:26px; font-weight:800; letter-spacing:-0.5px;">1,248</div>
                  <div style="font-size:11px; color:#697386;">Total Units</div>
                </div>
              </div>
              <div style="display:flex; flex-direction:column; gap:14px;">
                <div><div style="display:flex; align-items:center; gap:7px; font-size:12px; color:#697386;"><span style="width:9px; height:9px; border-radius:3px; background:#22C55E;"></span>Sold</div><div style="font-size:15px; font-weight:700; margin-left:16px;">632 <span style="color:#697386; font-weight:500; font-size:12px;">(51%)</span></div></div>
                <div><div style="display:flex; align-items:center; gap:7px; font-size:12px; color:#697386;"><span style="width:9px; height:9px; border-radius:3px; background:#F59E0B;"></span>Reserved</div><div style="font-size:15px; font-weight:700; margin-left:16px;">148 <span style="color:#697386; font-weight:500; font-size:12px;">(12%)</span></div></div>
                <div><div style="display:flex; align-items:center; gap:7px; font-size:12px; color:#697386;"><span style="width:9px; height:9px; border-radius:3px; background:#93C5FD;"></span>Available</div><div style="font-size:15px; font-weight:700; margin-left:16px;">468 <span style="color:#697386; font-weight:500; font-size:12px;">(37%)</span></div></div>
              </div>
            </div>
            <a style="margin-top:14px; font-size:13px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" href="HomeVerse%20Analytics.dc.html">View Detailed Report →</a>
          </div>
          <!-- line -->
          <div style="padding:24px; display:flex; flex-direction:column;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
              <div style="font-size:17px; font-weight:700;">Payment Collection Trend</div>
              <div style="display:flex; align-items:center; gap:7px; font-size:12px; font-weight:600; color:#3A4256; border:1px solid #EEF2F8; border-radius:9px; padding:7px 11px; cursor:pointer;">This Year <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9AA3B4" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></div>
            </div>
            <div style="flex:1; margin-top:14px; position:relative;">
              <svg width="100%" viewBox="0 0 620 250" preserveAspectRatio="none" style="display:block;">
                <defs>
                  <linearGradient id="hvArea" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="var(--accent)" stop-opacity="0.18"/><stop offset="100%" stop-color="var(--accent)" stop-opacity="0"/></linearGradient>
                </defs>
                <g stroke="#F1F4F9" stroke-width="1">
                  <line x1="70" y1="30" x2="610" y2="30"/><line x1="70" y1="66" x2="610" y2="66"/><line x1="70" y1="102" x2="610" y2="102"/><line x1="70" y1="138" x2="610" y2="138"/><line x1="70" y1="174" x2="610" y2="174"/><line x1="70" y1="210" x2="610" y2="210"/>
                </g>
                <g fill="#A0A8B8" font-size="11" font-family="Inter" text-anchor="end">
                  <text x="58" y="34">BDT 30M</text><text x="58" y="70">BDT 24M</text><text x="58" y="106">BDT 18M</text><text x="58" y="142">BDT 12M</text><text x="58" y="178">BDT 6M</text><text x="58" y="214">BDT 0</text>
                </g>
                <polygon points="90,162 210,144 330,114 450,90 570,61.5 570,210 90,210" fill="url(#hvArea)"/>
                <polyline points="90,162 210,144 330,114 450,90 570,61.5" fill="none" stroke="var(--accent)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <g fill="#fff" stroke="var(--accent)" stroke-width="2.5">
                  <circle cx="90" cy="162" r="4.5"/><circle cx="210" cy="144" r="4.5"/><circle cx="330" cy="114" r="4.5"/><circle cx="450" cy="90" r="4.5"/>
                </g>
                <circle cx="570" cy="61.5" r="7" fill="var(--accent)" stroke="#fff" stroke-width="3"/>
                <g fill="#A0A8B8" font-size="11" font-family="Inter" text-anchor="middle">
                  <text x="90" y="232">Jan 2026</text><text x="210" y="232">Feb 2026</text><text x="330" y="232">Mar 2026</text><text x="450" y="232">Apr 2026</text><text x="570" y="232">May 2026</text>
                </g>
                <g><rect x="500" y="14" width="108" height="40" rx="9" fill="#151B2E"/><text x="554" y="31" fill="#9AA3B4" font-size="11" font-family="Inter" text-anchor="middle">May 2026</text><text x="554" y="46" fill="#fff" font-size="13" font-weight="700" font-family="Inter" text-anchor="middle">BDT 24.75M</text></g>
              </svg>
            </div>
            <a style="margin-top:6px; font-size:13px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" href="HomeVerse%20Payments.dc.html">View Financial Report →</a>
          </div>
        </div>

        <!-- progress -->
        <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:24px; display:flex; flex-direction:column;">
          <div style="font-size:17px; font-weight:700; margin-bottom:18px;">Project Progress</div>
          <div style="display:flex; flex-direction:column; gap:18px; flex:1;">
            <!-- row -->
            <div style="display:flex; align-items:center; gap:13px;">
              <div style="width:42px; height:42px; border-radius:10px; background:linear-gradient(135deg,#C7D2FE,#A5B4FC); flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4338CA" stroke-width="1.6"><path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/></svg></div>
              <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><span style="font-size:13.5px; font-weight:600;">Lake View Residence</span><span style="font-size:13px; font-weight:700;">78%</span></div>
                <div style="height:7px; background:#F1F4F9; border-radius:4px; overflow:hidden;"><div style="height:100%; width:78%; background:var(--accent); border-radius:4px; transform-origin:left; animation:hvGrow .9s ease both;"></div></div>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:13px;">
              <div style="width:42px; height:42px; border-radius:10px; background:linear-gradient(135deg,#BBF7D0,#86EFAC); flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803D" stroke-width="1.6"><path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/></svg></div>
              <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><span style="font-size:13.5px; font-weight:600;">Green Park Heights</span><span style="font-size:13px; font-weight:700;">65%</span></div>
                <div style="height:7px; background:#F1F4F9; border-radius:4px; overflow:hidden;"><div style="height:100%; width:65%; background:#22C55E; border-radius:4px; transform-origin:left; animation:hvGrow .9s ease .1s both;"></div></div>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:13px;">
              <div style="width:42px; height:42px; border-radius:10px; background:linear-gradient(135deg,#BFDBFE,#93C5FD); flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1D4ED8" stroke-width="1.6"><path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/></svg></div>
              <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><span style="font-size:13.5px; font-weight:600;">Skyline Towers</span><span style="font-size:13px; font-weight:700;">45%</span></div>
                <div style="height:7px; background:#F1F4F9; border-radius:4px; overflow:hidden;"><div style="height:100%; width:45%; background:#3B82F6; border-radius:4px; transform-origin:left; animation:hvGrow .9s ease .2s both;"></div></div>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:13px;">
              <div style="width:42px; height:42px; border-radius:10px; background:linear-gradient(135deg,#FED7AA,#FDBA74); flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C2410C" stroke-width="1.6"><path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/></svg></div>
              <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><span style="font-size:13.5px; font-weight:600;">City Central Mall</span><span style="font-size:13px; font-weight:700;">32%</span></div>
                <div style="height:7px; background:#F1F4F9; border-radius:4px; overflow:hidden;"><div style="height:100%; width:32%; background:#F59E0B; border-radius:4px; transform-origin:left; animation:hvGrow .9s ease .3s both;"></div></div>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:13px;">
              <div style="width:42px; height:42px; border-radius:10px; background:linear-gradient(135deg,#DDD6FE,#C4B5FD); flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6D28D9" stroke-width="1.6"><path d="M5 21V5a1 1 0 011-1h6a1 1 0 011 1v16M13 9h5a1 1 0 011 1v11M8 8h2M8 12h2M8 16h2"/></svg></div>
              <div style="flex:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><span style="font-size:13.5px; font-weight:600;">Metro Line 4 Extension</span><span style="font-size:13px; font-weight:700;">60%</span></div>
                <div style="height:7px; background:#F1F4F9; border-radius:4px; overflow:hidden;"><div style="height:100%; width:60%; background:var(--accent-2); border-radius:4px; transform-origin:left; animation:hvGrow .9s ease .4s both;"></div></div>
              </div>
            </div>
          </div>
          <a style="margin-top:16px; font-size:13px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer; align-self:center;" href="HomeVerse%20Projects.dc.html">View All Projects →</a>
        </div>
      </section>

      <!-- LOWER SECTION -->
      <section style="display:grid; grid-template-columns:2fr 1fr; gap:24px; animation:hvUp .5s ease .12s both;">
        <!-- LEFT COLUMN -->
        <div style="display:flex; flex-direction:column; gap:24px;">
          <!-- reservations -->
          <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
              <div style="font-size:18px; font-weight:700;">Recent Reservations</div>
              <div style="display:flex; align-items:center; gap:8px;">
                <button style="display:flex; align-items:center; gap:6px; background:#F8F9FC; border:1px solid #EEF2F8; border-radius:9px; padding:8px 12px; font-family:inherit; font-size:12.5px; font-weight:600; color:#3A4256; cursor:pointer;" style-hover="background:#F1F4F9;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg>Filter</button>
                <button style="display:flex; align-items:center; gap:6px; background:#F8F9FC; border:1px solid #EEF2F8; border-radius:9px; padding:8px 12px; font-family:inherit; font-size:12.5px; font-weight:600; color:#3A4256; cursor:pointer;" style-hover="background:#F1F4F9;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>Export</button>
                <a style="font-size:13px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer; margin-left:4px;" href="HomeVerse%20Sales.dc.html">View All →</a>
              </div>
            </div>
            <table style="width:100%; border-collapse:collapse; margin-top:8px;">
              <thead><tr style="text-align:left;">
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Buyer Name</th>
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Project</th>
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Unit</th>
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Type</th>
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Amount</th>
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Date</th>
                <th style="font-size:11.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.4px; padding:10px 8px; border-bottom:1px solid #F1F4F9;">Status</th>
              </tr></thead>
              <tbody>
                <tr style="transition:background .15s;" style-hover="background:#FAFBFE;">
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><div style="display:flex; align-items:center; gap:9px;"><span style="width:30px; height:30px; border-radius:50%; background:#EDE9FE; color:#6D28D9; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center;">RU</span><span style="font-size:13.5px; font-weight:600;">Rahim Uddin</span></div></td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">Lake View Residence</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">A-1205</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">2 Bed</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; font-weight:600;">BDT 12,500,000</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#697386;">15 May 2026</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><span style="font-size:11.5px; font-weight:700; color:#15803D; background:#E6F7EE; padding:5px 11px; border-radius:20px;">Reserved</span></td>
                </tr>
                <tr style="transition:background .15s;" style-hover="background:#FAFBFE;">
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><div style="display:flex; align-items:center; gap:9px;"><span style="width:30px; height:30px; border-radius:50%; background:#FCE7F3; color:#BE185D; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center;">NJ</span><span style="font-size:13.5px; font-weight:600;">Nusrat Jahan</span></div></td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">Green Park Heights</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">B-804</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">3 Bed</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; font-weight:600;">BDT 16,800,000</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#697386;">15 May 2026</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><span style="font-size:11.5px; font-weight:700; color:#15803D; background:#E6F7EE; padding:5px 11px; border-radius:20px;">Reserved</span></td>
                </tr>
                <tr style="transition:background .15s;" style-hover="background:#FAFBFE;">
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><div style="display:flex; align-items:center; gap:9px;"><span style="width:30px; height:30px; border-radius:50%; background:#DBEAFE; color:#1D4ED8; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center;">AI</span><span style="font-size:13.5px; font-weight:600;">Aminul Islam</span></div></td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">Skyline Towers</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">C-302</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">2 Bed</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; font-weight:600;">BDT 11,250,000</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#697386;">14 May 2026</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><span style="font-size:11.5px; font-weight:700; color:#B45309; background:#FFF3E0; padding:5px 11px; border-radius:20px;">On Hold</span></td>
                </tr>
                <tr style="transition:background .15s;" style-hover="background:#FAFBFE;">
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><div style="display:flex; align-items:center; gap:9px;"><span style="width:30px; height:30px; border-radius:50%; background:#D1FAE5; color:#047857; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center;">FA</span><span style="font-size:13.5px; font-weight:600;">Fatema Akter</span></div></td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">Lake View Residence</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">A-903</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#3A4256;">1 Bed</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; font-weight:600;">BDT 7,200,000</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB; font-size:13px; color:#697386;">14 May 2026</td>
                  <td style="padding:14px 8px; border-bottom:1px solid #F5F7FB;"><span style="font-size:11.5px; font-weight:700; color:#15803D; background:#E6F7EE; padding:5px 11px; border-radius:20px;">Reserved</span></td>
                </tr>
                <tr style="transition:background .15s;" style-hover="background:#FAFBFE;">
                  <td style="padding:14px 8px;"><div style="display:flex; align-items:center; gap:9px;"><span style="width:30px; height:30px; border-radius:50%; background:#FEF3C7; color:#B45309; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center;">SA</span><span style="font-size:13.5px; font-weight:600;">Sakib Ahmed</span></div></td>
                  <td style="padding:14px 8px; font-size:13px; color:#3A4256;">City Central Mall</td>
                  <td style="padding:14px 8px; font-size:13px; color:#3A4256;">CM-12</td>
                  <td style="padding:14px 8px; font-size:13px; color:#3A4256;">Commercial</td>
                  <td style="padding:14px 8px; font-size:13px; font-weight:600;">BDT 25,000,000</td>
                  <td style="padding:14px 8px; font-size:13px; color:#697386;">13 May 2026</td>
                  <td style="padding:14px 8px;"><span style="font-size:11.5px; font-weight:700; color:#15803D; background:#E6F7EE; padding:5px 11px; border-radius:20px;">Reserved</span></td>
                </tr>
              </tbody>
            </table>
            <div style="display:flex; align-items:center; justify-content:space-between; margin-top:16px; padding-top:14px; border-top:1px solid #F1F4F9;">
              <span style="font-size:12.5px; color:#697386;">Showing 5 of 248 reservations</span>
              <div style="display:flex; align-items:center; gap:6px;">
                <button style="width:32px; height:32px; border-radius:8px; border:1px solid #EEF2F8; background:#fff; color:#9AA3B4; cursor:pointer; font-size:14px;">‹</button>
                <button style="width:32px; height:32px; border-radius:8px; border:none; background:var(--accent); color:#fff; cursor:pointer; font-size:13px; font-weight:600;">1</button>
                <button style="width:32px; height:32px; border-radius:8px; border:1px solid #EEF2F8; background:#fff; color:#3A4256; cursor:pointer; font-size:13px; font-weight:600;">2</button>
                <button style="width:32px; height:32px; border-radius:8px; border:1px solid #EEF2F8; background:#fff; color:#3A4256; cursor:pointer; font-size:13px; font-weight:600;">3</button>
                <button style="width:32px; height:32px; border-radius:8px; border:1px solid #EEF2F8; background:#fff; color:#3A4256; cursor:pointer; font-size:14px;">›</button>
              </div>
            </div>
          </div>

          <!-- bottom: support tickets + top performing -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
            <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:24px;">
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;"><div style="font-size:16px; font-weight:700;">Support Tickets Overview</div><a style="font-size:12.5px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer;" href="HomeVerse%20Support%20Tickets.dc.html">View All →</a></div>
              <div style="display:flex; align-items:center; gap:14px; margin-top:14px;">
                <div style="position:relative; width:140px; height:140px; flex-shrink:0;">
                  <svg width="140" height="140" viewBox="0 0 150 150" style="transform:rotate(-90deg);">
                    <circle cx="75" cy="75" r="60" fill="none" stroke="#F1F4F9" stroke-width="18"/>
                    <circle cx="75" cy="75" r="60" fill="none" stroke="#3B82F6" stroke-width="18" stroke-dasharray="150.8 226.2" stroke-dashoffset="0" style="--len:150.8; animation:hvDash 1s ease both;"/>
                    <circle cx="75" cy="75" r="60" fill="none" stroke="#F59E0B" stroke-width="18" stroke-dasharray="82.9 294.1" stroke-dashoffset="-150.8" style="--len:82.9; animation:hvDash 1s ease .12s both;"/>
                    <circle cx="75" cy="75" r="60" fill="none" stroke="var(--accent)" stroke-width="18" stroke-dasharray="67.9 309.1" stroke-dashoffset="-233.7" style="--len:67.9; animation:hvDash 1s ease .24s both;"/>
                    <circle cx="75" cy="75" r="60" fill="none" stroke="#22C55E" stroke-width="18" stroke-dasharray="75.4 301.6" stroke-dashoffset="-301.6" style="--len:75.4; animation:hvDash 1s ease .36s both;"/>
                  </svg>
                  <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;"><div style="font-size:24px; font-weight:800;">32</div><div style="font-size:10px; color:#697386;">Total Tickets</div></div>
                </div>
                <div style="flex:1; display:flex; flex-direction:column; gap:11px;">
                  <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;"><span style="display:flex; align-items:center; gap:7px; color:#697386;"><span style="width:8px; height:8px; border-radius:50%; background:#3B82F6;"></span>Open</span><span style="font-weight:700;">32 <span style="color:#9AA3B4; font-weight:500;">(40%)</span></span></div>
                  <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;"><span style="display:flex; align-items:center; gap:7px; color:#697386;"><span style="width:8px; height:8px; border-radius:50%; background:#F59E0B;"></span>In Progress</span><span style="font-weight:700;">18 <span style="color:#9AA3B4; font-weight:500;">(22%)</span></span></div>
                  <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;"><span style="display:flex; align-items:center; gap:7px; color:#697386;"><span style="width:8px; height:8px; border-radius:50%; background:var(--accent);"></span>Waiting for Buyer</span><span style="font-weight:700;">15 <span style="color:#9AA3B4; font-weight:500;">(18%)</span></span></div>
                  <div style="display:flex; align-items:center; justify-content:space-between; font-size:12.5px;"><span style="display:flex; align-items:center; gap:7px; color:#697386;"><span style="width:8px; height:8px; border-radius:50%; background:#22C55E;"></span>Resolved</span><span style="font-weight:700;">25 <span style="color:#9AA3B4; font-weight:500;">(20%)</span></span></div>
                </div>
              </div>
            </div>

            <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:24px;">
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;"><div style="font-size:16px; font-weight:700;">Top Performing Projects</div><a style="font-size:12.5px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer;" href="Project%20Performance%20Ranking.dc.html">Full Report →</a></div>
              <table style="width:100%; border-collapse:collapse;">
                <thead><tr style="text-align:left;">
                  <th style="font-size:10.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.3px; padding:7px 4px; border-bottom:1px solid #F1F4F9;">Project</th>
                  <th style="font-size:10.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.3px; padding:7px 4px; border-bottom:1px solid #F1F4F9; text-align:right;">Views</th>
                  <th style="font-size:10.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.3px; padding:7px 4px; border-bottom:1px solid #F1F4F9; text-align:right;">Leads</th>
                  <th style="font-size:10.5px; font-weight:600; color:#9AA3B4; text-transform:uppercase; letter-spacing:0.3px; padding:7px 4px; border-bottom:1px solid #F1F4F9; text-align:right;">Conv.</th>
                </tr></thead>
                <tbody>
                  <tr><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; font-weight:600;">Lake View Residence</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">12,458</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">1,245</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; text-align:right;"><span style="font-size:12px; font-weight:700; color:#15803D;">17.3%</span></td></tr>
                  <tr><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; font-weight:600;">Green Park Heights</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">9,876</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">987</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; text-align:right;"><span style="font-size:12px; font-weight:700; color:#15803D;">17.0%</span></td></tr>
                  <tr><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; font-weight:600;">Skyline Towers</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">8,654</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">865</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; text-align:right;"><span style="font-size:12px; font-weight:700; color:#15803D;">15.6%</span></td></tr>
                  <tr><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; font-weight:600;">Metro Line 4 Ext.</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">7,542</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; font-size:12.5px; color:#3A4256; text-align:right;">654</td><td style="padding:11px 4px; border-bottom:1px solid #F5F7FB; text-align:right;"><span style="font-size:12px; font-weight:700; color:#15803D;">15.0%</span></td></tr>
                  <tr><td style="padding:11px 4px; font-size:12.5px; font-weight:600;">City Central Mall</td><td style="padding:11px 4px; font-size:12.5px; color:#3A4256; text-align:right;">6,214</td><td style="padding:11px 4px; font-size:12.5px; color:#3A4256; text-align:right;">512</td><td style="padding:11px 4px; text-align:right;"><span style="font-size:12px; font-weight:700; color:#15803D;">14.1%</span></td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div style="display:flex; flex-direction:column; gap:24px;">
          <sc-if value="{{ showQuickActions }}" hint-placeholder-val="{{ true }}">
          <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:24px;">
            <div style="font-size:17px; font-weight:700; margin-bottom:16px;">Quick Actions</div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px;">
              <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 8px; border:1px solid #EEF2F8; border-radius:14px; cursor:pointer; transition:all .18s;" onClick="{{ goProjects }}" style-hover="border-color:var(--accent); background:var(--accent-soft); transform:translateY(-2px);">
                <div style="width:40px; height:40px; border-radius:11px; background:var(--accent-soft); display:flex; align-items:center; justify-content:center; color:var(--accent);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h12V5a1 1 0 00-1-1H4a1 1 0 00-1 1zM7 8h2M7 12h2M19 21V10l-4-2M19 13h0M19 17h0"/></svg></div>
                <span style="font-size:11.5px; font-weight:600; text-align:center; color:#3A4256;">Add New Project</span>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 8px; border:1px solid #EEF2F8; border-radius:14px; cursor:pointer; transition:all .18s;" onClick="{{ goInventory }}" style-hover="border-color:#3B82F6; background:#EFF6FF; transform:translateY(-2px);">
                <div style="width:40px; height:40px; border-radius:11px; background:#E8F0FF; display:flex; align-items:center; justify-content:center; color:#3B82F6;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-7 9 7M5 10v9h14v-9M12 14v5"/></svg></div>
                <span style="font-size:11.5px; font-weight:600; text-align:center; color:#3A4256;">Add New Unit</span>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 8px; border:1px solid #EEF2F8; border-radius:14px; cursor:pointer; transition:all .18s;" onClick="{{ goCRM }}" style-hover="border-color:#22C55E; background:#F0FDF4; transform:translateY(-2px);">
                <div style="width:40px; height:40px; border-radius:11px; background:#E6F7EE; display:flex; align-items:center; justify-content:center; color:#22C55E;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M3 20v-1a5 5 0 015-5h2a5 5 0 015 5v1M18 8v6M21 11h-6"/></svg></div>
                <span style="font-size:11.5px; font-weight:600; text-align:center; color:#3A4256;">Add New Buyer</span>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 8px; border:1px solid #EEF2F8; border-radius:14px; cursor:pointer; transition:all .18s;" onClick="{{ goPayments }}" style-hover="border-color:#F59E0B; background:#FFFBEB; transform:translateY(-2px);">
                <div style="width:40px; height:40px; border-radius:11px; background:#FFF3E0; display:flex; align-items:center; justify-content:center; color:#F59E0B;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/></svg></div>
                <span style="font-size:11.5px; font-weight:600; text-align:center; color:#3A4256;">Create Payment Plan</span>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 8px; border:1px solid #EEF2F8; border-radius:14px; cursor:pointer; transition:all .18s;" onClick="{{ goDocuments }}" style-hover="border-color:#EF4444; background:#FEF2F2; transform:translateY(-2px);">
                <div style="width:40px; height:40px; border-radius:11px; background:#FDE8E8; display:flex; align-items:center; justify-content:center; color:#EF4444;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5z"/><path d="M14 3v5h5M12 18v-6M9 15h6"/></svg></div>
                <span style="font-size:11.5px; font-weight:600; text-align:center; color:#3A4256;">Upload Document</span>
              </div>
              <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 8px; border:1px solid #EEF2F8; border-radius:14px; cursor:pointer; transition:all .18s;" onClick="{{ goNotify }}" style-hover="border-color:#0EA5E9; background:#F0F9FF; transform:translateY(-2px);">
                <div style="width:40px; height:40px; border-radius:11px; background:#E6F4FB; display:flex; align-items:center; justify-content:center; color:#0EA5E9;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4z"/></svg></div>
                <span style="font-size:11.5px; font-weight:600; text-align:center; color:#3A4256;">Send Notification</span>
              </div>
            </div>
          </div>
          </sc-if>

          <sc-if value="{{ showAnnouncements }}" hint-placeholder-val="{{ true }}">
          <div style="background:#fff; border-radius:20px; border:1px solid #EEF2F8; box-shadow:0 8px 24px rgba(0,0,0,0.04); padding:24px; flex:1;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;"><div style="font-size:17px; font-weight:700;">System Announcements</div><a style="font-size:12.5px; font-weight:600; color:var(--accent); text-decoration:none; cursor:pointer;">View All</a></div>
            <div style="display:flex; flex-direction:column; gap:6px;">
              <div style="display:flex; gap:12px; padding:13px; border-radius:13px; cursor:pointer; transition:background .15s;" style-hover="background:#FAFBFE;">
                <div style="width:38px; height:38px; border-radius:11px; background:var(--accent-soft); display:flex; align-items:center; justify-content:center; color:var(--accent); flex-shrink:0;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 16a8 8 0 0116 0M2 16h20M9 9V6a3 3 0 016 0v3"/></svg></div>
                <div><div style="font-size:13.5px; font-weight:700;">New Construction Update</div><div style="font-size:12px; color:#697386; margin-top:2px; line-height:1.4;">Lake View Residence progress updated to 78%.</div><div style="font-size:11px; color:#9AA3B4; margin-top:5px;">2 hours ago</div></div>
              </div>
              <div style="display:flex; gap:12px; padding:13px; border-radius:13px; cursor:pointer; transition:background .15s;" style-hover="background:#FAFBFE;">
                <div style="width:38px; height:38px; border-radius:11px; background:#FFF3E0; display:flex; align-items:center; justify-content:center; color:#F59E0B; flex-shrink:0;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
                <div><div style="font-size:13.5px; font-weight:700;">Payment Reminder</div><div style="font-size:12px; color:#697386; margin-top:2px; line-height:1.4;">15 payments are due in the next 7 days.</div><div style="font-size:11px; color:#9AA3B4; margin-top:5px;">5 hours ago</div></div>
              </div>
              <div style="display:flex; gap:12px; padding:13px; border-radius:13px; cursor:pointer; transition:background .15s;" style-hover="background:#FAFBFE;">
                <div style="width:38px; height:38px; border-radius:11px; background:#E8F0FF; display:flex; align-items:center; justify-content:center; color:#3B82F6; flex-shrink:0;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M5 5l2 2M17 17l2 2M2 12h3M19 12h3M5 19l2-2M17 7l2-2"/></svg></div>
                <div><div style="font-size:13.5px; font-weight:700;">System Maintenance</div><div style="font-size:12px; color:#697386; margin-top:2px; line-height:1.4;">Scheduled maintenance on 20 May 2026 from 02:00 AM - 04:00 AM.</div><div style="font-size:11px; color:#9AA3B4; margin-top:5px;">1 day ago</div></div>
              </div>
            </div>
          </div>
          </sc-if>
        </div>
      </section>

      <!-- FOOTER -->
      <footer style="display:flex; align-items:center; justify-content:space-between; padding:18px 4px 28px; font-size:12.5px; color:#9AA3B4;">
        <div>© 2026 HomeVerse Admin &nbsp;·&nbsp; Developed &amp; Prototyped by <span style="color:#3A4256; font-weight:600;">Future Studios BD</span></div>
        <div style="display:flex; align-items:center; gap:16px;">
          <span>Version 1.0</span>
          <span style="display:flex; align-items:center; gap:6px;"><span style="width:7px; height:7px; border-radius:50%; background:#22C55E;"></span>System Online</span>
          <span>Last Sync: 2 min ago</span>
        </div>
      </footer>
    </div>
  </main>
</div>
</x-dc>
<script type="text/x-dc" data-dc-script data-props="{
  &quot;$preview&quot;: { &quot;width&quot;: 1440, &quot;height&quot;: 1180 },
  &quot;accentColor&quot;: { &quot;editor&quot;: &quot;color&quot;, &quot;default&quot;: &quot;#5B3DF5&quot;, &quot;tsType&quot;: &quot;string&quot;, &quot;options&quot;: [&quot;#5B3DF5&quot;, &quot;#2563EB&quot;, &quot;#0EA5E9&quot;, &quot;#059669&quot;, &quot;#E11D48&quot;] },
  &quot;showQuickActions&quot;: { &quot;editor&quot;: &quot;boolean&quot;, &quot;default&quot;: true, &quot;tsType&quot;: &quot;boolean&quot; },
  &quot;showAnnouncements&quot;: { &quot;editor&quot;: &quot;boolean&quot;, &quot;default&quot;: true, &quot;tsType&quot;: &quot;boolean&quot; }
}">
class Component extends DCLogic {
  componentDidMount(){
    const a = this.props.accentColor;
    if (a) {
      document.documentElement.style.setProperty('--accent', a);
      document.documentElement.style.setProperty('--accent-2', a);
    }
  }
  renderVals(){
    const go=(u)=>()=>{ window.location.href=u; };
    return {
      showQuickActions: this.props.showQuickActions ?? true,
      showAnnouncements: this.props.showAnnouncements ?? true,
      goProjects: go('Add%20New%20Project.dc.html'),
      goInventory: go('Add%20New%20Unit.dc.html'),
      goCRM: go('Add%20New%20Buyer.dc.html'),
      goPayments: go('Create%20Payment%20Plan.dc.html'),
      goDocuments: go('HomeVerse%20Documents.dc.html'),
      goNotify: go('HomeVerse%20Support%20Tickets.dc.html'),
    };
  }
}
</script>
</body>
</html>
