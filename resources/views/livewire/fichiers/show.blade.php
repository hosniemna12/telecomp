<div>

    {{-- Bouton retour --}}
    <div class="mb-6">
        <a href="{{ route('fichiers.index') }}"
           class="text-slate-400 hover:text-white text-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux fichiers
        </a>
    </div>

    {{-- En-tête fichier --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-white font-mono">
                    {{ $fichier->nom_fichier }}
                </h1>
                <p class="text-slate-400 text-sm mt-1">
                    Reçu le {{ \Carbon\Carbon::parse($fichier->date_reception)->format('d/m/Y à H:i:s') }}
                </p>
            </div>

            {{-- Statut --}}
            @php
                $statuts = [
                    'RECU'           => 'bg-slate-600/20 text-slate-400 border-slate-600/30',
                    'EN_COURS'       => 'bg-blue-600/20 text-blue-400 border-blue-600/30',
                    'TRAITE'         => 'bg-green-600/20 text-green-400 border-green-600/30',
                    'TRAITE_PARTIEL' => 'bg-orange-600/20 text-orange-400 border-orange-600/30',
                    'ERREUR'         => 'bg-red-600/20 text-red-400 border-red-600/30',
                ];
                $classeStatut = $statuts[$fichier->statut] ?? 'bg-slate-600/20 text-slate-400';
            @endphp
            <span class="text-sm px-4 py-1.5 rounded-full border {{ $classeStatut }}">
                {{ $fichier->statut }}
            </span>
        </div>

        {{-- Infos du lot --}}
        @if($fichier->enrGlobaux->isNotEmpty())
            @php $global = $fichier->enrGlobaux->first(); @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-700">
                <div>
                    <p class="text-slate-500 text-xs">Numéro de lot</p>
                    <p class="text-white text-sm font-medium mt-1">{{ $global->numero_lot }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Date opération</p>
                    <p class="text-white text-sm font-medium mt-1">
                        {{ \Carbon\Carbon::createFromFormat('Ymd', $global->date_operation)->format('d/m/Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Montant total</p>
                    <p class="text-white text-sm font-medium mt-1">
                        {{ number_format($global->montant_total_virements, 3, ',', ' ') }}
                        {{ $fichier->code_devise }}
                    </p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs">Code devise</p>
                    <p class="text-white text-sm font-medium mt-1">{{ $fichier->code_devise }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- KPIs transactions --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 text-center">
            <div class="text-3xl font-bold text-white">
                {{ $fichier->total_transactions }}
            </div>
            <div class="text-slate-400 text-xs mt-1">Total transactions</div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 text-center">
            <div class="text-3xl font-bold text-green-400">
                {{ $fichier->transactions_valides }}
            </div>
            <div class="text-slate-400 text-xs mt-1">Validées</div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5 text-center">
            <div class="text-3xl font-bold text-red-400">
                {{ $fichier->transactions_rejetees }}
            </div>
            <div class="text-slate-400 text-xs mt-1">Rejetées</div>
        </div>
    </div>

    {{-- XML généré --}}
    @if($fichier->xmlProduits->isNotEmpty())
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-6
                    flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-purple-600/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-white text-sm font-medium">XML ISO 20022 généré</p>
                    <p class="text-slate-400 text-xs">
                        {{ $fichier->xmlProduits->first()->type_message }}
                    </p>
                </div>
            </div>
            <a href="{{ route('fichiers.xml', $fichier->id) }}"
               class="bg-purple-600/20 hover:bg-purple-600/40 text-purple-400
                      text-xs px-4 py-2 rounded-lg transition">
                Voir XML
            </a>
        </div>
    @endif

    {{-- Filtres transactions --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input
                    wire:model.live.debounce.300ms="recherche"
                    type="text"
                    placeholder="Rechercher par nom ou RIB..."
                    class="w-full bg-slate-700 border border-slate-600 text-white
                           placeholder-slate-400 rounded-lg px-4 py-2 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
            <select
                wire:model.live="statutFiltre"
                class="bg-slate-700 border border-slate-600 text-white rounded-lg
                       px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Tous les statuts</option>
                <option value="VALIDE">Validées</option>
                <option value="REJETE">Rejetées</option>
                <option value="EN_ATTENTE">En attente</option>
            </select>
        </div>
    </div>

    {{-- Tableau transactions --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700">
            <h2 class="text-white font-semibold">Transactions</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-700/50">
                    <tr class="text-slate-400 text-xs uppercase tracking-wider">
                        <th class="text-left px-4 py-3">N°</th>
                        <th class="text-left px-4 py-3">Donneur d'ordres</th>
                        <th class="text-left px-4 py-3">Bénéficiaire</th>
                        <th class="text-right px-4 py-3">Montant</th>
                        <th class="text-left px-4 py-3">Motif</th>
                        <th class="text-left px-4 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-700/30 transition">

                            {{-- Numéro --}}
                            <td class="px-4 py-4 text-slate-400 text-xs">
                                #{{ str_pad($transaction->numero_virement, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            {{-- Donneur --}}
                            <td class="px-4 py-4">
                                <p class="text-white text-sm font-medium">
                                    {{ $transaction->nom_donneur ?: '—' }}
                                </p>
                                <p class="text-slate-500 text-xs font-mono mt-0.5">
                                    {{ $transaction->rib_donneur ?: '—' }}
                                </p>
                            </td>

                            {{-- Bénéficiaire --}}
                            <td class="px-4 py-4">
                                <p class="text-white text-sm font-medium">
                                    {{ $transaction->nom_beneficiaire ?: '—' }}
                                </p>
                                <p class="text-slate-500 text-xs font-mono mt-0.5">
                                    {{ $transaction->rib_beneficiaire ?: '—' }}
                                </p>
                            </td>

                            {{-- Montant --}}
                            <td class="px-4 py-4 text-right">
                                <span class="text-white font-medium text-sm">
                                    {{ number_format($transaction->montant, 3, ',', ' ') }}
                                </span>
                                <span class="text-slate-400 text-xs ml-1">TND</span>
                            </td>

                            {{-- Motif --}}
                            <td class="px-4 py-4">
                                <span class="text-slate-300 text-xs">
                                    {{ $transaction->motif_operation ?: '—' }}
                                </span>
                            </td>

                            {{-- Statut --}}
                            <td class="px-4 py-4">
                                @if($transaction->statut === 'VALIDE')
                                    <span class="bg-green-600/20 text-green-400 text-xs
                                                 px-2 py-0.5 rounded">
                                        VALIDE
                                    </span>
                                @elseif($transaction->statut === 'REJETE')
                                    <div>
                                        <span class="bg-red-600/20 text-red-400 text-xs
                                                     px-2 py-0.5 rounded">
                                            REJETE
                                        </span>
                                        @if($transaction->motif_rejet)
                                            <p class="text-red-400/70 text-xs mt-1">
                                                {{ $transaction->motif_rejet }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <span class="bg-slate-600/20 text-slate-400 text-xs
                                                 px-2 py-0.5 rounded">
                                        {{ $transaction->statut }}
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <p class="text-slate-500 text-sm">
                                    Aucune transaction trouvée
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

    {{-- Logs --}}
    @if($fichier->logs->isNotEmpty())
        <div class="bg-slate-800 border border-slate-700 rounded-xl mt-6">
            <div class="px-6 py-4 border-b border-slate-700">
                <h2 class="text-white font-semibold">Journal de traitement</h2>
            </div>
            <div class="p-4 space-y-2">
                @foreach($fichier->logs as $log)
                    <div class="flex items-start gap-3 text-sm">
                        <span class="text-xs px-2 py-0.5 rounded flex-shrink-0 mt-0.5
                            {{ $log->niveau === 'ERROR' ? 'bg-red-600/20 text-red-400' : '' }}
                            {{ $log->niveau === 'WARNING' ? 'bg-yellow-600/20 text-yellow-400' : '' }}
                            {{ $log->niveau === 'INFO' ? 'bg-blue-600/20 text-blue-400' : '' }}">
                            {{ $log->niveau }}
                        </span>
                        <span class="text-slate-400 text-xs flex-shrink-0 mt-0.5">
                            {{ $log->etape }}
                        </span>
                        <span class="text-slate-300 text-xs">{{ $log->message }}</span>
                        <span class="text-slate-600 text-xs ml-auto flex-shrink-0">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>