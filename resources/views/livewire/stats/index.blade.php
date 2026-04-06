<div>

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Statistiques</h1>
            <p class="text-slate-400 text-sm mt-1">
                Analyse et visualisation des données de télécompensation
            </p>
        </div>
        <select wire:model.live="periode"
            class="bg-slate-700 border border-slate-600 text-white rounded-lg
                   px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="7">7 derniers jours</option>
            <option value="30">30 derniers jours</option>
            <option value="90">90 derniers jours</option>
        </select>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <p class="text-slate-400 text-xs mb-2">Total fichiers</p>
            <p class="text-3xl font-bold text-white">{{ $stats['total_fichiers'] }}</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <p class="text-slate-400 text-xs mb-2">Total transactions</p>
            <p class="text-3xl font-bold text-white">{{ $stats['total_transactions'] }}</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <p class="text-slate-400 text-xs mb-2">Taux de rejet</p>
            <p class="text-3xl font-bold {{ $stats['taux_rejet'] > 10 ? 'text-red-400' : 'text-green-400' }}">
                {{ $stats['taux_rejet'] }}%
            </p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <p class="text-slate-400 text-xs mb-2">Montant total</p>
            <p class="text-2xl font-bold text-white">
                {{ number_format($stats['montant_total'], 3, ',', ' ') }}
            </p>
            <p class="text-slate-500 text-xs mt-1">TND</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <p class="text-slate-400 text-xs mb-2">Montant moyen</p>
            <p class="text-2xl font-bold text-white">
                {{ number_format($stats['montant_moyen'], 3, ',', ' ') }}
            </p>
            <p class="text-slate-500 text-xs mt-1">TND par transaction</p>
        </div>
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
            <p class="text-slate-400 text-xs mb-2">Total rejets</p>
            <p class="text-3xl font-bold text-red-400">{{ $stats['total_rejets'] }}</p>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Graphique fichiers par jour --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h2 class="text-white font-semibold mb-4">Fichiers traités par jour</h2>
            <canvas id="chartFichiersParJour" height="250"></canvas>
        </div>

        {{-- Graphique répartition par type --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h2 class="text-white font-semibold mb-4">Répartition par type</h2>
            <canvas id="chartParType" height="250"></canvas>
        </div>

        {{-- Graphique transactions par statut --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h2 class="text-white font-semibold mb-4">Transactions par statut</h2>
            <canvas id="chartTransactionsStatut" height="250"></canvas>
        </div>

        {{-- Top 5 transactions --}}
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
            <h2 class="text-white font-semibold mb-4">Top 5 transactions (montant)</h2>
            @if($topTransactions->isEmpty())
                <p class="text-slate-500 text-sm text-center py-8">Aucune donnée</p>
            @else
                <div class="space-y-3">
                    @foreach($topTransactions as $i => $t)
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-blue-600/20 text-blue-400
                                         text-xs flex items-center justify-center flex-shrink-0">
                                {{ $i + 1 }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-white text-sm truncate">
                                    {{ $t->nom_donneur ?: 'N/A' }}
                                    → {{ $t->nom_beneficiaire ?: 'N/A' }}
                                </p>
                                <p class="text-slate-500 text-xs font-mono">
                                    {{ $t->rib_donneur }}
                                </p>
                            </div>
                            <span class="text-green-400 text-sm font-medium flex-shrink-0">
                                {{ number_format($t->montant, 3, ',', ' ') }}
                            </span>
                        </div>
                        @if(!$loop->last)
                            <div class="border-t border-slate-700"></div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    const chartDefaults = {
        color: '#94a3b8',
        borderColor: '#334155',
        grid: { color: '#1e293b' }
    };

    // ① Fichiers par jour
    const fichiersData = @json($fichiersParJour);
    const ctxFichiers = document.getElementById('chartFichiersParJour');
    if (ctxFichiers) {
        new Chart(ctxFichiers, {
            type: 'bar',
            data: {
                labels: fichiersData.map(d => d.jour ? d.jour.substring(0, 10) : ''),
                datasets: [
                    {
                        label: 'Traités',
                        data: fichiersData.map(d => d.traites),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderRadius: 4,
                    },
                    {
                        label: 'Erreurs',
                        data: fichiersData.map(d => d.erreurs),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' }, beginAtZero: true }
                }
            }
        });
    }

    // ② Répartition par type
    const typeData = @json($parType);
    const typeLabels = {
        '10': 'Virement', '20': 'Prélèvement',
        '30': 'Chèque', '31': 'Chq. partiel', '32': 'Chq. ARP'
    };
    const ctxType = document.getElementById('chartParType');
    if (ctxType) {
        new Chart(ctxType, {
            type: 'doughnut',
            data: {
                labels: typeData.map(d => typeLabels[d.type_valeur] || d.type_valeur),
                datasets: [{
                    data: typeData.map(d => d.total),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#94a3b8' }, position: 'bottom' }
                }
            }
        });
    }

    // ③ Transactions par statut
    const statutData = @json($transactionsParStatut);
    const statutColors = {
        'VALIDE': 'rgba(34, 197, 94, 0.8)',
        'REJETE': 'rgba(239, 68, 68, 0.8)',
        'EN_ATTENTE': 'rgba(234, 179, 8, 0.8)',
    };
    const ctxStatut = document.getElementById('chartTransactionsStatut');
    if (ctxStatut) {
        new Chart(ctxStatut, {
            type: 'pie',
            data: {
                labels: statutData.map(d => d.statut),
                datasets: [{
                    data: statutData.map(d => d.total),
                    backgroundColor: statutData.map(d => statutColors[d.statut] || 'rgba(148,163,184,0.8)'),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#94a3b8' }, position: 'bottom' }
                }
            }
        });
    }
</script>