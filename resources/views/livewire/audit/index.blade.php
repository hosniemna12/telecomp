<div>
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px">
        <div>
            <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)">Journal d'audit</h1>
            <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Traçabilité complète des actions système</p>
        </div>
    </div>

    <div class="grid-4" style="margin-bottom:24px">
        <div class="stat-card gold">
            <div class="stat-label">Total événements</div>
            <div class="stat-value gold">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Succès</div>
            <div class="stat-value green">{{ $stats['success'] ?? 0 }}</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Échecs</div>
            <div class="stat-value red">{{ $stats['failed'] ?? 0 }}</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Aujourd'hui</div>
            <div class="stat-value blue">{{ $stats['today'] ?? 0 }}</div>
        </div>
    </div>

    <div class="table-wrap">
        <div class="table-head">
            <span class="table-title">Événements système</span>
            <div style="display:flex;gap:10px">
                <div class="input-wrap" style="width:220px">
                    <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input wire:model.live="recherche" type="text" class="input" placeholder="Rechercher...">
                </div>
                <select wire:model.live="action" class="input" style="width:130px">
                    <option value="">Toutes actions</option>
                    <option value="LOGIN">Connexion</option>
                    <option value="UPLOAD">Upload</option>
                    <option value="TRAITEMENT">Traitement</option>
                    <option value="EXPORT">Export</option>
                </select>
                <select wire:model.live="statut" class="input" style="width:120px">
                    <option value="">Tous statuts</option>
                    <option value="SUCCESS">Succès</option>
                    <option value="FAILED">Échec</option>
                </select>
                <button wire:click="reinitialiser" class="btn btn-secondary btn-sm">Réinitialiser</button>
            </div>
        </div>
        <table>
            <thead>
                <tr><th>Utilisateur</th><th>Action</th><th>Description</th><th>Module</th><th>Statut</th><th>IP</th><th>Date</th></tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--gold-dim);border:1px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--gold-light)">
                            {{ strtoupper(substr($log->user_email ?? 'S', 0, 1)) }}
                        </div>
                        <span style="font-size:12px;color:var(--text-secondary)">{{ $log->user_email ?? 'Système' }}</span>
                    </div>
                </td>
                <td><span class="badge badge-blue">{{ $log->action ?? '—' }}</span></td>
                <td style="font-size:12px;color:var(--text-secondary);max-width:300px">{{ Str::limit($log->description ?? '—', 60) }}</td>
                <td style="font-size:12px;color:var(--text-muted)">{{ $log->module ?? '—' }}</td>
                <td>
                    @if(($log->statut ?? '') === 'SUCCESS')
                        <span class="badge badge-success">Succès</span>
                    @elseif(($log->statut ?? '') === 'FAILED')
                        <span class="badge badge-danger">Échec</span>
                    @else
                        <span class="badge badge-muted">{{ $log->statut ?? '—' }}</span>
                    @endif
                </td>
                <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">{{ $log->ip_address ?? '—' }}</td>
                <td style="font-size:11px;color:var(--text-muted)">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun événement trouvé</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($logs->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $logs->links() }}</div>
        @endif
    </div>
</div>