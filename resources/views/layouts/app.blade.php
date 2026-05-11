<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTL — Télécompensation</title>
    <link rel="icon" type="image/png" href="{{ asset('img/btl-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        :root {
            /* ═══ COULEURS OFFICIELLES BTL ═══ */
            --btl-green:#0F4A36;
            --btl-green-light:#1A6B4D;
            --btl-green-pale:#4CAF7F;
            --btl-red:#D8232A;
            --btl-red-light:#E84850;
            --btl-red-dark:#A01820;

            /* Backgrounds */
            --bg-base:#0a0d14;--bg-surface:#0f1420;--bg-card:#141928;
            --bg-input:#0f1420;--border:rgba(255,255,255,0.07);
            --border-active:rgba(255,255,255,0.15);

            /* Aliases BTL (compatibilité) */
            --gold:var(--btl-green);
            --gold-light:var(--btl-green-pale);
            --gold-dim:rgba(15,74,54,0.18);
            --gold-glow:rgba(15,74,54,0.08);

            /* Couleurs sémantiques */
            --blue-acc:#3b82f6;--blue-dim:rgba(59,130,246,0.12);
            --green:#22c55e;--green-dim:rgba(34,197,94,0.12);
            --red:var(--btl-red);--red-dim:rgba(216,35,42,0.12);
            --yellow:#f59e0b;--yellow-dim:rgba(245,158,11,0.12);

            /* Textes */
            --text-primary:#f0f4ff;--text-secondary:#8892a4;--text-muted:#4a5568;

            /* Layout */
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
            --gold-dim:rgba(15,74,54,0.08);
        }
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;background:var(--bg-base);color:var(--text-primary);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased;transition:background 0.3s,color 0.3s}

        /* ═══ SIDEBAR — Aux couleurs BTL ═══ */
        .sidebar{position:fixed;top:0;left:0;bottom:0;width:var(--sidebar-w);background:linear-gradient(180deg,var(--btl-green) 0%,#0a3525 60%,#0f1420 100%);border-right:1px solid rgba(216,35,42,0.2);display:flex;flex-direction:column;z-index:100;overflow:hidden;transition:background 0.3s,border-color 0.3s}
        .sidebar::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--btl-red),var(--btl-green-pale),var(--btl-red));opacity:0.8}

        /* Logo BTL en haut */
        .sidebar-brand{padding:22px 20px 20px;border-bottom:1px solid rgba(255,255,255,0.12);display:flex;align-items:center;gap:12px;background:rgba(0,0,0,0.15)}
        .brand-logo-img{width:46px;height:46px;background:white;border-radius:10px;padding:5px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(0,0,0,0.3)}
        .brand-logo-img img{max-width:100%;max-height:100%;object-fit:contain}
        .brand-name{font-family:var(--font-display);font-weight:700;font-size:16px;color:white;letter-spacing:0.3px}
        .brand-sub{font-size:10px;color:rgba(255,255,255,0.7);font-weight:500;letter-spacing:1.2px;text-transform:uppercase;margin-top:2px}

        .sidebar-nav{flex:1;overflow-y:auto;padding:16px 12px;scrollbar-width:none}
        .nav-section-label{font-size:10px;font-weight:600;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,0.4);padding:16px 8px 6px}
        .nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:var(--radius-sm);color:rgba(255,255,255,0.75);text-decoration:none;font-size:13.5px;transition:all 0.18s;margin-bottom:1px;position:relative}
        .nav-item:hover{background:rgba(255,255,255,0.08);color:white}
        .nav-item.active{background:rgba(255,255,255,0.12);color:white;font-weight:600}
        .nav-item.active::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:3px;background:var(--btl-red);border-radius:0 3px 3px 0;box-shadow:0 0 8px var(--btl-red)}
        .nav-icon{width:18px;height:18px;flex-shrink:0;opacity:0.85}
        .nav-item.active .nav-icon{opacity:1}

        .sidebar-footer{padding:14px 12px;border-top:1px solid rgba(255,255,255,0.1);background:rgba(0,0,0,0.2)}
        .user-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:var(--radius-sm);cursor:pointer}
        .user-avatar{width:34px;height:34px;border-radius:50%;background:rgba(216,35,42,0.2);border:1.5px solid var(--btl-red);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-weight:700;font-size:13px;color:white;flex-shrink:0}
        .user-name{font-size:13px;font-weight:500;color:white}
        .user-role{font-size:11px;color:rgba(255,255,255,0.6)}
        .logout-btn{display:flex;align-items:center;gap:8px;padding:8px 12px;color:rgba(255,255,255,0.6);font-size:12.5px;text-decoration:none;border-radius:var(--radius-sm);transition:all 0.15s;margin-top:4px}
        .logout-btn:hover{color:white;background:var(--btl-red)}

        /* ═══ HEADER ═══ */
        .header{position:fixed;top:0;left:var(--sidebar-w);right:0;height:var(--header-h);background:rgba(10,13,20,0.85);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 28px;z-index:90;transition:background 0.3s}
        body.light-mode .header{background:rgba(240,242,248,0.9)}
        .header-breadcrumb{font-size:12px;color:var(--text-muted);display:flex;align-items:center;gap:6px;font-weight:500}
        .header-breadcrumb strong{color:var(--btl-green-pale);font-weight:600}
        .header-right{display:flex;align-items:center;gap:12px}
        .header-date{font-size:12px;color:var(--text-muted)}
        .system-badge{display:flex;align-items:center;gap:6px;padding:5px 12px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;font-size:11.5px;color:var(--text-secondary);font-weight:500}
        .system-dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulse 2s infinite;box-shadow:0 0 8px var(--green)}
        @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.5;transform:scale(0.9)}}
        .theme-btn{width:36px;height:36px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:all 0.15s}
        .theme-btn:hover{border-color:var(--btl-green-pale);transform:translateY(-1px)}
        .notif-btn{position:relative;width:36px;height:36px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-secondary)}
        .notif-dot{position:absolute;top:7px;right:7px;width:7px;height:7px;background:var(--btl-red);border-radius:50%;border:1.5px solid var(--bg-surface)}

        .main{margin-left:var(--sidebar-w);margin-top:var(--header-h);min-height:calc(100vh - var(--header-h));padding:28px}

        /* ═══ CARDS ═══ */
        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;transition:background 0.3s,border-color 0.3s}
        .stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;transition:all 0.2s;position:relative;overflow:hidden}
        .stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:2px}
        .stat-card.gold::after{background:linear-gradient(90deg,var(--btl-green-pale),transparent)}
        .stat-card.green::after{background:linear-gradient(90deg,var(--green),transparent)}
        .stat-card.red::after{background:linear-gradient(90deg,var(--btl-red),transparent)}
        .stat-card.blue::after{background:linear-gradient(90deg,var(--blue-acc),transparent)}
        .stat-label{font-size:11px;font-weight:600;letter-spacing:0.8px;text-transform:uppercase;color:var(--text-muted);margin-bottom:12px}
        .stat-value{font-family:var(--font-display);font-size:32px;font-weight:700;line-height:1;color:var(--text-primary)}
        .stat-value.gold{color:var(--btl-green-pale)}
        .stat-value.green{color:var(--green)}
        .stat-value.red{color:var(--btl-red)}
        .stat-value.blue{color:var(--blue-acc)}
        .stat-sub{font-size:12px;color:var(--text-muted);margin-top:6px}

        /* ═══ TABLES ═══ */
        .table-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:background 0.3s}
        .table-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px}
        .table-title{font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary)}
        table{width:100%;border-collapse:collapse}
        thead tr{border-bottom:1px solid var(--border)}
        th{padding:10px 20px;text-align:left;font-size:10.5px;font-weight:600;letter-spacing:0.9px;text-transform:uppercase;color:var(--text-muted)}
        td{padding:13px 20px;font-size:13px;color:var(--text-secondary);border-bottom:1px solid rgba(255,255,255,0.03)}
        tbody tr:hover td{background:rgba(255,255,255,0.02);color:var(--text-primary)}
        tbody tr:last-child td{border-bottom:none}

        /* ═══ BADGES ═══ */
        .badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}
        .badge::before{content:'';width:5px;height:5px;border-radius:50%}
        .badge-success{background:var(--green-dim);color:var(--green)}.badge-success::before{background:var(--green)}
        .badge-danger{background:var(--red-dim);color:var(--btl-red)}.badge-danger::before{background:var(--btl-red)}
        .badge-warning{background:var(--yellow-dim);color:var(--yellow)}.badge-warning::before{background:var(--yellow)}
        .badge-gold{background:var(--gold-dim);color:var(--btl-green-pale)}.badge-gold::before{background:var(--btl-green)}
        .badge-blue{background:var(--blue-dim);color:var(--blue-acc)}.badge-blue::before{background:var(--blue-acc)}
        .badge-muted{background:rgba(255,255,255,0.05);color:var(--text-muted)}.badge-muted::before{background:var(--text-muted)}

        /* ═══ BUTTONS — Aux couleurs BTL ═══ */
        .btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;border:none;transition:all 0.15s;text-decoration:none;font-family:var(--font-body)}
        .btn-primary{background:linear-gradient(135deg,var(--btl-green),var(--btl-green-light));color:white;font-weight:600;border:1px solid var(--btl-green)}
        .btn-primary:hover{box-shadow:0 4px 20px rgba(15,74,54,0.5);transform:translateY(-1px);background:linear-gradient(135deg,var(--btl-green-light),var(--btl-green-pale))}
        .btn-secondary{background:var(--bg-card);color:var(--text-secondary);border:1px solid var(--border)}
        .btn-secondary:hover{border-color:var(--btl-green-pale);color:var(--text-primary)}
        .btn-danger{background:linear-gradient(135deg,var(--btl-red),var(--btl-red-light));color:white;font-weight:600;border:1px solid var(--btl-red)}
        .btn-danger:hover{box-shadow:0 4px 20px rgba(216,35,42,0.4);transform:translateY(-1px)}
        .btn-sm{padding:6px 12px;font-size:12px}
        .btn-ghost{background:transparent;color:var(--text-muted);border:none;padding:6px 10px}
        .btn-ghost:hover{color:var(--text-primary);background:rgba(255,255,255,0.05)}

        /* ═══ INPUTS ═══ */
        .input{background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-sm);padding:9px 14px;font-size:13px;color:var(--text-primary);font-family:var(--font-body);outline:none;transition:border-color 0.15s;width:100%}
        .input:focus{border-color:var(--btl-green-pale);box-shadow:0 0 0 3px rgba(15,74,54,0.15)}
        .input::placeholder{color:var(--text-muted)}
        select.input{cursor:pointer}
        .input-wrap{position:relative;display:flex;align-items:center}
        .input-icon{position:absolute;left:12px;color:var(--text-muted);pointer-events:none}
        .input-wrap .input{padding-left:36px}

        /* ═══ GRID & UTILS ═══ */
        .grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
        .grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
        .grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
        .flex{display:flex}.flex-1{flex:1}.gap-3{gap:12px}.gap-4{gap:16px}
        .items-center{align-items:center}.justify-between{justify-content:space-between}
        .mb-6{margin-bottom:24px}.mb-5{margin-bottom:20px}.mb-4{margin-bottom:16px}.mb-3{margin-bottom:12px}
        .text-muted{color:var(--text-muted)}.text-gold{color:var(--btl-green-pale)}.text-green{color:var(--green)}.text-red{color:var(--btl-red)}
        .font-mono{font-family:monospace;font-size:12px}.text-sm{font-size:12px}.text-xs{font-size:11px}
        .empty-state{text-align:center;padding:60px 20px;color:var(--text-muted)}
        .empty-title{font-size:15px;font-weight:500;color:var(--text-secondary);margin-bottom:6px}

        ::-webkit-scrollbar{width:5px;height:5px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.1);border-radius:10px}
        body.light-mode ::-webkit-scrollbar-thumb{background:rgba(0,0,0,0.15)}

        /* Version footer dans sidebar */
        .sidebar-version{padding:8px 16px;font-size:10px;color:rgba(255,255,255,0.3);text-align:center;letter-spacing:0.8px;border-top:1px solid rgba(255,255,255,0.05)}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo-img">
            <img src="{{ asset('img/btl-logo.png') }}" alt="BTL Bank">
        </div>
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
        {{-- Badge fichiers en attente --}}
        @if(in_array(auth()->user()->role ?? '', ['superviseur','admin']))
        @php $enAttente = \App\Models\TcFichier::where('statut','EN_ATTENTE_VALIDATION')->count(); @endphp
        @if($enAttente > 0)
        <a href="{{ route('fichiers.index') }}" class="nav-item" style="background:rgba(245,158,11,0.2);color:#fbbf24;margin-top:8px;border:1px solid rgba(245,158,11,0.3)">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $enAttente }} fichier(s) à valider
        </a>
        @endif
        @endif

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
        <a href="{{ route('rapport.index') }}" class="nav-item {{ request()->routeIs('rapport.index') ? 'active' : '' }}">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
            Rapports
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
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
    <div class="sidebar-version">BTL · v1.0.0 · © 2026</div>
</aside>
<header class="header">
    <div class="header-breadcrumb">
        <strong>BTL Bank</strong>
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
        @livewire('notifications')
    </div>
</header>
<main class="main">
    {{ $slot ?? '' }}
    @yield('content')
</main>
@livewireScripts
@stack('scripts')
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
    if(el)el.textContent=n.toLocaleDateString('fr-FR',{day:'2-digit',month:'short',year:'numeric'})+' · '+n.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
}
updateTime();setInterval(updateTime,30000);
</script>
</body>
</html>