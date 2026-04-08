<div class="p-6 space-y-6">

    {{-- ── En-tête ──────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Messages de Rejet — Pacs.004
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Génération des fichiers XML de retour de paiement (Payment Return)
            </p>
        </div>
    </div>

    {{-- ── Messages succès / erreur ───────────────────────────── --}}
    @if($messageSucces)
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ $messageSucces }}
        </div>
    @endif

    @if($messageErreur)
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $messageErreur }}
        </div>
    @endif

    {{-- ── Statistiques ────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Fichiers avec rejets</p>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['fichiers_rejet'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-indigo-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Pacs.004 générés</p>
            <p class="text-3xl font-bold text-indigo-600">{{ $stats['total_generes'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-yellow-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">En attente d'envoi</p>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['en_attente'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Envoyés</p>
            <p class="text-3xl font-bold text-green-600">{{ $stats['envoyes'] }}</p>
        </div>
    </div>

    {{-- ── Section 1 : Fichiers à traiter ────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 dark:text-white">
                Fichiers avec rejets — Générer Pacs.004
            </h2>
            <div class="flex gap-2">
                <input
                    wire:model.live.debounce.300ms="recherche"
                    type="text"
                    placeholder="Rechercher un fichier..."
                    class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                />
                <select
                    wire:model.live="typeValeur"
                    class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
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
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Fichier</th>
                        <th class="px-4 py-3 text-center">Type</th>
                        <th class="px-4 py-3 text-center">Nb Rejets</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Date</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($fichiers as $fichier)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $fichier->nom_fichier }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $fichier->type_valeur ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    {{ $fichier->rejets_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $couleur = match($fichier->statut) {
                                        'TRAITE'         => 'green',
                                        'TRAITE_PARTIEL' => 'yellow',
                                        'ERREUR'         => 'red',
                                        default          => 'gray',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $couleur }}-100 text-{{ $couleur }}-800">
                                    {{ $fichier->statut }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs">
                                {{ $fichier->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="confirmerGeneration({{ $fichier->id }})"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Générer Pacs.004
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Aucun fichier avec des rejets trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $fichiers->links() }}
        </div>
    </div>

    {{-- ── Section 2 : Pacs.004 générés ──────────────────────── --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 dark:text-white">
                Pacs.004 générés
            </h2>
            <select
                wire:model.live="statut"
                class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
                <option value="">Tous statuts</option>
                <option value="GENERE">Généré</option>
                <option value="ENVOYE">Envoyé</option>
                <option value="ERREUR">Erreur</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Msg ID</th>
                        <th class="px-4 py-3 text-left">Fichier source</th>
                        <th class="px-4 py-3 text-center">Nb Txs</th>
                        <th class="px-4 py-3 text-center">XSD</th>
                        <th class="px-4 py-3 text-center">Statut</th>
                        <th class="px-4 py-3 text-center">Date génération</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($pacs004List as $pacs004)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                            <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $pacs004->msg_id }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $pacs004->fichier?->nom_fichier ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800 dark:text-white">
                                {{ $pacs004->nb_of_txs }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($pacs004->valide_xsd)
                                    <span class="text-green-600" title="XML valide">✓</span>
                                @else
                                    <span class="text-red-500" title="XML invalide">✗</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $couleur = match($pacs004->statut) {
                                        'ENVOYE' => 'green',
                                        'ERREUR' => 'red',
                                        default  => 'yellow',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-{{ $couleur }}-100 text-{{ $couleur }}-800">
                                    {{ $pacs004->statut }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-500 text-xs">
                                {{ $pacs004->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Voir XML --}}
                                    <button
                                        wire:click="voirXml({{ $pacs004->id }})"
                                        title="Voir XML"
                                        class="p-1.5 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-700 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>

                                    {{-- Télécharger --}}
                                    <a
                                        href="{{ route('pacs004.telecharger', $pacs004->id) }}"
                                        title="Télécharger XML"
                                        class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>

                                    {{-- Marquer envoyé --}}
                                    @if($pacs004->statut === 'GENERE')
                                        <button
                                            wire:click="marquerEnvoye({{ $pacs004->id }})"
                                            wire:confirm="Marquer ce Pacs.004 comme envoyé ?"
                                            title="Marquer comme envoyé"
                                            class="p-1.5 rounded-lg bg-green-100 hover:bg-green-200 text-green-700 transition-colors"
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
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Aucun Pacs.004 généré pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $pacs004List->links() }}
        </div>
    </div>

    {{-- ── Modal Confirmation Génération ──────────────────────── --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-100 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Générer Pacs.004
                    </h3>
                </div>

                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Voulez-vous générer le message de rejet <strong>Pacs.004</strong> pour le fichier
                    <strong>#{{ $fichierId }}</strong> ?
                    <br><br>
                    Cette action va créer un fichier XML normalisé contenant tous les rejets associés.
                </p>

                <div class="flex gap-3 justify-end">
                    <button
                        wire:click="annuler"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium"
                    >
                        Annuler
                    </button>
                    <button
                        wire:click="generer"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition-colors disabled:opacity-60 flex items-center gap-2"
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

    {{-- ── Modal Visualisation XML ─────────────────────────────── --}}
    @if($showXmlModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl mx-4 flex flex-col max-h-[85vh]">
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Contenu XML — Pacs.004
                    </h3>
                    <button
                        wire:click="fermerXml"
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-500 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-auto p-4">
                    <pre class="text-xs font-mono bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto leading-relaxed">{{ $xmlContent }}</pre>
                </div>
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button
                        wire:click="fermerXml"
                        class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium transition-colors"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>