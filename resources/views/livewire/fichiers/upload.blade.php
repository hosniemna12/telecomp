<div class="max-w-2xl mx-auto">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Upload fichier T24</h1>
        <p class="text-slate-400 text-sm mt-1">
            Importez un fichier ENV ou PAK généré par Temenos T24
        </p>
    </div>

    <div class="bg-slate-800 border border-slate-700 rounded-xl p-8">
        <form wire:submit="traiter" class="space-y-6">

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-3">
                    Type de fichier
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">

                    <button type="button" wire:click="$set('typeValeur', '10')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '10' ? 'border-blue-500 bg-blue-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '10' ? 'bg-blue-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">Virement</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 10 — 280 car.</p>
                    </button>

                    <button type="button" wire:click="$set('typeValeur', '20')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '20' ? 'border-purple-500 bg-purple-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '20' ? 'bg-purple-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">Prélèvement</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 20 — 200 car.</p>
                    </button>

                    <button type="button" wire:click="$set('typeValeur', '30')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '30' ? 'border-amber-500 bg-amber-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '30' ? 'bg-amber-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">Chèque</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 30 — 160 car.</p>
                    </button>

                    <button type="button" wire:click="$set('typeValeur', '31')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '31' ? 'border-orange-500 bg-orange-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '31' ? 'bg-orange-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">CNP — Chq. partiel</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 31 — 350 car.</p>
                    </button>

                    <button type="button" wire:click="$set('typeValeur', '32')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '32' ? 'border-teal-500 bg-teal-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '32' ? 'bg-teal-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">ARP — Chq. après ARP</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 32 — 160 car.</p>
                    </button>

                    <button type="button" wire:click="$set('typeValeur', '40')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '40' ? 'border-indigo-500 bg-indigo-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '40' ? 'bg-indigo-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">Lettre de change</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 40-43 — 380 car.</p>
                    </button>

                    <button type="button" wire:click="$set('typeValeur', '84')"
                        class="p-4 rounded-xl border-2 text-left transition
                               {{ $typeValeur === '84' ? 'border-cyan-500 bg-cyan-600/20' : 'border-slate-600 bg-slate-700/50 hover:border-slate-500' }}">
                        <div class="w-8 h-8 rounded-lg mb-2 flex items-center justify-center {{ $typeValeur === '84' ? 'bg-cyan-600' : 'bg-slate-600' }}">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-white text-sm font-medium">Papillon</p>
                        <p class="text-slate-400 text-xs mt-0.5">Code 84 — 280 car.</p>
                    </button>

                </div>
                @error('typeValeur')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-3">
                    Fichier de télécompensation
                </label>
                <div class="relative border-2 border-dashed border-slate-600 rounded-xl p-8
                            hover:border-blue-500 transition cursor-pointer text-center"
                     onclick="document.getElementById('fichier-input').click()">
                    @if($fichier)
                        <div class="flex items-center justify-center gap-3">
                            <div class="w-10 h-10 bg-green-600/20 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-white text-sm font-medium">{{ $fichier->getClientOriginalName() }}</p>
                                <p class="text-slate-400 text-xs">Fichier sélectionné ✓</p>
                            </div>
                        </div>
                    @else
                        <div class="w-12 h-12 bg-slate-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <p class="text-slate-300 text-sm">Cliquez pour sélectionner un fichier</p>
                        <p class="text-slate-500 text-xs mt-1">Formats acceptés : ENV, PAK — Max 10MB</p>
                    @endif
                    <input id="fichier-input" type="file" wire:model="fichier"
                           accept=".env,.pak,.ENV,.PAK" class="hidden"/>
                </div>
                @error('fichier')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50
                       text-white font-medium py-3 px-4 rounded-xl text-sm
                       transition flex items-center justify-center gap-2"
                @if(!$fichier || !$typeValeur || $enTraitement) disabled @endif>
                <span wire:loading.remove wire:target="traiter">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Lancer le traitement
                </span>
                <span wire:loading wire:target="traiter" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Traitement en cours...
                </span>
            </button>

        </form>
    </div>

    @if(!empty($resultat))
        <div class="mt-6 bg-slate-800 border border-slate-700 rounded-xl p-6">
            @if($resultat['succes'])
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-green-600/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Traitement terminé</p>
                        <p class="text-slate-400 text-xs">Fichier #{{ $resultat['fichier_id'] }}</p>
                    </div>
                    <span class="ml-auto text-xs px-3 py-1 rounded-full
                        {{ $resultat['stats']['statut'] === 'TRAITE' ? 'bg-green-600/20 text-green-400' : 'bg-yellow-600/20 text-yellow-400' }}">
                        {{ $resultat['stats']['statut'] }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-slate-700/50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-white">{{ $resultat['stats']['total'] }}</div>
                        <div class="text-slate-400 text-xs mt-1">Total transactions</div>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-400">{{ $resultat['stats']['valides'] }}</div>
                        <div class="text-slate-400 text-xs mt-1">Validées</div>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-400">{{ $resultat['stats']['rejetes'] }}</div>
                        <div class="text-slate-400 text-xs mt-1">Rejetées</div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-700 flex items-center gap-4">
                    <a href="{{ route('fichiers.show', $resultat['fichier_id']) }}"
                       class="text-blue-400 hover:text-blue-300 text-sm transition">Voir les détails →</a>
                    <button wire:click="reinitialiser"
                            class="text-slate-400 hover:text-white text-sm transition">Nouveau fichier</button>
                </div>
            @else
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-600/20 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Erreur de traitement</p>
                        <p class="text-red-400 text-xs mt-1">{{ $resultat['message'] }}</p>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($erreur)
        <div class="mt-4 bg-red-500/10 border border-red-500/30 rounded-xl p-4">
            <p class="text-red-400 text-sm">{{ $erreur }}</p>
        </div>
    @endif

</div>