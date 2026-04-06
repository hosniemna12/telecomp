<div wire:poll.10000ms="refresh">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-slate-400 text-sm mt-1">Supervision du systeme de telecompensation</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-1.5">
                <div class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></div>
                <span class="text-slate-400 text-xs">Mise a jour auto (10s)</span>
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isOperateur())
                <a href="{{ route('fichiers.upload') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg transition">+ Nouveau fichier</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Fichiers recus</span>
                <div class="w-8 h-8 bg-blue-600/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ $stats['fichiers'] }}</div>
            <div class="flex items-center gap-3 mt-2">
                <span class="text-green-400 text-xs">✓{{ $stats['traites'] }} traites</span>
                @if($stats['erreurs'] > 0)<span class="text-red-400 text-xs">✗{{ $stats['erreurs'] }} erreurs</span>@endif
            </div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Transactions</span>
                <div class="w-8 h-8 bg-green-600/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ $stats['transactions'] }}</div>
            <p class="text-slate-500 text-xs mt-1">{{ number_format($stats['montant_total'], 3, ',', ' ') }} TND</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">Rejets</span>
                <div class="w-8 h-8 bg-red-600/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ $stats['rejets'] }}</div>
            <p class="text-slate-500 text-xs mt-1">Anomalies detectees</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-slate-400 text-sm">XML generes</span>
                <div class="w-8 h-8 bg-purple-600/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ $stats['xml'] }}</div>
            <p class="text-slate-500 text-xs mt-1">Fichiers ISO 20022</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-800 border border-blue-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-400">{{ $statsParType['virements'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Virements (10)</div>
        </div>
        <div class="bg-slate-800 border border-purple-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-purple-400">{{ $statsParType['prelevements'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Prelevements (20)</div>
        </div>
        <div class="bg-slate-800 border border-amber-700/30 rounded-xl p-4 text-center">
            <div class="text-2xl font-bold text-amber-400">{{ $statsParType['cheques'] }}</div>
            <div class="text-slate-400 text-xs mt-1">Cheques (30/31/32)</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-slate-800 border border-slate-700 rounded-xl">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                <h2 class="text-white font-semibold">Derniers fichiers traites</h2>
                <a href="{{ route('fichiers.index') }}" class="text-blue-400 hover:text-blue-300 text-xs transition">Voir tout →</a>
            </div>
            <div class="p-4">
                @if($derniersFichiers->isEmpty())
                    <div class="text-center py-8"><p class="text-slate-500 text-sm">Aucun fichier traite</p></div>
                @else
                    <table class="w-full">
                        <thead><tr class="text-slate-400 text-xs uppercase tracking-wider">
                            <th class="text-left pb-3 px-2">Fichier</th>
                            <th class="text-left pb-3 px-2">Type</th>
                            <th class="text-left pb-3 px-2">Statut</th>
                            <th class="text-left pb-3 px-2">Date</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($derniersFichiers as $fichier)
                            <tr class="text-sm hover:bg-slate-700/30 transition">
                                <td class="py-2 px-2"><a href="{{ route('fichiers.show', $fichier->id) }}" class="text-blue-400 hover:text-blue-300 font-mono text-xs transition">{{ $fichier->nom_fichier }}</a></td>
                                <td class="py-2 px-2">@php $types=['10'=>['label'=>'Virement','class'=>'bg-blue-600/20 text-blue-400'],'20'=>['label'=>'Prelev.','class'=>'bg-purple-600/20 text-purple-400'],'30'=>['label'=>'Cheque','class'=>'bg-amber-600/20 text-amber-400'],'31'=>['label'=>'Chq.partiel','class'=>'bg-orange-600/20 text-orange-400'],'32'=>['label'=>'Chq.ARP','class'=>'bg-teal-600/20 text-teal-400']];$t=$types[$fichier->type_valeur]??['label'=>$fichier->type_valeur,'class'=>'bg-slate-600/20 text-slate-400'];@endphp<span class="text-xs px-2 py-0.5 rounded {{ $t['class'] }}">{{ $t['label'] }}</span></td>
                                <td class="py-2 px-2">@php $statuts=['TRAITE'=>'bg-green-600/20 text-green-400','ERREUR'=>'bg-red-600/20 text-red-400','EN_COURS'=>'bg-blue-600/20 text-blue-400','TRAITE_PARTIEL'=>'bg-orange-600/20 text-orange-400','RECU'=>'bg-yellow-600/20 text-yellow-400'];$sc=$statuts[$fichier->statut]??'bg-slate-600/20 text-slate-400';@endphp<span class="text-xs px-2 py-0.5 rounded {{ $sc }}">{{ $fichier->statut }}</span></td>
                                <td class="py-2 px-2 text-slate-400 text-xs">{{ \Carbon\Carbon::parse($fichier->date_reception)->format('d/m H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl">
            <div class="px-6 py-4 border-b border-slate-700"><h2 class="text-white font-semibold">Activite recente</h2></div>
            <div class="p-4 space-y-3">
                @forelse($derniersLogs as $log)
                    <div class="flex items-start gap-3">
                        <span class="text-xs px-2 py-0.5 rounded flex-shrink-0 mt-0.5 {{ $log->niveau==='ERROR'?'bg-red-600/20 text-red-400':($log->niveau==='WARNING'?'bg-yellow-600/20 text-yellow-400':'bg-blue-600/20 text-blue-400') }}">{{ $log->niveau }}</span>
                        <div class="flex-1 min-w-0"><p class="text-slate-300 text-xs truncate">{{ $log->message }}</p><p class="text-slate-500 text-xs mt-0.5">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m H:i:s') }}</p></div>
                    </div>
                @empty
                    <p class="text-slate-500 text-sm text-center py-4">Aucune activite</p>
                @endforelse
            </div>
        </div>
    </div>

</div>