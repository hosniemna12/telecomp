<div>

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des rejets</h1>
            <p class="text-slate-400 text-sm mt-1">
                Supervision et traitement des anomalies détectées
            </p>
        </div>
        @if($stats['non_traites'] > 0)
            <button
                wire:click="marquerTousTraites"
                wire:confirm="Marquer tous les rejets comme traités ?"
                class="bg-green-600 hover:bg-green-700 text-white text-sm
                       px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"/>
                </svg>
                Tout marquer traité
            </button>
        @endif
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Total rejets</div>
        </div>
        <div class="bg-slate-800 border border-red-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-red-400">{{ $stats['non_traites'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Non traités</div>
        </div>
        <div class="bg-slate-800 border border-green-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-400">{{ $stats['traites'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Traités</div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-400">{{ $stats['parsing'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Parsing</div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-orange-400">{{ $stats['validation'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Validation</div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-4">

            <div class="flex-1">
                <input
                    wire:model.live.debounce.300ms="recherche"
                    type="text"
                    placeholder="Rechercher par code ou motif..."
                    class="w-full bg-slate-700 border border-slate-600 text-white
                           placeholder-slate-400 rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <select
                wire:model.live="etape"
                class="bg-slate-700 border border-slate-600 text-white rounded-lg
                       px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Toutes les étapes</option>
                <option value="PARSING">Parsing</option>
                <option value="VALIDATION">Validation</option>
                <option value="TRANSFORMATION">Transformation</option>
                <option value="SYSTEME">Système</option>
            </select>

            <select
                wire:model.live="traite"
                class="bg-slate-700 border border-slate-600 text-white rounded-lg
                       px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Tous</option>
                <option value="0">Non traités</option>
                <option value="1">Traités</option>
            </select>

            @if($recherche || $etape || $traite !== '')
                <button
                    wire:click="reinitialiser"
                    class="text-slate-400 hover:text-white text-sm transition
                           px-3 py-2 border border-slate-600 rounded-lg">
                    Réinitialiser
                </button>
            @endif

        </div>
    </div>

    {{-- Tableau --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-700/50">
                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-6 py-3">Fichier</th>
                    <th class="text-left px-6 py-3">Code rejet</th>
                    <th class="text-left px-6 py-3">Motif</th>
                    <th class="text-left px-6 py-3">Étape</th>
                    <th class="text-left px-6 py-3">Transaction</th>
                    <th class="text-left px-6 py-3">Statut</th>
                    <th class="text-left px-6 py-3">Date</th>
                    <th class="text-left px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($rejets as $rejet)
                    <tr class="hover:bg-slate-700/30 transition
                               {{ !$rejet->traite ? 'border-l-2 border-red-500/50' : '' }}">

                        {{-- Fichier --}}
                        <td class="px-6 py-4">
                            <a href="{{ route('fichiers.show', $rejet->fichier_id) }}"
                               class="text-blue-400 hover:text-blue-300 text-xs font-mono transition">
                                {{ $rejet->fichier->nom_fichier ?? '—' }}
                            </a>
                        </td>

                        {{-- Code rejet --}}
                        <td class="px-6 py-4">
                            <span class="bg-red-600/20 text-red-400 text-xs
                                         px-2 py-0.5 rounded font-mono">
                                {{ $rejet->code_rejet }}
                            </span>
                        </td>

                        {{-- Motif --}}
                        <td class="px-6 py-4">
                            <p class="text-slate-300 text-xs max-w-xs truncate">
                                {{ $rejet->motif_rejet ?: '—' }}
                            </p>
                        </td>

                        {{-- Étape --}}
                        <td class="px-6 py-4">
                            @php
                                $etapes = [
                                    'PARSING'        => 'bg-yellow-600/20 text-yellow-400',
                                    'VALIDATION'     => 'bg-orange-600/20 text-orange-400',
                                    'TRANSFORMATION' => 'bg-purple-600/20 text-purple-400',
                                    'SYSTEME'        => 'bg-red-600/20 text-red-400',
                                ];
                                $classeEtape = $etapes[$rejet->etape_detection]
                                    ?? 'bg-slate-600/20 text-slate-400';
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $classeEtape }}">
                                {{ $rejet->etape_detection }}
                            </span>
                        </td>

                        {{-- Transaction --}}
                        <td class="px-6 py-4">
                            @if($rejet->detail)
                                <div class="text-xs">
                                    <p class="text-slate-300">
                                        {{ $rejet->detail->nom_donneur ?: '—' }}
                                    </p>
                                    <p class="text-slate-500 font-mono">
                                        {{ $rejet->detail->rib_donneur ?: '—' }}
                                    </p>
                                </div>
                            @else
                                <span class="text-slate-500 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Statut traitement --}}
                        <td class="px-6 py-4">
                            @if($rejet->traite)
                                <div>
                                    <span class="bg-green-600/20 text-green-400
                                                 text-xs px-2 py-0.5 rounded">
                                        Traité
                                    </span>
                                    @if($rejet->date_traitement)
                                        <p class="text-slate-500 text-xs mt-1">
                                            {{ \Carbon\Carbon::parse($rejet->date_traitement)
                                                ->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <span class="bg-red-600/20 text-red-400
                                             text-xs px-2 py-0.5 rounded">
                                    En attente
                                </span>
                            @endif
                        </td>

                        {{-- Date création --}}
                        <td class="px-6 py-4 text-slate-400 text-xs">
                            {{ \Carbon\Carbon::parse($rejet->created_at)
                                ->format('d/m/Y H:i') }}
                        </td>

                        {{-- Action --}}
                        <td class="px-6 py-4">
                            @if(!$rejet->traite)
                                <button
                                    wire:click="marquerTraite({{ $rejet->id }})"
                                    class="bg-green-600/20 hover:bg-green-600/40
                                           text-green-400 text-xs px-3 py-1.5
                                           rounded-lg transition">
                                    Marquer traité
                                </button>
                            @else
                                <span class="text-slate-500 text-xs">—</span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="text-slate-500">
                                <svg class="w-12 h-12 mx-auto mb-4 opacity-50"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-medium text-slate-400">
                                    Aucun rejet trouvé
                                </p>
                                <p class="text-xs mt-1">
                                    Tous les fichiers ont été traités sans anomalie
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($rejets->hasPages())
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $rejets->links() }}
            </div>
        @endif

    </div>

</div>