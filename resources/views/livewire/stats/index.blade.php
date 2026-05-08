<div>
    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 style="font-family:var(--font-display);font-size:24px;font-weight:700;color:var(--text-primary)">
                Statistiques
            </h1>
            <p style="font-size:13px;color:var(--text-muted);margin-top:2px">
                Analyse des flux de télécompensation
            </p>
        </div>
        <div class="flex gap-3">
            @foreach(['7' => '7 derniers jours', '30' => '30 jours', '90' => '3 mois'] as $val => $label)
                <button wire:click="$set('periode','{{ $val }}')"
                    style="font-size:12px;padding:6px 16px;border-radius:20px;cursor:pointer;
                    border:1px solid {{ $periode === $val ? 'var(--gold)' : 'var(--border)' }};
                    background:{{ $periode === $val ? 'var(--gold-dim)' : 'transparent' }};
                    color:{{ $periode === $val ? 'var(--gold-light)' : 'var(--text-muted)' }};
                    font-family:var(--font-body);transition:all 0.15s">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid-4 mb-6">
        <div class="stat-card gold">
            <div class="stat-label">Total fichiers</div>
            <div class="stat-value gold">{{ $kpis['total_fichiers'] }}</div>
            <div class="stat-sub">Période sélectionnée</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Total transactions</div>
            <div class="stat-value blue">{{ $kpis['total_transactions'] }}</div>
            <div class="stat-sub">Toutes opérations</div>
        </div>
        <div class="stat-card" style="border-top-color:var(--green)">
            <div class="stat-label">Montant total</div>
            <div class="stat-value green" style="font-size:22px">
                {{ number_format($kpis['montant_total'], 3, ',', ' ') }}
            </div>
            <div class="stat-sub">TND</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Total rejets</div>
            <div class="stat-value red">{{ $kpis['total_rejets'] }}</div>
            <div class="stat-sub">Taux : {{ $kpis['taux_rejet'] }}%</div>
        </div>
    </div>

    {{-- Courbe évolution quotidienne --}}
    <div class="card mb-5">
        <div class="flex items-center justify-between mb-4">
            <div style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary)">
                Évolution quotidienne des transactions
            </div>
            <div class="flex gap-3" style="font-size:11px;color:var(--text-muted)">
                <span style="display:flex;align-items:center;gap:5px">
                    <span style="width:20px;height:2px;background:var(--blue-acc);border-radius:2px;display:inline-block"></span>
                    Validées
                </span>
                <span style="display:flex;align-items:center;gap:5px">
                    <span style="width:20px;height:2px;background:var(--red);border-radius:2px;display:inline-block;border-top:2px dashed var(--red)"></span>
                    Rejetées
                </span>
            </div>
        </div>
        <div style="position:relative;height:220px">
            <canvas id="chartEvolution"></canvas>
        </div>
    </div>

    {{-- Donut + Barres --}}
    <div class="grid-2 mb-5">
        <div class="card">
            <div style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
                Transactions par statut
            </div>
            <div style="position:relative;height:220px">
                <canvas id="chartStatut"></canvas>
            </div>
        </div>
        <div class="card">
            <div style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
                Répartition par type de valeur
            </div>
            <div style="position:relative;height:220px">
                <canvas id="chartTypes"></canvas>
            </div>
        </div>
    </div>

    {{-- Taux de rejet --}}
    <div class="card">
        <div style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:16px">
            Évolution du taux de rejet (%)
        </div>
        <div style="position:relative;height:160px">
            <canvas id="chartRejets"></canvas>
        </div>
    </div>

    {{-- Données JSON pour JS --}}
    <script>
        window._statsData = {
            evolution : @json($chartEvolution),
            statut    : @json($chartStatut),
            types     : @json($chartTypes),
            rejets    : @json($chartRejets),
        };
    </script>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
let charts = {};

function initCharts(data) {
    const grid = 'rgba(255,255,255,0.05)';
    const tick  = '#4a5568';

    Object.values(charts).forEach(c => c.destroy());
    charts = {};

    // 1. Courbe évolution quotidienne
    charts.evolution = new Chart(document.getElementById('chartEvolution'), {
        type: 'line',
        data: {
            labels: data.evolution.map(d => d.date),
            datasets: [
                {
                    label: 'Validées',
                    data: data.evolution.map(d => d.valides),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true, tension: 0.4, pointRadius: 4,
                    pointBackgroundColor: '#3b82f6', borderWidth: 2,
                },
                {
                    label: 'Rejetées',
                    data: data.evolution.map(d => d.rejetes),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.06)',
                    fill: true, tension: 0.4, pointRadius: 4,
                    pointBackgroundColor: '#ef4444', borderWidth: 2,
                    borderDash: [5, 3],
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                x: {
                    grid: { color: grid },
                    ticks: { color: tick, font: { size: 11, family: 'DM Sans' } }
                },
                y: {
                    grid: { color: grid },
                    ticks: { color: tick, font: { size: 11, family: 'DM Sans' } },
                    beginAtZero: true
                }
            }
        }
    });

    // 2. Donut statut
    charts.statut = new Chart(document.getElementById('chartStatut'), {
        type: 'doughnut',
        data: {
            labels: ['Validées', 'Rejetées'],
            datasets: [{
                data: [data.statut.valides, data.statut.rejetes],
                backgroundColor: ['#22c55e', '#ef4444'],
                borderColor: '#141928',
                borderWidth: 3,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#8892a4', font: { size: 11, family: 'DM Sans' }, padding: 16 }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = data.statut.valides + data.statut.rejetes;
                            const pct   = Math.round(ctx.raw / total * 100);
                            return `  ${ctx.label} : ${ctx.raw} (${pct}%)`;
                        }
                    }
                }
            }
        }
    });

    // 3. Barres horizontales types de valeur
    charts.types = new Chart(document.getElementById('chartTypes'), {
        type: 'bar',
        data: {
            labels: data.types.map(t => t.label),
            datasets: [{
                data: data.types.map(t => t.value),
                backgroundColor: [
                    '#c9a84c','#3b82f6','#22c55e',
                    '#ef4444','#a78bfa','#06b6d4','#f97316'
                ],
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: grid },
                    ticks: { color: tick, font: { size: 11, family: 'DM Sans' } },
                    beginAtZero: true
                },
                y: {
                    grid: { display: false },
                    ticks: { color: '#8892a4', font: { size: 11, family: 'DM Sans' } }
                }
            }
        }
    });

    // 4. Courbe taux de rejet
    charts.rejets = new Chart(document.getElementById('chartRejets'), {
        type: 'line',
        data: {
            labels: data.rejets.map(d => d.date),
            datasets: [{
                data: data.rejets.map(d => d.taux),
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,0.08)',
                fill: true, tension: 0.4,
                pointBackgroundColor: '#ef4444',
                pointRadius: 4, borderWidth: 2,
                borderDash: [5, 3],
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    grid: { color: grid },
                    ticks: { color: tick, font: { size: 11, family: 'DM Sans' } }
                },
                y: {
                    grid: { color: grid },
                    ticks: {
                        color: tick,
                        font: { size: 11, family: 'DM Sans' },
                        callback: v => v + '%'
                    },
                    min: 0
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (window._statsData) initCharts(window._statsData);
});

document.addEventListener('livewire:navigated', () => {
    if (window._statsData) initCharts(window._statsData);
});

Livewire.on('stats-updated', (payload) => {
    initCharts(payload[0]);
});
</script>
@endpush