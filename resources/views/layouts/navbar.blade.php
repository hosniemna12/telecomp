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

            <a href="{{ route('rejets.index') }}"
               class="text-sm transition {{ request()->routeIs('rejets.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                Rejets
            </a>
            <a href="{{ route('stats.index') }}"
   class="text-sm transition {{ request()->routeIs('stats.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
    Statistiques
</a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}"
                   class="text-sm transition {{ request()->routeIs('users.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                    Utilisateurs
                </a>
            @endif
        </div>

        {{-- Profil + Déconnexion --}}
        <div class="flex items-center gap-4">
            <span class="text-slate-400 text-sm">
                {{ auth()->user()->name }}
                <span class="ml-2 text-xs px-2 py-0.5 rounded-full
                    {{ auth()->user()->role === 'admin' ? 'bg-blue-600/20 text-blue-400' : '' }}
                    {{ auth()->user()->role === 'operateur' ? 'bg-green-600/20 text-green-400' : '' }}
                    {{ auth()->user()->role === 'superviseur' ? 'bg-purple-600/20 text-purple-400' : '' }}">
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