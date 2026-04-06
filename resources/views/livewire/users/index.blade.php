<div>

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des utilisateurs</h1>
            <p class="text-slate-400 text-sm mt-1">
                Créer et gérer les accès au module de télécompensation
            </p>
        </div>
        @if(auth()->user()->isAdmin())
            <button
                wire:click="ouvrirModal"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm
                       px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4"/>
                </svg>
                Nouvel utilisateur
            </button>
        @endif
    </div>

    {{-- Message succès --}}
    @if($successMsg)
        <div class="bg-green-500/10 border border-green-500/30 text-green-400
                    rounded-xl px-4 py-3 mb-6 flex items-center gap-2 text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $successMsg }}
        </div>
    @endif

    {{-- Message erreur --}}
    @if($erreurForm && !$showModal)
        <div class="bg-red-500/10 border border-red-500/30 text-red-400
                    rounded-xl px-4 py-3 mb-6 text-sm">
            {{ $erreurForm }}
        </div>
    @endif

    {{-- KPIs --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Total</div>
        </div>
        <div class="bg-slate-800 border border-blue-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-400">{{ $stats['admins'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Admins</div>
        </div>
        <div class="bg-slate-800 border border-green-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-400">{{ $stats['operateurs'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Opérateurs</div>
        </div>
        <div class="bg-slate-800 border border-purple-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-purple-400">{{ $stats['superviseurs'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Superviseurs</div>
        </div>
    </div>

    {{-- Recherche --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-6">
        <input
            wire:model.live.debounce.300ms="recherche"
            type="text"
            placeholder="Rechercher par nom ou email..."
            class="w-full bg-slate-700 border border-slate-600 text-white
                   placeholder-slate-400 rounded-lg px-4 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
    </div>

    {{-- Tableau --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-slate-700/50">
                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-6 py-3">Utilisateur</th>
                    <th class="text-left px-6 py-3">Email</th>
                    <th class="text-left px-6 py-3">Rôle</th>
                    <th class="text-left px-6 py-3">Créé le</th>
                    <th class="text-left px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($users as $user)
                    <tr class="hover:bg-slate-700/30 transition
                        {{ (int)$user->id === (int)auth()->id() ? 'bg-blue-900/10' : '' }}">

                        {{-- Nom --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                            text-xs font-bold
                                    {{ $user->role === 'admin' ? 'bg-blue-600/30 text-blue-400' : '' }}
                                    {{ $user->role === 'operateur' ? 'bg-green-600/30 text-green-400' : '' }}
                                    {{ $user->role === 'superviseur' ? 'bg-purple-600/30 text-purple-400' : '' }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-white text-sm font-medium">
                                        {{ $user->name }}
                                        @if((int)$user->id === (int)auth()->id())
                                            <span class="text-blue-400 text-xs ml-1">(vous)</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Email --}}
                        <td class="px-6 py-4 text-slate-300 text-sm">
                            {{ $user->email }}
                        </td>

                        {{-- Rôle --}}
                        <td class="px-6 py-4">
                            @php
                                $roles = [
                                    'admin'       => 'bg-blue-600/20 text-blue-400',
                                    'operateur'   => 'bg-green-600/20 text-green-400',
                                    'superviseur' => 'bg-purple-600/20 text-purple-400',
                                ];
                                $classeRole = $roles[$user->role]
                                    ?? 'bg-slate-600/20 text-slate-400';
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $classeRole }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-6 py-4 text-slate-400 text-xs">
                            {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            @if(auth()->user()->isAdmin())
                                <div class="flex items-center gap-3">
                                    <button
                                        wire:click="editer({{ $user->id }})"
                                        class="text-blue-400 hover:text-blue-300 text-xs transition">
                                        Modifier
                                    </button>
                                    @if((int)$user->id !== (int)auth()->id())
                                        <button
                                            wire:click="supprimer({{ $user->id }})"
                                            wire:confirm="Supprimer cet utilisateur ?"
                                            class="text-red-400 hover:text-red-300 text-xs transition">
                                            Supprimer
                                        </button>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <p class="text-slate-500 text-sm">Aucun utilisateur trouvé</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;
                    align-items:center;justify-content:center;z-index:50;min-height:100vh;">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 w-full max-w-md mx-4">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-white">
                        {{ $editMode ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
                    </h2>
                    <button wire:click="fermerModal"
                            class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($erreurForm)
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400
                                rounded-lg px-4 py-3 mb-4 text-sm">
                        {{ $erreurForm }}
                    </div>
                @endif

                <div class="space-y-4">

                    {{-- Nom --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nom complet
                        </label>
                        <input
                            wire:model="name"
                            type="text"
                            placeholder="Ex: Mohamed Ben Ali"
                            class="w-full bg-slate-700 border border-slate-600 text-white
                                   placeholder-slate-400 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @error('name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">
                            Adresse email
                        </label>
                        <input
                            wire:model="email"
                            type="email"
                            placeholder="Ex: m.benali@banque.tn"
                            class="w-full bg-slate-700 border border-slate-600 text-white
                                   placeholder-slate-400 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @error('email')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">
                            Mot de passe
                            @if($editMode)
                                <span class="text-slate-500 font-normal">
                                    (laisser vide = inchangé)
                                </span>
                            @endif
                        </label>
                        <input
                            wire:model="password"
                            type="password"
                            placeholder="••••••••"
                            class="w-full bg-slate-700 border border-slate-600 text-white
                                   placeholder-slate-400 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        @error('password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Rôle --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1.5">
                            Rôle
                        </label>
                        <select
                            wire:model="role"
                            class="w-full bg-slate-700 border border-slate-600 text-white
                                   rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="operateur">Opérateur</option>
                            <option value="superviseur">Superviseur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                        @error('role')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description rôles --}}
                    <div class="bg-slate-700/50 rounded-lg p-3 text-xs text-slate-400 space-y-1">
                        <p>
                            <span class="text-green-400 font-medium">Opérateur</span>
                            — Upload et traitement des fichiers
                        </p>
                        <p>
                            <span class="text-purple-400 font-medium">Superviseur</span>
                            — Consultation et supervision uniquement
                        </p>
                        <p>
                            <span class="text-blue-400 font-medium">Administrateur</span>
                            — Accès complet + gestion utilisateurs
                        </p>
                    </div>

                </div>

                {{-- Boutons --}}
                <div class="flex items-center gap-3 mt-6">
                    <button
                        wire:click="sauvegarder"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white
                               font-medium py-2.5 rounded-lg text-sm transition">
                        {{ $editMode ? 'Mettre à jour' : 'Créer l\'utilisateur' }}
                    </button>
                    <button
                        wire:click="fermerModal"
                        class="flex-1 bg-slate-700 hover:bg-slate-600 text-slate-300
                               font-medium py-2.5 rounded-lg text-sm transition">
                        Annuler
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>