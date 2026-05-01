<div>
<style>
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px}
.page-title{font-family:var(--font-display);font-size:22px;font-weight:700;color:var(--text-primary)}
.comment-item{display:flex;gap:10px;padding:12px 0;border-bottom:1px solid var(--border)}
.comment-avatar{width:30px;height:30px;border-radius:50%;background:var(--gold-dim);border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--gold-light);flex-shrink:0}
.validation-bar{padding:16px 20px;border-radius:var(--radius);margin-bottom:20px;border:1px solid}
.validation-bar.attente{background:var(--yellow-dim);border-color:rgba(245,158,11,0.3)}
.validation-bar.valide{background:var(--green-dim);border-color:rgba(34,197,94,0.3)}
.validation-bar.rejete{background:var(--red-dim);border-color:rgba(239,68,68,0.3)}
</style>

<div class="page-header">
    <div>
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:4px;font-family:monospace">{{ $fichier->nom_fichier }}</div>
        <h1 class="page-title">Détail du fichier</h1>
    </div>
    <div style="display:flex;gap:10px">
        <a href="{{ route('fichiers.index') }}" class="btn btn-secondary btn-sm">← Retour</a>
        @if($fichier->xmlProduit)
        <a href="{{ route('fichiers.xml', $fichier->id) }}" class="btn btn-secondary btn-sm">Voir XML</a>
        @endif
    </div>
</div>

{{-- BARRE DE VALIDATION --}}
@if($fichier->statut === 'EN_ATTENTE_VALIDATION')
<div class="validation-bar attente">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
        <div>
            <div style="font-weight:600;color:var(--yellow);font-size:14px">⏳ En attente de validation superviseur</div>
            <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">
                Ce fichier a été soumis par {{ $fichier->uploader->name ?? 'un opérateur' }} et attend votre validation pour générer le XML ISO 20022.
            </div>
        </div>
        @if(in_array(auth()->user()->role, ['superviseur','admin']))
        <div style="display:flex;gap:8px;flex-shrink:0">
            <button wire:click="ouvrirModalValidation" class="btn btn-primary btn-sm">✓ Valider</button>
            <button wire:click="ouvrirModalRejet" class="btn btn-danger btn-sm">✗ Rejeter</button>
        </div>
        @endif
    </div>
</div>
@elseif($fichier->statut === 'VALIDE')
<div class="validation-bar valide">
    <div style="font-weight:600;color:var(--green);font-size:14px">✓ Fichier validé</div>
    <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">
        Validé par {{ $fichier->valideur->name ?? '—' }} le {{ $fichier->date_validation?->format('d/m/Y à H:i') }}
    </div>
</div>
@elseif($fichier->statut === 'REJETE_VALIDATION')
<div class="validation-bar rejete">
    <div style="font-weight:600;color:var(--red);font-size:14px">✗ Fichier rejeté</div>
    <div style="font-size:12px;color:var(--text-secondary);margin-top:2px">
        Rejeté par {{ $fichier->valideur->name ?? '—' }} — Motif : {{ $fichier->commentaire_rejet }}
    </div>
    @if(auth()->user()->role === 'operateur' && $fichier->uploaded_by === auth()->id())
    <button wire:click="resoumettre" class="btn btn-secondary btn-sm" style="margin-top:8px">↩ Resoumettre après correction</button>
    @endif
</div>
@endif

{{-- INFOS GLOBALES --}}
<div class="grid-4" style="margin-bottom:20px">
    <div class="stat-card gold">
        <div class="stat-label">Statut</div>
        <div style="margin-top:8px">
            @if($fichier->statut==='TRAITE') <span class="badge badge-success">Traité</span>
            @elseif($fichier->statut==='EN_ATTENTE_VALIDATION') <span class="badge badge-warning">En attente</span>
            @elseif($fichier->statut==='VALIDE') <span class="badge badge-success">Validé</span>
            @elseif($fichier->statut==='REJETE_VALIDATION') <span class="badge badge-danger">Rejeté</span>
            @elseif($fichier->statut==='ERREUR') <span class="badge badge-danger">Erreur</span>
            @else <span class="badge badge-muted">{{ $fichier->statut }}</span>
            @endif
        </div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Transactions</div>
        <div class="stat-value blue" style="font-size:24px">{{ $fichier->nb_transactions }}</div>
    </div>
    <div class="stat-card {{ $fichier->nb_rejets > 0 ? 'red' : 'green' }}">
        <div class="stat-label">Rejets</div>
        <div class="stat-value {{ $fichier->nb_rejets > 0 ? 'red' : 'green' }}" style="font-size:24px">{{ $fichier->nb_rejets }}</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-label">Montant total</div>
        <div style="font-family:var(--font-display);font-size:16px;font-weight:700;color:var(--gold-light);margin-top:8px">
            {{ number_format($fichier->montant_total, 3, ',', ' ') }} TND
        </div>
    </div>
</div>

