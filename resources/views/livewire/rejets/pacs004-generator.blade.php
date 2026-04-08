<div class="space-y-6">

    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Pacs.004 — Messages de rejet</h1>
            <p class="text-slate-400 text-sm mt-1">Génération des fichiers XML de retour de paiement</p>
        </div>
    </div>

    {{-- Messages --}}
    @if($messageSucces)
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $messageSucces }}
        </div>
    @endif

    @if($messageErreur)
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $messageErreur }}
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
            <p class="text-slate-400 text-xs uppercase tracking-wider">Total générés</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $stats['total_generes'] }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
            <p class="text-slate-400 text-xs uppercase tracking-wider">En attente</p>
            <p class="text-2xl font-bold text-orange-400 mt-1">{{ $stats['en_attente'] }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
            <p class="text-slate-400 text-xs uppercase tracking-wider">Envoyés</p>
            <p class="text-2xl font-bold text-green-400 mt-1">{{ $stats['envoyes'] }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
            <p class="text-slate-400 text-xs uppercase tracking-wider">Fichiers avec rejets</p>
            <p class="text-2xl font-bold text-blue-400 mt-1">{{ $stats['fichiers_rejet'] }}</p>
        </div>
    </div>

    {{-- Section : Fichiers à traiter --}}
    <div class="bg-slate-800 rounded-xl border border-slate-700">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h2 class="text-white font-semibold">Fichiers avec rejets</h2>
            <input wire:model.live="recherche" type="text" placeholder="Rechercher..."
                   class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg px-3 py-1.5 w-56 focus:outline-none focus:border-blue-500"/>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700">
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Fichier</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Type</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Statut</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Date</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Pacs.004</th>
                        <th class="text-right px-6 py-3 text-slate-400 font-medium">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($fichiers as $fichier)
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="px-6 py-4">
                                <p class="text-white font-mono text-xs">{{ $fichier->nom_fichier }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-500/20 text-blue-400 text-xs px-2 py-1 rounded-full">
                                    {{ $fichier->type_valeur }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs px-2 py-1 rounded-full
                                    {{ $fichier->statut === 'TRAITE' ? 'bg-green-500/20 text-green-400' :
                                       ($fichier->statut === 'ERREUR' ? 'bg-red-500/20 text-red-400' :
                                       'bg-orange-500/20 text-orange-400') }}">
                                    {{ $fichier->statut }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs">
                                {{ \Carbon\Carbon::parse($fichier->date_reception)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($fichier->pacs004()->exists())
                                    <span class="bg-green-500/20 text-green-400 text-xs px-2 py-1 rounded-full">
                                        ✓ Généré
                                    </span>
                                @else
                                    <span class="bg-slate-600/50 text-slate-400 text-xs px-2 py-1 rounded-full">
                                        Non généré
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="confirmerGeneration({{ $fichier->id }})"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                    Générer Pacs.004
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Aucun fichier avec rejets trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-700">
            {{ $fichiers->links() }}
        </div>
    </div>

    {{-- Section : Pacs.004 générés --}}
    <div class="bg-slate-800 rounded-xl border border-slate-700">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h2 class="text-white font-semibold">Pacs.004 générés</h2>
            <select wire:model.live="statut"
                    class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg px-3 py-1.5 focus:outline-none focus:border-blue-500">
                <option value="">Tous les statuts</option>
                <option value="GENERE">Généré</option>
                <option value="ENVOYE">Envoyé</option>
                <option value="ERREUR">Erreur</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700">
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Message ID</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Fichier</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Nb Tx</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Statut</th>
                        <th class="text-left px-6 py-3 text-slate-400 font-medium">Date</th>
                        <th class="text-right px-6 py-3 text-slate-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($pacs004List as $pacs)
                        <tr class="hover:bg-slate-700/30 transition">
                            <td class="px-6 py-4">
                                <p class="text-white font-mono text-xs">{{ $pacs->msg_id }}</p>
                            </td>
                            <td class="px-6 py-4 text-slate-300 text-xs">
                                {{ $pacs->fichier->nom_fichier ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-500/20 text-blue-400 text-xs px-2 py-1 rounded-full">
                                    {{ $pacs->nb_of_txs }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs px-2 py-1 rounded-full
                                    {{ $pacs->statut === 'ENVOYE' ? 'bg-green-500/20 text-green-400' :
                                       ($pacs->statut === 'ERREUR' ? 'bg-red-500/20 text-red-400' :
                                       'bg-orange-500/20 text-orange-400') }}">
                                    {{ $pacs->statut }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-400 text-xs">
                                {{ $pacs->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                <button wire:click="voirXml({{ $pacs->id }})"
                                        class="bg-slate-600 hover:bg-slate-500 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                    Voir XML
                                </button>
                                <a href="{{ route('pacs004.telecharger', $pacs->id) }}"
                                   class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                    Télécharger
                                </a>
                                @if($pacs->statut === 'GENERE')
                                    <button wire:click="marquerEnvoye({{ $pacs->id }})"
                                            class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg transition">
                                        Marquer envoyé
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                Aucun Pacs.004 généré
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-700">
            {{ $pacs004List->links() }}
        </div>
    </div>

    {{-- Modal confirmation génération --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 w-full max-w-md mx-4">
                <h3 class="text-white font-semibold text-lg mb-2">Confirmer la génération</h3>
                <p class="text-slate-400 text-sm mb-6">
                    Voulez-vous générer le fichier Pacs.004 pour ce fichier ?
                    Cette action créera un message XML de rejet ISO 20022.
                </p>
                <div class="flex justify-end gap-3">
                    <button wire:click="annuler"
                            class="bg-slate-700 hover:bg-slate-600 text-white text-sm px-4 py-2 rounded-lg transition">
                        Annuler
                    </button>
                    <button wire:click="generer"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">
                        Générer
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal XML --}}
    @if($showXmlModal)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 w-full max-w-4xl mx-4 max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-white font-semibold text-lg">Contenu XML Pacs.004</h3>
                    <button wire:click="fermerXml" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto">
                    <pre class="bg-slate-900 rounded-lg p-4 text-xs text-green-400 font-mono overflow-x-auto whitespace-pre-wrap">{{ $xmlContent }}</pre>
                </div>
                <div class="flex justify-end mt-4">
                    <button wire:click="fermerXml"
                            class="bg-slate-700 hover:bg-slate-600 text-white text-sm px-4 py-2 rounded-lg transition">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>