<div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Audit Trail</h1>
            <p class="text-slate-400 text-sm mt-1">
                Journal complet des actions — Conformite bancaire BCT
            </p>
        </div>
        <div class="flex items-center gap-2 bg-slate-800 border border-slate-700
                    rounded-lg px-3 py-1.5">
            <div class="w-2 h-2 rounded-full bg-green-400"></div>
            <span class="text-slate-400 text-xs">Traçabilite activee</span>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Total actions</div>
        </div>
        <div class="bg-slate-800 border border-green-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-green-400">{{ $stats['success'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Succes</div>
        </div>
        <div class="bg-slate-800 border border-red-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-red-400">{{ $stats['failed'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Echecs</div>
        </div>
        <div class="bg-slate-800 border border-blue-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-400">{{ $stats['today'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Aujourd'hui</div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <input wire:model.live.debounce.300ms="recherche" type="text"
                placeholder="Email ou description..."
                class="col-span-2 bg-slate-700 border border-slate-600 text-white
                       placeholder-slate-400 rounded-lg px-4 py-2 text-sm
                       focus:outline-none focus:ring-2 focus:ring-blue-500"/>

            <select wire:model.live="action"
                class="bg-slate-700 border border-slate-600 text-white rounded-lg
                       px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Toutes actions</option>
                <option value="LOGIN">Connexion</option>
                <option value="LOGOUT">Deconnexion</option>
                <option value="UPLOAD">Upload</option>
                <option value="TRAITEMENT">Traitement</option>
                <option value="REJET_TRAITE">Rejet traite</option>
                <option value="USER_CREATE">Creation user</option>
                <option value="USER_UPDATE">Modif. user</option>
                <option value="USER_DELETE">Suppression user</option>
                <option value="PASSWORD_CHANGE">Mdp change</option>
            </select>

            <select wire:model.live="module"
                class="bg-slate-700 border border-slate-600 text-white rounded-lg
                       px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous modules</option>
                <option value="AUTH">Authentification</option>
                <option value="FICHIERS">Fichiers</option>
                <option value="REJETS">Rejets</option>
                <option value="USERS">Utilisateurs</option>
                <option value="PROFILE">Profil</option>
            </select>

            <select wire:model.live="statut"
                class="bg-slate-700 border border-slate-600 text-white rounded-lg
                       px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous statuts</option>
                <option value="SUCCESS">Succes</option>
                <option value="FAILED">Echec</option>
            </select>
        </div>

        @if($recherche || $action || $module || $statut)
            <div class="mt-3 flex justify-end">
                <button wire:click="reinitialiser"
                    class="text-slate-400 hover:text-white text-xs transition
                           px-3 py-1.5 border border-slate-600 rounded-lg">
                    Reinitialiser filtres
                </button>
            </div>
        @endif
    </div>

    {{-- Tableau --}}
    <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden">

        <div class="px-6 py-3 border-b border-slate-700">
            <span class="text-slate-400 text-xs">{{ $logs->total() }} entree(s)</span>
        </div>

        <table class="w-full">
            <thead class="bg-slate-700/50">
                <tr class="text-slate-400 text-xs uppercase tracking-wider">
                    <th class="text-left px-4 py-3">Date/Heure</th>
                    <th class="text-left px-4 py-3">Utilisateur</th>
                    <th class="text-left px-4 py-3">Action</th>
                    <th class="text-left px-4 py-3">Module</th>
                    <th class="text-left px-4 py-3">Description</th>
                    <th class="text-left px-4 py-3">IP</th>
                    <th class="text-left px-4 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-700/20 transition">

                        <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </td>

                        <td class="px-4 py-3">
                            <p class="text-slate-300 text-xs">{{ $log->user_email ?? 'Systeme' }}</p>
                        </td>

                        <td class="px-4 py-3">
                            @php
                                $actionColors = [
                                    'LOGIN'           => 'bg-green-600/20 text-green-400',
                                    'LOGOUT'          => 'bg-slate-600/20 text-slate-400',
                                    'UPLOAD'          => 'bg-blue-600/20 text-blue-400',
                                    'TRAITEMENT'      => 'bg-purple-600/20 text-purple-400',
                                    'REJET_TRAITE'    => 'bg-amber-600/20 text-amber-400',
                                    'USER_CREATE'     => 'bg-teal-600/20 text-teal-400',
                                    'USER_UPDATE'     => 'bg-orange-600/20 text-orange-400',
                                    'USER_DELETE'     => 'bg-red-600/20 text-red-400',
                                    'PASSWORD_CHANGE' => 'bg-pink-600/20 text-pink-400',
                                    'ACCESS_DENIED'   => 'bg-red-600/20 text-red-400',
                                ];
                                $ac = $actionColors[$log->action] ?? 'bg-slate-600/20 text-slate-400';
                            @endphp
                            <span class="text-xs px-2 py-0.5 rounded {{ $ac }}">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-slate-400 text-xs">{{ $log->module ?? '—' }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <p class="text-slate-300 text-xs max-w-xs truncate">
                                {{ $log->description ?? '—' }}
                            </p>
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-slate-500 text-xs font-mono">
                                {{ $log->ip_address ?? '—' }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded
                                {{ $log->statut_action === 'SUCCESS' ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                {{ $log->statut_action }}
                            </span>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <p class="text-slate-500 text-sm">Aucune action enregistree</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $logs->links() }}
            </div>
        @endif

    </div>

</div>

