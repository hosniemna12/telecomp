<div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px">
        <div>
            <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)">Tableau de bord</h1>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Supervision du système de télécompensation SIBTEL — BTL</p>
        </div>
        <a href="{{ route('fichiers.upload') }}" class="btn btn-primary">+ Nouveau fichier</a>
    </div>

    <div class="grid-4" style="margin-bottom:24px" wire:poll.10000ms>
        <div class="stat-card gold">
            <div class="stat-label">Fichiers reçus</div>
            <div class="stat-value gold">{{ $stats['fichiers'] ?? 0 }}</div>
            <div class="stat-sub">✓ {{ $stats['traites'] ?? 0 }} traités</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Transactions</div>
            <div class="stat-value blue">{{ $stats['transactions'] ?? 0 }}</div>
            <div class="stat-sub">{{ number_format($stats['montant_total'] ?? 0, 3, ',', ' ') }} TND</div>
        </div>
        <div class="stat-card {{ ($stats['rejets'] ?? 0) > 0 ? 'red' : 'green' }}">
            <div class="stat-label">Anomalies</div>
            <div class="stat-value {{ ($stats['rejets'] ?? 0) > 0 ? 'red' : 'green' }}">{{ $stats['rejets'] ?? 0 }}</div>
            <div class="stat-sub">{{ ($stats['rejets'] ?? 0) === 0 ? 'Aucune anomalie' : 'Rejets à traiter' }}</div>
        </div>
        <div class="stat-card gold">
            <div class="stat-label">XML ISO 20022</div>
            <div class="stat-value gold">{{ $stats['xml'] ?? 0 }}</div>
            <div class="stat-sub">Fichiers générés</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:24px">
        <div class="stat-card blue" style="padding:14px 16px">
            <div class="stat-label">Virements</div>
            <div class="stat-value blue" style="font-size:22px">{{ $statsParType['virements'] ?? 0 }}</div>
        </div>
        <div class="stat-card blue" style="padding:14px 16px">
            <div class="stat-label">Prélèvements</div>
            <div class="stat-value blue" style="font-size:22px">{{ $statsParType['prelevements'] ?? 0 }}</div>
        </div>
        <div class="stat-card green" style="padding:14px 16px">
            <div class="stat-label">Chèques</div>
            <div class="stat-value green" style="font-size:22px">{{ $statsParType['cheques'] ?? 0 }}</div>
        </div>
        <div class="stat-card gold" style="padding:14px 16px">
            <div class="stat-label">LDC</div>
            <div class="stat-value gold" style="font-size:22px">{{ $statsParType['ldc'] ?? 0 }}</div>
        </div>
        <div class="stat-card gold" style="padding:14px 16px">
            <div class="stat-label">Papillons</div>
            <div class="stat-value gold" style="font-size:22px">{{ $statsParType['papillons'] ?? 0 }}</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        <div class="table-wrap">
            <div class="table-head">
                <span class="table-title">Derniers fichiers traités</span>
                <a href="{{ route('fichiers.index') }}" class="btn btn-ghost btn-sm">Voir tout →</a>
            </div>
            <table>
                <thead><tr><th>Fichier</th><th>Type</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($derniersFichiers as $f)
                @php
                    $typeNames = ["10"=>"Virement","20"=>"Prélèv.","30"=>"Chèque","31"=>"CNP","32"=>"ARP","40"=>"LDC","84"=>"Papillon"];
                    $tv = $f->type_valeur ?? "—";
                @endphp
                <tr>
                    <td style="font-family:monospace;font-size:11px">
                        <a href="{{ route('fichiers.show', $f->id) }}" style="color:var(--gold);text-decoration:none">{{ Str::limit($f->nom_fichier, 28) }}</a>
                    </td>
                    <td><span class="badge badge-gold">{{ $typeNames[$tv] ?? $tv }}</span></td>
                    <td>
                        @if($f->statut==='TRAITE') <span class="badge badge-success">Traité</span>
                        @elseif($f->statut==='ERREUR') <span class="badge badge-danger">Erreur</span>
                        @else <span class="badge badge-warning">{{ $f->statut }}</span>
                        @endif
                    </td>
                    <td class="text-muted text-sm">{{ $f->created_at?->format('d/m H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--text-muted)">Aucun fichier</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="table-title" style="margin-bottom:16px">Activité récente</div>
            @forelse($derniersLogs as $log)
            <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
                <div style="width:26px;height:26px;border-radius:50%;background:var(--blue-dim);color:var(--blue-acc);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0">
                    {{ substr($log->etape??'I',0,1) }}
                </div>
                <div>
                    <div style="font-size:12px;color:var(--text-secondary)">{{ Str::limit($log->message,55) }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $log->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:var(--text-muted);padding:20px;font-size:13px">Aucune activité</div>
            @endforelse
        </div>
    </div>
</div>