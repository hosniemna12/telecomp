<div class="w-full max-w-md px-4">

    {{-- Logo + Titre --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Télécompensation</h1>
        <p class="text-slate-400 text-sm mt-1">Système National — SIBTEL</p>
    </div>

    {{-- Carte Login --}}
    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-8 shadow-2xl">

        <h2 class="text-lg font-semibold text-white mb-6">Connexion</h2>

        {{-- Erreur globale --}}
        @if($erreur)
            <div class="bg-red-500/20 border border-red-500/40 text-red-300 rounded-lg px-4 py-3 mb-5 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $erreur }}
            </div>
        @endif

        <form wire:submit="connecter" class="space-y-5">

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Adresse email
                </label>
                <input
                    wire:model="email"
                    type="email"
                    placeholder="admin@sibtel.tn"
                    class="w-full bg-white/10 border border-white/20 text-white placeholder-slate-500
                           rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2
                           focus:ring-blue-500 focus:border-transparent transition"
                    autocomplete="email"
                />
                @error('email')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mot de passe --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">
                    Mot de passe
                </label>
                <input
                    wire:model="password"
                    type="password"
                    placeholder="••••••••"
                    class="w-full bg-white/10 border border-white/20 text-white placeholder-slate-500
                           rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2
                           focus:ring-blue-500 focus:border-transparent transition"
                    autocomplete="current-password"
                />
                @error('password')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Se souvenir --}}
            <div class="flex items-center gap-2">
                <input
                    wire:model="remember"
                    type="checkbox"
                    id="remember"
                    class="w-4 h-4 rounded border-white/20 bg-white/10 text-blue-500
                           focus:ring-blue-500 focus:ring-offset-0"
                />
                <label for="remember" class="text-sm text-slate-400 cursor-pointer">
                    Se souvenir de moi
                </label>
            </div>

            {{-- Bouton --}}
            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4
                       rounded-lg text-sm transition duration-200 flex items-center justify-center gap-2"
            >
                <span wire:loading.remove wire:target="connecter">Se connecter</span>
                <span wire:loading wire:target="connecter" class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Connexion en cours...
                </span>
            </button>

        </form>
    </div>

    {{-- Footer --}}
    <p class="text-center text-slate-500 text-xs mt-6">
        © 2026 SIBTEL — Société Interbancaire de Télécompensation
    </p>

</div>