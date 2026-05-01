<div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px">
        <div>
            <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)">Statistiques</h1>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Analyse des flux de télécompensation</p>
        </div>
        <select wire:model.live="periode" class="input" style="width:160px">
            <option value="7">7 derniers jours</option>
            <option value="30">30 derniers jours</option>
            <option value="90">90 derniers jours</option>
        </select>
    </div>

    <div class="grid-4" style="margin-bottom:24px">
        <div class="stat-card gold">
            <div class="stat-label">Total fichiers</div>
            <div class="stat-value gold">{{ $stats['total_fichiers'] ?? 0 }}</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Total transactions</div>
            <div class="stat-value blue">{{ $stats['total_transactions'] ?? 0 }}</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Montant total</div>
            <div class="stat-value green" style="font-size:20px">{{ number_format($stats['montant_total'] ?? 0, 3, ',', ' ') }}</div>
            <div class="stat-sub">TND</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Total rejets</div>
            <div class="stat-value red">{{ $stats['total_rejets'] ?? 0 }}</div>
            <div class="stat-sub">Taux : {{ $stats['taux_rejet'] ?? 0 }}%</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
        <div class="card">
            <div class="table-title" style="margin-bottom:16px">Répartition par type de valeur</div>
            @forelse($parType as $t)
            @php
                $typeNames = ["10"=>"Virement","20"=>"Prélèvement","30"=>"Chèque","31"=>"CNP","32"=>"ARP","40"=>"LDC","84"=>"Papillon"];
                $nom = $typeNames[$t->type_valeur] ?? $t->type_valeur;
            @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span style="font-size:13px;color:var(--text-secondary)">{{ $nom }}</span>
                <span style="font-family:var(--font-display);font-weight:700;color:var(--gold-light)">{{ $t->total }}</span>
            </div>
            @empty
            <div style="color:var(--text-muted);text-align:center;padding:20px">Aucune donnée</div>
            @endforelse
        </div>

        <div class="card">
            <div class="table-title" style="margin-bottom:16px">Transactions par statut</div>
            @forelse($transactionsParStatut as $tx)
            @php
                $colors = ['VALIDE'=>'green','REJETE'=>'red','EN_ATTENTE'=>'warning'];
                $col = $colors[$tx->statut] ?? 'muted';
            @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
                <span class="badge badge-{{ $col }}">{{ $tx->statut }}</span>
                <span style="font-family:var(--font-display);font-weight:700;color:var(--text-primary)">{{ $tx->total }}</span>
            </div>
            @empty
            <div style="color:var(--text-muted);text-align:center;padding:20px">Aucune donnée</div>
            @endforelse
        </div>
    </div>

    <div class="table-wrap">
        <div class="table-head">
            <span class="table-title">Top 5 transactions par montant</span>
        </div>
        <table>
            <thead><tr><th>N°</th><th>RIB Donneur</th><th>RIB Bénéficiaire</th><th>Montant</th><th>Statut</th></tr></thead>
            <tbody>
            @forelse($topTransactions as $i => $tx)
            <tr>
                <td style="color:var(--text-muted);font-size:12px">#{{ $i+1 }}</td>
                <td style="font-family:monospace;font-size:11px;color:var(--text-secondary)">{{ $tx->rib_donneur ?? '—' }}</td>
                <td style="font-family:monospace;font-size:11px;color:var(--text-secondary)">{{ $tx->rib_beneficiaire ?? '—' }}</td>
                <td style="font-family:var(--font-display);font-weight:700;color:var(--gold-light)">{{ number_format($tx->montant, 3, ',', ' ') }} TND</td>
                <td><span class="badge badge-success">{{ $tx->statut }}</span></td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-muted)">Aucune transaction</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>