{{-- XML généré --}}
@if($fichier->xmlProduit)
<div class="card" style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:36px;height:36px;background:var(--gold-dim);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--gold)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary)">XML ISO 20022 généré</div>
            <div style="font-size:11px;color:var(--text-muted)">{{ $fichier->xmlProduit->type_message ?? 'pacs' }}</div>
        </div>
    </div>
    <a href="{{ route('fichiers.xml', $fichier->id) }}" class="btn btn-primary btn-sm">Voir XML</a>
</div>
@endif

{{-- COMMENTAIRES --}}
<div class="table-wrap" style="margin-bottom:20px">
    <div class="table-head">
        <span class="table-title">Commentaires ({{ $commentaires->count() }})</span>
    </div>
    <div style="padding:16px 20px">
        {{-- Ajouter commentaire --}}
        <div style="display:flex;gap:10px;margin-bottom:16px">
            <div class="comment-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div style="flex:1">
                <textarea wire:model="nouveauCommentaire"
                    style="width:100%;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-sm);padding:9px 14px;font-size:13px;color:var(--text-primary);font-family:var(--font-body);outline:none;resize:vertical;min-height:70px"
                    placeholder="Ajouter un commentaire..."
                    onfocus="this.style.borderColor='var(--gold)'"
                    onblur="this.style.borderColor='var(--border)'"></textarea>
                <button wire:click="ajouterCommentaire" class="btn btn-secondary btn-sm" style="margin-top:6px">
                    Commenter
                </button>
            </div>
        </div>

        {{-- Liste commentaires --}}
        @forelse($commentaires as $c)
        <div class="comment-item">
            <div class="comment-avatar">{{ strtoupper(substr($c->user->name ?? 'S', 0, 1)) }}</div>
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ $c->user->name ?? 'Système' }}</span>
                    @php $roleColors=['admin'=>'danger','superviseur'=>'gold','operateur'=>'blue','lecteur'=>'muted']; @endphp
                    <span class="badge badge-{{ $roleColors[$c->user->role ?? ''] ?? 'muted' }}" style="font-size:9px;padding:1px 6px">{{ ucfirst($c->user->role ?? '—') }}</span>
                    @if($c->type === 'VALIDATION') <span class="badge badge-success" style="font-size:9px;padding:1px 6px">Validation</span>
                    @elseif($c->type === 'REJET') <span class="badge badge-danger" style="font-size:9px;padding:1px 6px">Rejet</span>
                    @endif
                    <span style="font-size:11px;color:var(--text-muted);margin-left:auto">{{ $c->created_at?->diffForHumans() }}</span>
                </div>
                <div style="font-size:13px;color:var(--text-secondary)">{{ $c->contenu }}</div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:20px;color:var(--text-muted);font-size:13px">Aucun commentaire</div>
        @endforelse
    </div>
</div>

{{-- MODAL VALIDATION --}}
@if($showModalValidation)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px)">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;width:100%;max-width:440px">
        <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:16px">✓ Valider le fichier</div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">
            Vous allez valider <strong style="color:var(--text-primary)">{{ $fichier->nom_fichier }}</strong> et autoriser la génération du XML ISO 20022.
        </div>
        <label style="display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:5px">Commentaire (optionnel)</label>
        <textarea wire:model="commentaireValidation"
            style="width:100%;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-sm);padding:9px 14px;font-size:13px;color:var(--text-primary);font-family:var(--font-body);outline:none;resize:none;height:80px"
            placeholder="Commentaire de validation..."></textarea>
        <div style="display:flex;gap:10px;margin-top:16px">
            <button wire:click="confirmerValidation" class="btn btn-primary" style="flex:1;justify-content:center">✓ Confirmer la validation</button>
            <button wire:click="$set('showModalValidation', false)" class="btn btn-secondary">Annuler</button>
        </div>
    </div>
</div>
@endif

{{-- MODAL REJET --}}
@if($showModalRejet)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px)">
    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;width:100%;max-width:440px">
        <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:16px">✗ Rejeter le fichier</div>
        <div style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">
            Indiquez le motif de rejet pour <strong style="color:var(--text-primary)">{{ $fichier->nom_fichier }}</strong>.
        </div>
        <label style="display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:5px">Motif du rejet <span style="color:var(--red)">*</span></label>
        <textarea wire:model="motifRejet"
            style="width:100%;background:var(--bg-input);border:1px solid var(--border);border-radius:var(--radius-sm);padding:9px 14px;font-size:13px;color:var(--text-primary);font-family:var(--font-body);outline:none;resize:none;height:80px"
            placeholder="Ex: Montant total incorrect, RIBs invalides..."></textarea>
        <div style="display:flex;gap:10px;margin-top:16px">
            <button wire:click="confirmerRejet" class="btn btn-danger" style="flex:1;justify-content:center">✗ Confirmer le rejet</button>
            <button wire:click="$set('showModalRejet', false)" class="btn btn-secondary">Annuler</button>
        </div>
    </div>
</div>
@endif

</div>
