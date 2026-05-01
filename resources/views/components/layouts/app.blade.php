<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTL — Télécompensation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        :root {
            --bg-base:#0a0d14;--bg-surface:#0f1420;--bg-card:#141928;
            --bg-input:#0f1420;--border:rgba(255,255,255,0.07);
            --border-active:rgba(255,255,255,0.15);
            --gold:#c9a84c;--gold-light:#e8c97a;
            --gold-dim:rgba(201,168,76,0.15);--gold-glow:rgba(201,168,76,0.08);
            --blue-acc:#3b82f6;--blue-dim:rgba(59,130,246,0.12);
            --green:#22c55e;--green-dim:rgba(34,197,94,0.12);
            --red:#ef4444;--red-dim:rgba(239,68,68,0.12);
            --yellow:#f59e0b;--yellow-dim:rgba(245,158,11,0.12);
            --text-primary:#f0f4ff;--text-secondary:#8892a4;--text-muted:#4a5568;
            --sidebar-w:260px;--header-h:64px;
            --radius:12px;--radius-sm:8px;--radius-lg:16px;
            --font-display:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;
            --shadow-card:0 4px 24px rgba(0,0,0,0.4);
        }
        body.light-mode {
            --bg-base:#f0f2f8;--bg-surface:#ffffff;--bg-card:#ffffff;
            --bg-input:#f4f6fb;--border:rgba(0,0,0,0.08);
            --border-active:rgba(0,0,0,0.2);
            --text-primary:#1a1f2e;--text-secondary:#4a5568;--text-muted:#94a3b8;
            --shadow-card:0 4px 24px rgba(0,0,0,0.08);
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;background:var(--bg-base);color:var(--text-primary);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased;transition:background 0.3s,color 0.3s}
        .sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);background:var(--bg-surface);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;overflow:hidden;transition:background 0.3s,border-color 0.3s}
        .sidebar::before{content:'';position:absolute;top:0;left:0;right:0;height:1px;background:linear-gradient(90deg,transparent,var(--gold),transparent)}
        .sidebar-brand{padding:22px 20px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
        .brand-logo{width:40px;height:40px;background:linear-gradient(135deg,var(--gold),var(--gold-light));border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:800;font-size:15px;color:#0a0d14;flex-shrink:0}
        .brand-name{font-family:var(--font-display);font-weight:700;font-size:16px;color:var(--text-primary)}
        .brand-sub{font-size:11px;color:var(--gold);font-weight:500;letter-spacing:0.8px;text-transform:uppercase}
        .sidebar-nav{flex:1;overflow-y:auto;padding:16px 12px;scrollbar-width:none}
        .nav-section-label{font-size:10px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:var(--text-muted);padding:16px 8px 6px}
        .nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--radius-sm);color:var(--text-secondary);text-decoration:none;font-size:13.5px;transition:all 0.18s;margin-bottom:1px;position:relative}
        .nav-item:hover{background:rgba(255,255,255,0.04);color:var(--text-primary)}
        .nav-item.active{background:var(--gold-dim);color:var(--gold-light);font-weight:500}
        .nav-item.active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:2.5px;background:var(--gold);border-radius:0 2px 2px 0}
        .nav-icon{width:18px;height:18px;flex-shrink:0;opacity:0.7}
        .nav-item.active .nav-icon{opacity:1}
        .sidebar-footer{padding:14px 12px;border-top:1px solid var(--border)}
        .user-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);cursor:pointer}
        .user-avatar{width:34px;height:34px;border-radius:50%;background:var(--gold-dim);border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:13px;color:var(--gold-light);flex-shrink:0}
        .user-name{font-size:13px;font-weight:500;color:var(--text-primary)}
        .user-role{font-size:11px;color:var(--text-muted)}
        .logout-btn{display:flex;align-items:center;gap:8px;padding:8px 12px;color:var(--text-muted);font-size:12.5px;text-decoration:none;border-radius:var(--radius-sm);transition:all 0.15s;margin-top:4px}
        .logout-btn:hover{color:var(--red);background:var(--red-dim)}
        .header{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--header-h);background:rgba(10,13,20,0.85);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;z-index:90;transition:background 0.3s}
        body.light-mode .header{background:rgba(240,242,248,0.9)}
        body.light-mode .sidebar{background:#fff}
        .header-breadcrumb{font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px}
        .header-right{display:flex;align-items:center;gap:12px}
        .header-date{font-size:12px;color:var(--text-muted)}
        .system-badge{display:flex;align-items:center;gap:6px;padding:5px 10px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;font-size:11.5px;color:var(--text-secondary)}
        .system-dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:0.4}}
        .theme-btn{width:36px;height:36px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:all 0.15s}
        .theme-btn:hover{border-color:var(--gold)}
        .notif-btn{position:relative;width:36px;height:36px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-secondary)}
        .notif-dot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--gold);border-radius:50%;border:1.5px solid var(--bg-surface)}
        .main{margin-left:var(--sidebar-w);margin-top:var(--header-h);min-height:calc(100vh - var(--header-h));padding:28px}
        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;transition:background 0.3s,border-color 0.3s}
        .stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;transition:all 0.2s;position:relative;overflow:hidden}
        .stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:2px}
        .stat-card.gold::after{background:linear-gradient(90deg,var(--gold),transparent)}
        .stat-card.green::after{background:linear-gradient(90deg,var(--green),transparent)}
        .stat-card.red::after{background:linear-gradient(90deg,var(--red),transparent)}
        .stat-card.blue::after{background:linear-gradient(90deg,var(--blue-acc),transparent)}
        .stat-label{font-size:11px;font-weight:600;letter-spacing:0.8px;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px}
        .stat-value{font-family:var(--font-display);font-size:32px;font-weight:700;line-height:1;color:var(--text-primary)}
        .stat-value.gold{color:var(--gold-light)}.stat-value.green{color:var(--green)}.stat-value.red{color:var(--red)}.stat-value.blue{color:var(--blue-acc)}
        .stat-sub{font-size:12px;color:var(--text-muted);margin-top:6px}
        .table-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:background 0.3s}
        .table-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px}
        .table-title{font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary)}
        table{width:100%;border-collapse:collapse}
        thead tr{border-bottom:1px solid var(--border)}
        th{padding:10px 20px;text-align:left;font-size:10.5px;font-weight:600;letter-spacing:0.9px;text-transform:uppercase;color:var(--text-muted)}
        td{padding:13px 20px;font-size:13px;color:var(--text-secondary);border-bottom:1px solid rgba(255,255,255,0.03)}
        tbody tr:hover td{background:rgba(255,255,255,0.02);color:var(--text-primary)}
        tbody tr:last-child td{border-bottom:none}
        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
        .badge::before{content:'';width:5px;height:5px;border-radius:50%}
        .badge-success{background:var(--green-dim);color:var(--green)}.badge-success::before{background:var(--green)}
        .badge-danger{background:var(--red-dim);color:var(--red)}.badge-danger::before{background:var(--red)}
        .badge-warning{background:var(--yellow-dim);color:var(--yellow)}.badge-warning::before{background:var(--yellow)}
        .badge-gold{background:var(--gold-dim);color:var(--gold-light)}.badge-gold::before{background:var(--gold)}
        .badge-blue{background:var(--blue-dim);color:var(--blue-acc)}.badge-blue::before{background:var(--blue-acc)}
        .badge-muted{background:rgba(255,255,255,0.05);color:var(--text-muted)}.badge-muted::before{background:var(--text-muted)}
        .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;border:none;transition:all 0.15s;text-decoration:none;font-family:var(--font-body)}
        .btn-primary{background:linear-gradient(135deg,var(--gold),var(--gold-light));color:#0a0d14;font-weight:600}
        .btn-primary:hover{box-shadow:0 4px 20px rgba(201,168,76,0.4);transform:translateY(-1px)}
        .btn-secondary{background:var(--bg-card);color:var(--text-secondary);border:1px solid var(--border)}
        .btn-secondary:hover{border-color:var(--border-active);color:var(--text-primary)}
        .btn-danger{background:var(--red-dim);color:var(--red);border:1px solid rgba(239,68,68,0.2)}
        .btn-sm{padding:6px 12px;font-size:12px}
        .btn-ghost{background:transparent;color:var(--text-muted);border:none;padding:6px 10px}
        .btn-ghost:hover{color:var(--text-primary);background:rgba(255,255,255,0.05)}
        .input{background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-sm);padding:9px 14px;font-size:13px;color:var(--text-primary);font-family:var(--font-body);outline:none;transition:border-color 0.15s;width:100%}
        .input:focus{border-color:var(--gold)}
        .input::placeholder{color:var(--text-muted)}
        select.input{cursor:pointer}
        .input-wrap{position:relative;display:flex;align-items:center}
        .input-icon{position:absolute;left:12px;color:var(--text-muted);pointer-events:none}
        .input-wrap .input{padding-left:36px}
        .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
        .flex{display:flex}.flex-1{flex:1}.gap-3{gap:12px}.gap-4{gap:16px}
        .items-center{align-items:center}.justify-between{justify-content:space-between}
        .mb-6{margin-bottom:24px}.mb-5{margin-bottom:20px}.mb-4{margin-bottom:16px}.mb-3{margin-bottom:12px}
        .text-muted{color:var(--text-muted)}.text-gold{color:var(--gold-light)}.text-green{color:var(--green)}.text-red{color:var(--red)}
        .font-mono{font-family:monospace;font-size:12px}.text-sm{font-size:12px}.text-xs{font-size:11px}
        .empty-state{text-align:center;padding:60px 20px;color:var(--text-muted)}
        .empty-title{font-size:15px;font-weight:500;color:var(--text-secondary);margin-bottom:6px}
        ::-webkit-scrollbar{width:5px;height:5px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.1);border-radius:10px}
        body.light-mode ::-webkit-scrollbar-thumb{background:rgba(0,0,0,0.15)}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">BTL</div>
        <div>
            <div class="brand-name">Télécompensation</div>
            <div class="brand-sub">Système SIBTEL</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Tableau de bord
        </a>
        <div class="nav-section-label">Fichiers</div>
        <a href="{{ route('fichiers.upload') }}" class="nav-item {{ request()->routeIs('fichiers.upload') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Importer un fichier
        </a>
        <a href="{{ route('fichiers.index') }}" class="nav-item {{ request()->routeIs('fichiers.index') || request()->routeIs('fichiers.show') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Fichiers traités
        </a>
        <div class="nav-section-label">Rejets & Retours</div>
        <a href="{{ route('rejets.index') }}" class="nav-item {{ request()->routeIs('rejets.index') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Gestion des rejets
        </a>
        <a href="{{ route('rejets.pacs004') }}" class="nav-item {{ request()->routeIs('rejets.pacs004') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
            Retours pacs.004
        </a>
        <div class="nav-section-label">Analyse</div>
        <a href="{{ route('stats.index') }}" class="nav-item {{ request()->routeIs('stats.index') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Statistiques
        </a>
        <div class="nav-section-label">Administration</div>
        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Utilisateurs
        </a>
        <a href="{{ route('audit.index') }}" class="nav-item {{ request()->routeIs('audit.index') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Journal d'audit
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ substr(auth()->user()->name ?? 'A', 0, 2) }}</div>
            <div>
                <div class="user-name">{{ auth()->user()->name ?? 'Administrateur' }}</div>
                <div class="user-role">{{ auth()->user()->role ?? 'Admin' }}</div>
            </div>
        </div>
        <a href="{{ route('logout') }}" class="logout-btn"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Se déconnecter
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
    </div>
