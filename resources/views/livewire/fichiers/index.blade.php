<div>
<div class="page-header">
    <div>
        <h1 class="page-title">Fichiers de télécompensation</h1>
        <p class="page-subtitle">Gestion et suivi des fichiers T24 importés</p>
    </div>
    <a href="{{ route('fichiers.upload') }}" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Importer un fichier
    </a>
</div>

<!-- STATS -->
<div class="grid-3 mb-6">
    <div class="stat-card gold">
        <div class="stat-label">Total fichiers</div>
        <div class="stat-value gold">{{ $stats['total'] ?? 0 }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Traités avec succès</div>
        <div class="stat-value green">{{ $stats['traites'] ?? 0 }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Erreurs</div>
        <div class="stat-value red">{{ $stats['erreurs'] ?? 0 }}</div>
    </div>
</div>

<!-- TABLE -->
<div class="table-wrap">
    <div class="table-head">
        <span class="table-title">{{ $stats['total'] ?? 0 }} fichier(s) trouvé(s)</span>
        <div class="filter-bar">
            <div class="input-wrap" style="width:300px">
                <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input wire:model.live="search" type="text" class="input" placeholder="Rechercher par nom de fichier...">
            </div>
            <select wire:model.live="filtreStatut" class="input" style="width:140px">
                <option value="">Tous statuts</option>
                <option value="TRAITE">Traité</option>
                <option value="ERREUR">Erreur</option>
                <option value="EN_COURS">En cours</option>
            </select>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Fichier</th>
                <th>Type</th>
                <th>Transactions</th>
                <th>Rejets</th>
                <th>Statut</th>
                <th>Date réception</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($fichiers as $f)
            @php
                $typeNames = ["10"=>"Virement","20"=>"Prélèvement","30"=>"Chèque","31"=>"CNP","32"=>"ARP","40"=>"LDC","84"=>"Papillon"];
                $tv = $f->type_valeur ?? "—";
                $typeName = $typeNames[$tv] ?? $tv; $typeColor = ["10"=>"gold","20"=>"blue","30"=>"green","31"=>"warning","32"=>"warning","40"=>"blue","84"=>"gold"][$tv] ?? "muted";
            @endphp
            <tr>
                <td class="filename">
                    <a href="{{ route('fichiers.show', $f->id) }}">{{ $f->nom_fichier }}</a>
                </td>
                <td><span class="badge badge-{{ $typeColor }}">{{ $typeName }}</span></td>
                <td style="font-family:var(--font-display);font-weight:600;color:var(--text-primary)">
                    {{ $f->enregistrements_details_count ?? 0 }}
                </td>
                <td>
                    @if(($f->rejets_count ?? 0) > 0)
                        <span style="color:var(--red);font-weight:600">{{ $f->rejets_count }}</span>
                    @else
                        <span class="text-muted">0</span>
                    @endif
                </td>
                <td>
                    @if($f->statut === 'TRAITE')
                        <span class="badge badge-success">Traité</span>
                    @elseif($f->statut === 'ERREUR')
                        <span class="badge badge-danger">Erreur</span>
                    @elseif($f->statut === 'EN_COURS')
                        <span class="badge badge-warning">En cours</span>
                    @else
                        <span class="badge badge-muted">{{ $f->statut }}</span>
                    @endif
                </td>
                <td class="text-muted text-sm">{{ $f->created_at?->format('d/m/Y H:i') }}</td>
                <td>
                    <div class="flex gap-3">
                        <a href="{{ route('fichiers.show', $f->id) }}" class="btn btn-ghost btn-sm">Détails</a>
                        @if($f->xmlProduit)
                        <a href="{{ route('fichiers.xml', $f->id) }}" class="btn btn-secondary btn-sm">XML</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7">
                <div class="empty-state">
                    <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <div class="empty-title">Aucun fichier trouvé</div>
                    <div class="empty-sub">Importez votre premier fichier T24 pour commencer</div>
                </div>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($fichiers->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $fichiers->links() }}
    </div>
    @endif
</div>
</div>
