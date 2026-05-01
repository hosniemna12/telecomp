<div>
<div class="page-header">
    <div>
        <h1 class="page-title">Messages de retour pacs.004</h1>
        <p class="page-subtitle">Génération des fichiers XML de retour de paiement — Payment Return ISO 20022</p>
    </div>
</div>

<div class="grid-4 mb-6">
    <div class="stat-card red">
        <div class="stat-label">Fichiers avec rejets</div>
        <div class="stat-value red">{{ $stats['fichiers_rejet'] ?? 0 }}</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Pacs.004 générés</div>
        <div class="stat-value gold">{{ $stats['total_generes'] ?? 0 }}</div>
    </div>
    <div class="stat-card yellow" style="">
        <div class="stat-label">En attente d'envoi</div>
        <div class="stat-value" style="color:var(--yellow)">{{ $stats['en_attente'] ?? 0 }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Envoyés</div>
        <div class="stat-value green">{{ $stats['envoyes'] ?? 0 }}</div>
    </div>
</div>

<!-- Fichiers avec rejets -->
<div class="table-wrap mb-5">
    <div class="table-head">
        <span class="table-title">Fichiers avec rejets — Générer pacs.004</span>
        <div class="flex gap-3">
            <div class="input-wrap" style="width:240px">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input wire:model.live="search" type="text" class="input" placeholder="Rechercher un fichier...">
            </div>
            <select wire:model.live="filtreType" class="input" style="width:140px">
                <option value="">Tous types</option>
                <option value="10">Virement</option>
                <option value="20">Prélèvement</option>
                <option value="30">Chèque</option>
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Fichier</th><th>Type</th><th>Nb rejets</th><th>Statut</th><th>Date</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fichiers as $f)
            <tr>
                <td style="font-family:monospace;font-size:11px;color:var(--gold)">{{ Str::limit($f->nom_fichier, 35) }}</td>
                <td>
                    @php $types=['10'=>'Virement','20'=>'Prélèvement','30'=>'Chèque','31'=>'CNP','32'=>'ARP','40'=>'LDC','84'=>'Papillon']; @endphp
                    <span class="badge badge-gold">{{ $types[$f->type_valeur] ?? $f->type_valeur }}</span>
                </td>
                <td style="color:var(--red);font-weight:600;font-family:var(--font-display)">{{ $f->rejets_count ?? 0 }}</td>
                <td><span class="badge badge-danger">Rejeté</span></td>
                <td class="text-muted text-sm">{{ $f->created_at?->format('d/m/Y') }}</td>
                <td>
                    <button wire:click="confirmerGeneration({{ $f->id }})" class="btn btn-primary btn-sm">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                        Générer pacs.004
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="6">
                <div class="empty-state">
                    <div class="empty-title">Aucun fichier avec des rejets</div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pacs.004 générés -->
<div class="table-wrap">
    <div class="table-head">
        <span class="table-title">Pacs.004 générés</span>
        <select wire:model.live="filtreStatutPacs" class="input" style="width:140px">
            <option value="">Tous statuts</option>
            <option value="GENERE">Généré</option>
            <option value="ENVOYE">Envoyé</option>
        </select>
    </div>
    <table>
        <thead>
            <tr>
                <th>Message ID</th><th>Fichier source</th><th>Nb Txs</th><th>XSD</th><th>Statut</th><th>Date</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pacs004List as $p)
            <tr>
                <td style="font-family:monospace;font-size:11px;color:var(--text-primary)">{{ $p->msg_id }}</td>
                <td style="font-family:monospace;font-size:11px;color:var(--gold)">{{ Str::limit($p->fichier->nom_fichier ?? '—', 30) }}</td>
                <td style="font-family:var(--font-display);font-weight:600">{{ $p->nb_transactions ?? 0 }}</td>
                <td><span class="badge badge-blue">pacs.004.001.11</span></td>
                <td>
                    @if($p->statut === 'ENVOYE')
                        <span class="badge badge-success">Envoyé</span>
                    @else
                        <span class="badge badge-gold">Généré</span>
                    @endif
                </td>
                <td class="text-muted text-sm">{{ $p->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                    <div class="flex gap-3">
                        <a href="{{ route('pacs004.telecharger', $p->id) }}" class="btn btn-secondary btn-sm">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            XML
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <div class="empty-title">Aucun pacs.004 généré</div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($showModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:1000;display:flex;align-items:center;justify-content:center">
    <div class="card" style="width:480px;padding:28px">
        <div style="font-size:16px;font-weight:700;color:var(--text-primary);margin-bottom:8px">Confirmer la génération</div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:20px">
            Voulez-vous générer le fichier pacs.004 pour ce fichier ?<br>
            Les transactions rejetées seront incluses dans le fichier de retour SIBTEL.
        </div>
        @if($messageSucces)
        <div style="background:var(--green-dim);border:1px solid rgba(34,197,94,0.2);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:14px;color:var(--green);font-size:12px">✓ {{ $messageSucces }}</div>
        @endif
        @if($messageErreur)
        <div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:14px;color:var(--red);font-size:12px">✗ {{ $messageErreur }}</div>
        @endif
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button wire:click="annuler" class="btn btn-secondary">Annuler</button>
            <button wire:click="generer" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Générer pacs.004
            </button>
        </div>
    </div>
</div>
@endif
</div>
