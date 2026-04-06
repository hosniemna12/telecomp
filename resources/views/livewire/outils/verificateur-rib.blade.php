<div class="max-w-2xl mx-auto">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Vérificateur RIB Tunisien</h1>
        <p class="text-slate-400 text-sm mt-1">
            Validation par algorithme modulo 97 — Standard bancaire tunisien
        </p>
    </div>

    {{-- Formulaire --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 mb-6">

        <div class="mb-4">
            <label class="block text-sm font-medium text-slate-300 mb-2">
                Numéro RIB (20 chiffres)
            </label>
            <div class="flex gap-3">
                <input
                    wire:model="rib"
                    type="text"
                    maxlength="20"
                    placeholder="Ex: 05009000028314317192"
                    class="flex-1 bg-slate-700 border border-slate-600 text-white
                           placeholder-slate-400 rounded-lg px-4 py-3 text-sm
                           font-mono tracking-widest focus:outline-none
                           focus:ring-2 focus:ring-blue-500"
                />
                <button
                    wire:click="verifier"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3
                           rounded-lg text-sm transition font-medium">
                    Vérifier
                </button>
                @if($verifie)
                    <button
                        wire:click="reinitialiser"
                        class="bg-slate-700 hover:bg-slate-600 text-slate-300
                               px-4 py-3 rounded-lg text-sm transition">
                        Reset
                    </button>
                @endif
            </div>

            {{-- Compteur caractères --}}
            <p class="text-slate-500 text-xs mt-2">
                {{ strlen(preg_replace('/\s+/', '', $rib)) }} / 20 caractères
            </p>
        </div>

        {{-- Format attendu --}}
        <div class="bg-slate-700/50 rounded-lg p-3 text-xs">
            <p class="text-slate-400 font-medium mb-2">Format RIB tunisien :</p>
            <div class="flex items-center gap-2 font-mono">
                <span class="bg-blue-600/30 text-blue-300 px-2 py-1 rounded">BB</span>
                <span class="text-slate-500">+</span>
                <span class="bg-green-600/30 text-green-300 px-2 py-1 rounded">AAA</span>
                <span class="text-slate-500">+</span>
                <span class="bg-purple-600/30 text-purple-300 px-2 py-1 rounded">CCCCCCCCCCCCC</span>
                <span class="text-slate-500">+</span>
                <span class="bg-amber-600/30 text-amber-300 px-2 py-1 rounded">KK</span>
            </div>
            <div class="flex items-center gap-2 mt-2 text-slate-500">
                <span class="w-10 text-center text-blue-400">BB</span>
                <span class="text-slate-600">Code banque (2)</span>
                <span class="w-12 text-center text-green-400 ml-2">AAA</span>
                <span class="text-slate-600">Code agence (3)</span>
                <span class="w-28 text-center text-purple-400 ml-2">CCCCCCCCCCCCC</span>
                <span class="text-slate-600">N° compte (13)</span>
                <span class="w-8 text-center text-amber-400 ml-2">KK</span>
                <span class="text-slate-600">Clé (2)</span>
            </div>
        </div>

    </div>

    {{-- Résultat --}}
    @if($verifie && !empty($resultat))
        <div class="bg-slate-800 border {{ $resultat['valide'] ? 'border-green-700/50' : 'border-red-700/50' }}
                    rounded-xl p-6">

            {{-- Header résultat --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center
                            {{ $resultat['valide'] ? 'bg-green-600/20' : 'bg-red-600/20' }}">
                    @if($resultat['valide'])
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="font-semibold {{ $resultat['valide'] ? 'text-green-400' : 'text-red-400' }}">
                        {{ $resultat['valide'] ? 'RIB Valide ✓' : 'RIB Invalide ✗' }}
                    </p>
                    @if(!$resultat['valide'] && isset($resultat['erreur']))
                        <p class="text-red-300 text-xs mt-0.5">{{ $resultat['erreur'] }}</p>
                    @endif
                </div>
            </div>

            @if(strlen(preg_replace('/\s+/', '', $rib)) === 20)
                {{-- Décomposition visuelle --}}
                <div class="mb-6">
                    <p class="text-slate-400 text-xs font-medium mb-3">Décomposition du RIB :</p>
                    <div class="flex items-center gap-2 font-mono text-sm flex-wrap">
                        <div class="text-center">
                            <div class="bg-blue-600/20 border border-blue-600/30 text-blue-300
                                        px-3 py-2 rounded-lg">
                                {{ $resultat['code_banque'] ?? '' }}
                            </div>
                            <p class="text-slate-500 text-xs mt-1">Banque</p>
                        </div>
                        <span class="text-slate-600 text-lg">—</span>
                        <div class="text-center">
                            <div class="bg-green-600/20 border border-green-600/30 text-green-300
                                        px-3 py-2 rounded-lg">
                                {{ $resultat['code_agence'] ?? '' }}
                            </div>
                            <p class="text-slate-500 text-xs mt-1">Agence</p>
                        </div>
                        <span class="text-slate-600 text-lg">—</span>
                        <div class="text-center">
                            <div class="bg-purple-600/20 border border-purple-600/30 text-purple-300
                                        px-4 py-2 rounded-lg tracking-wider">
                                {{ $resultat['num_compte'] ?? '' }}
                            </div>
                            <p class="text-slate-500 text-xs mt-1">N° Compte</p>
                        </div>
                        <span class="text-slate-600 text-lg">—</span>
                        <div class="text-center">
                            <div class="border px-3 py-2 rounded-lg
                                        {{ $resultat['valide']
                                            ? 'bg-green-600/20 border-green-600/30 text-green-300'
                                            : 'bg-red-600/20 border-red-600/30 text-red-300' }}">
                                {{ $resultat['cle_controle'] ?? '' }}
                            </div>
                            <p class="text-slate-500 text-xs mt-1">Clé</p>
                        </div>
                    </div>
                </div>

                {{-- Infos banque --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-700/50 rounded-lg p-4">
                        <p class="text-slate-400 text-xs mb-1">Établissement bancaire</p>
                        <p class="text-white text-sm font-medium">
                            {{ $resultat['nom_banque'] ?? '—' }}
                        </p>
                    </div>
                    <div class="bg-slate-700/50 rounded-lg p-4">
                        <p class="text-slate-400 text-xs mb-1">Validation algorithme</p>
                        <div class="flex items-center gap-2">
                            <p class="text-slate-300 text-sm font-mono">
                                Clé calculée :
                                <span class="{{ $resultat['valide'] ? 'text-green-400' : 'text-red-400' }} font-bold">
                                    {{ $resultat['cle_calculee'] ?? '—' }}
                                </span>
                            </p>
                        </div>
                        <p class="text-slate-500 text-xs mt-1">Algorithme modulo 97</p>
                    </div>
                </div>
            @endif

        </div>

        {{-- RIBs exemples --}}
        <div class="mt-4 bg-slate-800/50 border border-slate-700/50 rounded-xl p-4">
            <p class="text-slate-400 text-xs font-medium mb-3">
                RIBs de test (extraits de vos fichiers) :
            </p>
            <div class="space-y-2">
                @foreach(\App\Models\TcEnrDetail::whereNotNull('rib_donneur')->take(3)->get() as $detail)
                    <button
                        wire:click="$set('rib', '{{ $detail->rib_donneur }}')"
                        class="w-full text-left bg-slate-700/50 hover:bg-slate-700
                               rounded-lg px-3 py-2 transition">
                        <span class="text-slate-300 text-xs font-mono">{{ $detail->rib_donneur }}</span>
                        <span class="text-slate-500 text-xs ml-2">— {{ $detail->nom_donneur }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

</div>