</aside>
<header class="header">
    <div class="header-breadcrumb">
        <span>BTL</span>
        <span style="opacity:0.4">/</span>
        <span style="color:var(--text-secondary)">Télécompensation</span>
    </div>
    <div class="header-right">
        <div class="system-badge">
            <span class="system-dot"></span>
            Système opérationnel
        </div>
        <div class="header-date" id="hdate"></div>
        <button class="theme-btn" onclick="toggleTheme()" id="theme-btn" title="Mode clair/sombre">☀️</button>
        <div class="notif-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="notif-dot"></span>
        </div>
    </div>
</header>
<main class="main">
    {{ $slot }}
</main>
@livewireScripts
<script>
function toggleTheme(){
    const b=document.body;
    const isLight=b.classList.contains('light-mode');
    b.classList.toggle('light-mode',!isLight);
    document.getElementById('theme-btn').textContent=isLight?'☀️':'🌙';
    localStorage.setItem('btl-theme',isLight?'dark':'light');
}
(function(){
    const s=localStorage.getItem('btl-theme');
    if(s==='light'){
        document.body.classList.add('light-mode');
        document.addEventListener('DOMContentLoaded',function(){
            const b=document.getElementById('theme-btn');
            if(b)b.textContent='🌙';
        });
    }
})();
function updateTime(){
    const n=new Date();
    const el=document.getElementById('hdate');
    if(el)el.textContent=n.toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'})+' '+n.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
}
updateTime();setInterval(updateTime,30000);
</script>
</body>
</html>