<div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Fichiers de telecompensation</h1>
            <p class="text-slate-400 text-sm mt-1">Gestion et suivi des fichiers T24</p>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->isOperateur())
            <a href="{{ route('fichiers.upload') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouveau fichier
            </a>
        @endif
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Total fichiers</div>
        </div>
        <div class="bg-slate-800 border border-green-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-400">{{ $stats['traites'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Traites</div>
        </div>
        <div class="bg-slate-800 border border-red-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-red-400">{{ $stats['erreurs'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Erreurs</div>
        </div>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="flex-1 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input wire:model.live.debounce.300ms="recherche" type="text"
                    placeholder="Rechercher par nom de fichier..."
                    class="w-full bg-slate-700 border border-slate-600 text-white placeholder-slate-400 rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
            </div>
            <button wire:click="toggleFiltres"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition border {{ $showFiltres ? 'bg-blue-600 border-blue-500 text-white' : 'bg-slate-700 border-slate-600 text-slate-300 hover:border-slate-500' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filtres avances
                @if($this->nbFiltresActifs() > 0)
                    <span class="bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $this->nbFiltresActifs() }}</span>
                @endif
            </button>
            @if($this->nbFiltresActifs() > 0)
                <button wire:click="reinitialiser" class="px-3 py-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg transition">
                    Reinitialiser
                </button>
            @endif
        </div>

        @if($showFiltres)
            <div class="mt-4 pt-4 border-t border-slate-700 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Type de fichier</label>
                    <select wire:model.live="typeValeur"
                        class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les types</option>
                        <option value="10">Virement (10)</option>
                        <option value="20">Prelevement (20)</option>
                        <option value="30">Cheque (30)</option>
                        <option value="31">CNP — Cheque partiel (31)</option>
                        <option value="32">ARP — Cheque apres ARP (32)</option>
                        <option value="33">Cheque retour (33)</option>
                        <option value="40">Lettre de change (40)</option>
                        <option value="41">LDC retour (41)</option>
                        <option value="42">LDC (42)</option>
                        <option value="43">LDC (43)</option>
                        <option value="82">CNP (82)</option>
                        <option value="83">ARP (83)</option>
                        <option value="84">Papillon (84)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Statut</label>
                    <select wire:model.live="statut"
                        class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Tous les statuts</option>
                        <option value="TRAITE">Traite</option>
                        <option value="ERREUR">Erreur</option>
                        <option value="EN_COURS">En cours</option>
                        <option value="TRAITE_PARTIEL">Traite partiel</option>
                        <option value="RECU">Recu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Date debut</label>
                    <input wire:model.live="dateDebut" type="date"
                        class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Date fin</label>
                    <input wire:model.live="dateFin" type="date"
                        class="w-full bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                </div>
            </div>

            @if($this->nbFiltresActifs() > 0)
                @php
                    $typesLabels = [
                        '10'=>'Virement','20'=>'Prelevement','30'=>'Cheque',
                        '31'=>'CNP','32'=>'ARP','33'=>'Chq.retour',
                        '40'=>'LDC','41'=>'LDC retour','42'=>'LDC 42','43'=>'LDC 43',
                        '82'=>'CNP 82','83'=>'ARP 83','84'=>'Papillon'
                    ];
                @endphp
                <div class="mt-3 flex items-center gap-2 flex-wrap">
                    <span class="text-slate-500 text-xs">Filtres actifs :</span>
                    @if($recherche)<span class="bg-blue-600/20 text-blue-400 text-xs px-2 py-0.5 rounded-full">Nom : {{ $recherche }}</span>@endif
                    @if($typeValeur)<span class="bg-purple-600/20 text-purple-400 text-xs px-2 py-0.5 rounded-full">Type : {{ $typesLabels[$typeValeur] ?? $typeValeur }}</span>@endif
                    @if($statut)<span class="bg-green-600/20 text-green-400 text-xs px-2 py-0.5 rounded-full">Statut : {{ $statut }}</span>@endif
                    @if($dateDebut)<span class="bg-amber-600/20 text-amber-400 text-xs px-2 py-0.5 rounded-full">Du : {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }}</span>@endif
                    @if($dateFin)<span class="bg-amber-600/20 text-amber-400 text-xs px-2 py-0.5 rounded-full">Au : {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}</span>@endif
                </div>
            @endif
        @endif
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-6 py-3 border-b border-slate-700 flex items-center justify-between">
            <span class="text-slate-400 text-xs">{{ $fichiers->total() }} fichier(s) trouve(s)</span>
            @if($this->nbFiltresActifs() > 0)
                <span class="text-blue-400 text-xs">{{ $this->nbFiltresActifs() }} filtre(s) applique(s)</span>
            @endif
        </div>

        <table class="w-full">
            <thead class="bg-slate-700/50">
                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-6 py-3">Fichier</th>
                    <th class="text-left px-6 py-3">Type</th>
                    <th class="text-left px-6 py-3">Transactions</th>
                    <th class="text-left px-6 py-3">Rejets</th>
                    <th class="text-left px-6 py-3">Statut</th>
                    <th class="text-left px-6 py-3">Date reception</th>
                    <th class="text-left px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($fichiers as $fichier)
                    <tr class="hover:bg-slate-700/30 transition">
                        <td class="px-6 py-4">
                            <p class="text-white text-xs font-mono">{{ $fichier->nom_fichier }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $types = [
                                    '10' => ['label' => 'Virement',    'class' => 'bg-blue-600/20 text-blue-400'],
                                    '20' => ['label' => 'Prelevement', 'class' => 'bg-purple-600/20 text-purple-400'],
                                    '30' => ['label' => 'Cheque',      'class' => 'bg-amber-600/20 text-amber-400'],
                                    '31' => ['label' => 'CNP',         'class' => 'bg-orange-600/20 text-orange-400'],
                                    '32' => ['label' => 'ARP',         'class' => 'bg-teal-600/20 text-teal-400'],
                                    '33' => ['label' => 'Chq.retour',  'class' => 'bg-red-600/20 text-red-400'],
                                    '40' => ['label' => 'LDC',         'class' => 'bg-indigo-600/20 text-indigo-400'],
                                    '41' => ['label' => 'LDC retour',  'class' => 'bg-violet-600/20 text-violet-400'],
                                    '42' => ['label' => 'LDC 42',      'class' => 'bg-indigo-600/20 text-indigo-400'],
                                    '43' => ['label' => 'LDC 43',      'class' => 'bg-violet-600/20 text-violet-400'],
                                    '82' => ['label' => 'CNP 82',      'class' => 'bg-pink-600/20 text-pink-400'],
                                    '83' => ['label' => 'ARP 83',      'class' => 'bg-rose-600/20 text-rose-400'],
                                    '84' => ['label' => 'Papillon',    'class' => 'bg-cyan-600/20 text-cyan-400'],
                                ];
                                $t = $types[$fichier->type_valeur] ?? ['label' => $fichier->type_valeur, 'class' => 'bg-slate-600/20 text-slate-400'];
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $t['class'] }}">{{ $t['label'] }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-300 text-sm">{{ $fichier->enr_details_count ?? 0 }}</td>
                        <td class="px-6 py-4">
                            @if(($fichier->rejets_count ?? 0) > 0)
                                <span class="text-red-400 text-sm font-medium">{{ $fichier->rejets_count }}</span>
                            @else
                                <span class="text-slate-500 text-sm">0</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statuts = [
                                    'TRAITE'         => 'bg-green-600/20 text-green-400',
                                    'ERREUR'         => 'bg-red-600/20 text-red-400',
                                    'EN_COURS'       => 'bg-blue-600/20 text-blue-400',
                                    'TRAITE_PARTIEL' => 'bg-orange-600/20 text-orange-400',
                                    'RECU'           => 'bg-yellow-600/20 text-yellow-400',
                                ];
                                $sc = $statuts[$fichier->statut] ?? 'bg-slate-600/20 text-slate-400';
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $sc }}">{{ $fichier->statut }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-400 text-xs">
                            {{ \Carbon\Carbon::parse($fichier->date_reception)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('fichiers.show', $fichier->id) }}"
                                   class="text-blue-400 hover:text-blue-300 text-xs transition">Details</a>
                                @if($fichier->xmlProduits && $fichier->xmlProduits->count() > 0)
                                    <a href="{{ route('fichiers.xml', $fichier->id) }}"
                                       class="text-purple-400 hover:text-purple-300 text-xs transition">XML</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-slate-400 text-sm font-medium">Aucun fichier trouve</p>
                            @if($this->nbFiltresActifs() > 0)
                                <p class="text-slate-500 text-xs mt-1">Essayez de modifier vos criteres de recherche</p>
                                <button wire:click="reinitialiser" class="mt-3 text-blue-400 hover:text-blue-300 text-xs transition">Reinitialiser les filtres</button>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($fichiers->hasPages())
            <div class="px-6 py-4 border-t border-slate-700">{{ $fichiers->links() }}</div>
        @endif
    </div>

</div>