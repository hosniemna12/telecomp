<div>
<div class="page-header">
    <div>
        <h1 class="page-title">Gestion des rejets</h1>
        <p class="page-subtitle">Supervision et traitement des anomalies détectées</p>
    </div>
</div>

<div class="grid-4 mb-6" style="grid-template-columns:repeat(5,1fr)">
    <div class="stat-card gold">
        <div class="stat-label">Total rejets</div>
        <div class="stat-value gold">{{ $stats['total'] ?? 0 }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Non traités</div>
        <div class="stat-value red">{{ $stats['non_traites'] ?? 0 }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Traités</div>
        <div class="stat-value green">{{ $stats['traites'] ?? 0 }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Parsing</div>
        <div class="stat-value blue">{{ $stats['parsing'] ?? 0 }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Validation</div>
        <div class="stat-value blue">{{ $stats['validation'] ?? 0 }}</div>
    </div>
</div>

<div class="table-wrap">
    <div class="table-head">
        <span class="table-title">Liste des anomalies</span>
        <div class="flex gap-3">
            <div class="input-wrap" style="width:260px">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input wire:model.live="search" type="text" class="input" placeholder="Rechercher par code ou motif...">
            </div>
            <select wire:model.live="filtreEtape" class="input" style="width:140px">
                <option value="">Toutes les étapes</option>
                <option value="PARSING">Parsing</option>
                <option value="VALIDATION">Validation</option>
            </select>
            <select wire:model.live="filtreStatut" class="input" style="width:120px">
                <option value="">Tous</option>
                <option value="en_attente">En attente</option>
                <option value="traite">Traité</option>
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Fichier</th>
                <th>Code rejet</th>
                <th>Motif</th>
                <th>Étape</th>
                <th>Transaction</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rejets as $r)
            <tr>
                <td style="font-family:monospace;font-size:11px;color:var(--gold)">
                    {{ Str::limit($r->fichier->nom_fichier ?? '—', 28) }}
                </td>
                <td>
                    <span class="badge badge-danger">{{ $r->code_rejet ?? 'VALID_ERR' }}</span>
                </td>
                <td style="font-size:12px;color:var(--text-secondary);max-width:280px">
                    {{ Str::limit($r->motif_rejet ?? '—', 60) }}
                </td>
                <td>
                    <span class="badge badge-blue">{{ $r->etape_detection ?? '—' }}</span>
                </td>
                <td style="font-family:monospace;font-size:11px;color:var(--text-muted)">
                    {{ Str::limit($r->detail->numero_virement ?? "—", 20) }}
                </td>
                <td>
                    @if(!$r->traite)
                        <span class="badge badge-warning">En attente</span>
                    @elseif($r->traite)
                        <span class="badge badge-success">Traité</span>
                    @else
                        <span class="badge badge-muted">{{ $r->traite ? "Traité" : "En attente" }}</span>
                    @endif
                </td>
                <td class="text-muted text-sm">{{ $r->created_at?->format('d/m H:i') }}</td>
                <td>
                    @if(!$r->traite)
                    <button wire:click="marquerTraite({{ $r->id }})" class="btn btn-secondary btn-sm">
                        Marquer traité
                    </button>
                    @else
                    <span class="text-muted text-xs">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8">
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <div class="empty-title">Aucun rejet trouvé</div>
                    <div class="empty-sub">Tous les fichiers ont été traités sans anomalie</div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if(isset($rejets) && $rejets->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $rejets->links() }}
    </div>
    @endif
</div>
</div>
