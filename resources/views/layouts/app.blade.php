<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécompensation — SIBTEL</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-900 min-h-screen flex">

    <aside class="w-64 bg-slate-800 border-r border-slate-700 min-h-screen flex flex-col fixed left-0 top-0 z-30">

        <div class="px-6 py-5 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white font-semibold text-sm">SIBTEL</p>
                    <p class="text-slate-400 text-xs">Télécompensation</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            @if(auth()->user()->isAdmin() || auth()->user()->isOperateur())
            <a href="{{ route('fichiers.upload') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('fichiers.upload') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upload fichier
            </a>
            @endif

            <a href="{{ route('fichiers.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('fichiers.index','fichiers.show','fichiers.xml') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Fichiers
            </a>

            <a href="{{ route('rejets.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition relative {{ request()->routeIs('rejets.index') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Rejets
                @php $nbRejets = \App\Models\TcRejet::where('traite', false)->count(); @endphp
                @if($nbRejets > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $nbRejets }}</span>
                @endif
            </a>

            <a href="{{ route('stats.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('stats.index') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            {{-- Pacs.004 --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isOperateur())
            <a href="{{ route('rejets.pacs004') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition text-slate-400 hover:bg-slate-700 hover:text-white">
                Pacs.004 -- Rejets
            </a>
            @endif

            {{-- Pacs.004 corrige --}}
                Statistiques
            </a>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('users.index') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Utilisateurs
            </a>
            @endif


            {{-- Separateur Outils --}}
            <div class="px-3 py-2 mt-2">
                <p class="text-slate-600 text-xs uppercase tracking-wider">Outils</p>
            </div>

            @if(auth()->user()->isAdmin())
            <a href="{{ route('audit.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('audit.index') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Audit Trail
            </a>
            @endif

            <a href="{{ route('outils.rib') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('outils.rib') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Verificateur RIB
            </a>

        </nav>

        <div class="px-3 py-4 border-t border-slate-700">
            <a href="{{ route('profile.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('profile.index') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0 {{ auth()->user()->role === 'admin' ? 'bg-blue-600/30 text-blue-400' : (auth()->user()->role === 'operateur' ? 'bg-green-600/30 text-green-400' : 'bg-purple-600/30 text-purple-400') }}">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-xs font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-slate-500 text-xs">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
            </a>

            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-slate-400 hover:bg-red-600/10 hover:text-red-400 transition">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>

    </aside>

    <div class="flex-1 ml-64 flex flex-col min-h-screen">

        <header class="bg-slate-800 border-b border-slate-700 px-8 py-4 flex items-center justify-between sticky top-0 z-20">
            <p class="text-slate-400 text-xs">Système National de Télécompensation — SIBTEL</p>
            <div class="flex items-center gap-3">
                @livewire('notifications')
                <span class="text-slate-500 text-xs">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </header>

        @if(session('erreur_acces'))
            <div class="mx-8 mt-6 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('erreur_acces') }}
            </div>
        @endif

        <main class="flex-1 p-8">
            {{ $slot }}
        </main>

    </div>

    @livewireScripts
</body>
</html>

