<nav class="bg-slate-800 border-b border-slate-700 px-6 py-4">
    <div class="flex items-center justify-between">

        {{-- Logo --}}
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <span class="text-white font-semibold">Télécompensation SIBTEL</span>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center gap-6">

            <a href="{{ route('dashboard') }}"
               class="text-sm transition {{ request()->routeIs('dashboard') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                Dashboard
            </a>

            @if(auth()->user()->isAdmin() || auth()->user()->isOperateur())
                <a href="{{ route('fichiers.upload') }}"
                   class="text-sm transition {{ request()->routeIs('fichiers.upload') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                    Upload
                </a>
            @endif

            <a href="{{ route('fichiers.index') }}"
               class="text-sm transition {{ request()->routeIs('fichiers.index', 'fichiers.show', 'fichiers.xml') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                Fichiers
            </a>

            {{-- Rejets avec sous-menu --}}
            <div class="relative group">
                <button class="text-sm transition flex items-center gap-1
                    {{ request()->routeIs('rejets.*') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                    Rejets
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Sous-menu --}}
                <div class="absolute top-full left-0 mt-2 w-48 bg-slate-700 rounded-lg shadow-xl
                            opacity-0 invisible group-hover:opacity-100 group-hover:visible
                            transition-all duration-200 z-50">
                    <a href="{{ route('rejets.index') }}"
                       class="flex items-center gap-2 px-4 py-2.5 text-sm rounded-t-lg transition
                           {{ request()->routeIs('rejets.index') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Liste des rejets
                    </a>

                    @if(auth()->user()->isAdmin() || auth()->user()->isOperateur())
                        <a href="{{ route('rejets.pacs004') }}"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm rounded-b-lg transition
                               {{ request()->routeIs('rejets.pacs004') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-600 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Pacs.004 — Rejets
                        </a>
                    @endif
                </div>
            </div>

            <a href="{{ route('stats.index') }}"
               class="text-sm transition {{ request()->routeIs('stats.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                Statistiques
            </a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}"
                   class="text-sm transition {{ request()->routeIs('users.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                    Utilisateurs
                </a>

                <a href="{{ route('audit.index') }}"
                   class="text-sm transition {{ request()->routeIs('audit.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                    Audit
                </a>
            @endif

        </div>

        {{-- Profil + Déconnexion --}}
        <div class="flex items-center gap-4">
            <span class="text-slate-400 text-sm">
                {{ auth()->user()->name }}
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full
                    {{ auth()->user()->role === 'admin'      ? 'bg-blue-600/20 text-blue-400'   : '' }}
                    {{ auth()->user()->role === 'operateur'  ? 'bg-green-600/20 text-green-400' : '' }}
                    {{ auth()->user()->role === 'superviseur'? 'bg-purple-600/20 text-purple-400': '' }}">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-slate-400 hover:text-red-400 text-sm transition">
                    Déconnexion
                </button>
            </form>
        </div>

    </div>
</nav>