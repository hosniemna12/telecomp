<div class="relative" wire:poll.15000ms="refresh">
    <button wire:click="toggleDropdown"
            class="relative p-2 text-slate-400 hover:text-white transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($nbRejets > 0)
            <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 rounded-full
                         text-white text-xs flex items-center justify-center">
                {{ $nbRejets > 9 ? '9+' : $nbRejets }}
            </span>
        @endif
        @if($nbEnCours > 0)
            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-blue-500
                         rounded-full animate-pulse"></span>
        @endif
    </button>

    @if($showDropdown)
        <div class="absolute right-0 top-10 w-80 bg-slate-800 border border-slate-700
                    rounded-xl shadow-2xl z-50">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-white font-medium text-sm">Notifications</h3>
                @if($nbRejets > 0)
                    <span class="bg-red-600/20 text-red-400 text-xs px-2 py-0.5 rounded-full">
                        {{ $nbRejets }} rejet(s)
                    </span>
                @endif
            </div>
            <div class="max-h-64 overflow-y-auto">
                @if($nbEnCours > 0)
                    <div class="px-4 py-3 border-b border-slate-700/50 bg-blue-900/10 flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse flex-shrink-0"></div>
                        <p class="text-blue-300 text-xs">{{ $nbEnCours }} fichier(s) en cours</p>
                    </div>
                @endif
                @forelse($derniersRejets as $rejet)
                    <div class="px-4 py-3 border-b border-slate-700/50 hover:bg-slate-700/30">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0 mt-1"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-slate-300 text-xs font-medium">{{ $rejet->code_rejet }}</p>
                                <p class="text-slate-500 text-xs truncate mt-0.5">{{ $rejet->motif_rejet ?: 'Anomalie détectée' }}</p>
                                <p class="text-slate-600 text-xs mt-0.5">{{ $rejet->fichier->nom_fichier ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <svg class="w-8 h-8 text-slate-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-slate-500 text-xs">Aucun rejet en attente</p>
                    </div>
                @endforelse
            </div>
            <div class="px-4 py-3 border-t border-slate-700">
                <a href="{{ route('rejets.index') }}" wire:click="toggleDropdown"
                   class="text-blue-400 hover:text-blue-300 text-xs transition">
                    Voir tous les rejets →
                </a>
            </div>
        </div>
        <div class="fixed inset-0 z-40" wire:click="toggleDropdown"></div>
    @endif
</div>
