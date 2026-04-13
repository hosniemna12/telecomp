<div class="p-6 space-y-6">

    {{-- ── En-tête ──────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">
                Messages de Rejet — Pacs.004
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Génération des fichiers XML de retour de paiement (Payment Return)
            </p>
        </div>
    </div>

    {{-- ── Messages succès / erreur ───────────────────────────── --}}
    @if($messageSucces)
        <div class="bg-green-900/30 border border-green-700 text-green-400 rounded-lg p-4 flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ $messageSucces }}
        </div>
    @endif

    @if($messageErreur)
        <div class="bg-red-900/30 border border-red-700 text-red-400 rounded-lg p-4 flex items-center gap-2">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $messageErreur }}
        </div>
    @endif

    {{-- ── Statistiques ────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-slate-800 rounded-xl shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Fichiers avec rejets</p>
            <p class="text-3xl font-bold text-blue-400 mt-1">{{ $stats['fichiers_rejet'] }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl shadow p-4 border-l-4 border-indigo-500">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Pacs.004 générés</p>
            <p class="text-3xl font-bold text-indigo-400 mt-1">{{ $stats['total_generes'] }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-slate-400 uppercase tracking-wide">En attente d'envoi</p>
            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $stats['en_attente'] }}</p>
        </div>
        <div class="bg-slate-800 rounded-xl shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-slate-400 uppercase tracking-wide">Envoyés</p>
            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['envoyes'] }}</p>
        </div>
    </div>

    {{-- ── Section 1 : Fichiers à traiter ────────────────────── --}}
    <div class="bg-slate-800 rounded-xl shadow border border-slate-700">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h2 class="font-semibold text-white">
                Fichiers avec rejets — Générer Pacs.004
            </h2>
            <div class="flex gap-2">
                <input
                    wire:model.live.debounce.300ms="recherche"
                    type="text"
                    placeholder="Rechercher un fichier..."
                    class="text-sm bg-slate-700 border border-slate-600 text-white placeholder-slate-400 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <select
                    wire:model.live="typeValeur"
                    class="text-sm bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Tous types</option>
                    <option value="20">20 — Prélèvement</option>
                    <option value="30">30 — Chèque CNP</option>
                    <option value="40">40 — Lettre de change</option>
                    <option value="60">60 — Virement</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700/50 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Fichier</th>
                        <th class="px-4 py-3 text-center">Type</th>
                        <th class="px-4 py-3 text-center">Nb Rejets</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Date</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($fichiers as $fichier)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3 font-medium text-white">
                                {{ $fichier->nom_fichier }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-900/40 text-blue-400 border border-blue-700">
                                    {{ $fichier->type_valeur ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-900/40 text-red-400 border border-red-700">
                                    {{ $fichier->rejets_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $couleurs = [
                                        'TRAITE'         => 'bg-green-900/40 text-green-400 border-green-700',
                                        'TRAITE_PARTIEL' => 'bg-yellow-900/40 text-yellow-400 border-yellow-700',
                                        'ERREUR'         => 'bg-red-900/40 text-red-400 border-red-700',
                                    ];
                                    $c = $couleurs[$fichier->statut] ?? 'bg-slate-700 text-slate-400 border-slate-600';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium border {{ $c }}">
                                    {{ $fichier->statut }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-400 text-xs">
                                {{ $fichier->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="confirmerGeneration({{ $fichier->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium rounded-lg transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Générer Pacs.004
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                Aucun fichier avec des rejets trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-700">
            {{ $fichiers->links() }}
        </div>
    </div>

    {{-- ── Section 2 : Pacs.004 générés ──────────────────────── --}}
    <div class="bg-slate-800 rounded-xl shadow border border-slate-700">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h2 class="font-semibold text-white">
                Pacs.004 générés
            </h2>
            <select
                wire:model.live="statut"
                class="text-sm bg-slate-700 border border-slate-600 text-white rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Tous statuts</option>
                <option value="GENERE">Généré</option>
                <option value="ENVOYE">Envoyé</option>
                <option value="ERREUR">Erreur</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-700/50 text-slate-400 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Msg ID</th>
                        <th class="px-4 py-3 text-left">Fichier source</th>
                        <th class="px-4 py-3 text-center">Nb Txs</th>
                        <th class="px-4 py-3 text-center">XSD</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Date</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($pacs004List as $pacs004)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-slate-300">
                                {{ $pacs004->msg_id }}
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                {{ $pacs004->fichier?->nom_fichier ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-white">
                                {{ $pacs004->nb_of_txs }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($pacs004->valide_xsd)
                                    <span class="text-green-400 font-bold text-base">✓</span>
                                @else
                                    <span class="text-red-400 font-bold text-base">✗</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $couleurs = [
                                        'ENVOYE' => 'bg-green-900/40 text-green-400 border-green-700',
                                        'ERREUR' => 'bg-red-900/40 text-red-400 border-red-700',
                                        'GENERE' => 'bg-yellow-900/40 text-yellow-400 border-yellow-700',
                                    ];
                                    $c = $couleurs[$pacs004->statut] ?? 'bg-slate-700 text-slate-400 border-slate-600';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium border {{ $c }}">
                                    {{ $pacs004->statut }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-400 text-xs">
                                {{ $pacs004->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">

                                    {{-- Voir XML --}}
                                    <button
                                        wire:click="voirXml({{ $pacs004->id }})"
                                        title="Voir XML"
                                        class="p-1.5 rounded-lg bg-blue-900/40 hover:bg-blue-800 text-blue-400 border border-blue-700 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    {{-- Télécharger --}}
                                    <button
                                        wire:click="telecharger({{ $pacs004->id }})"
                                        title="Télécharger XML"
                                        class="p-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 border border-slate-600 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </button>

                                    {{-- Marquer envoyé --}}
                                    @if($pacs004->statut === 'GENERE')
                                        <button
                                            wire:click="marquerEnvoye({{ $pacs004->id }})"
                                            wire:confirm="Marquer ce Pacs.004 comme envoyé ?"
                                            title="Marquer comme envoyé"
                                            class="p-1.5 rounded-lg bg-green-900/40 hover:bg-green-800 text-green-400 border border-green-700 transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                Aucun Pacs.004 généré pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-700">
            {{ $pacs004List->links() }}
        </div>
    </div>

    {{-- ── Modal Confirmation ──────────────────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-900/50 border border-indigo-700 rounded-full">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-white">
                        Générer Pacs.004
                    </h3>
                </div>
                <p class="text-slate-400 mb-6">
                    Voulez-vous générer le message de rejet <strong class="text-white">Pacs.004</strong>
                    pour le fichier <strong class="text-indigo-400">#{{ $fichierId }}</strong> ?
                    <br><br>
                    Cette action va créer un fichier XML normalisé contenant tous les rejets associés.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        wire:click="annuler"
                        class="px-4 py-2 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700 text-sm font-medium transition-colors"
                    >
                        Annuler
                    </button>
                    <button
                        wire:click="generer"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium disabled:opacity-60 flex items-center gap-2 transition-colors"
                    >
                        <span wire:loading.remove wire:target="generer">Générer</span>
                        <span wire:loading wire:target="generer" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Génération...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal XML ───────────────────────────────────────────── --}}
    @if($showXmlModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
            <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-4xl mx-4 flex flex-col max-h-[85vh]">
                <div class="flex items-center justify-between p-4 border-b border-slate-700">
                    <h3 class="text-lg font-bold text-white">
                        Contenu XML — Pacs.004
                    </h3>
                    <button
                        wire:click="fermerXml"
                        class="p-1.5 rounded-lg hover:bg-slate-700 text-slate-400 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-4">
                    <pre class="text-xs font-mono bg-slate-900 text-green-400 p-4 rounded-lg overflow-x-auto leading-relaxed border border-slate-700">{{ $xmlContent }}</pre>
                </div>
                <div class="p-4 border-t border-slate-700 flex justify-end">
                    <button
                        wire:click="fermerXml"
                        class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium transition-colors"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>