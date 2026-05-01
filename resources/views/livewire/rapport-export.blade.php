<div>
<style>
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px}
.page-title{font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)}
.page-subtitle{font-size:13px;color:var(--text-muted);margin-top:4px}
.format-card{padding:16px 20px;background:var(--bg-input);border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:all 0.15s;text-align:center}
.format-card.selected{border-color:var(--gold);background:var(--gold-dim)}
.format-card:hover{border-color:rgba(201,168,76,0.4)}
.format-icon{font-size:28px;margin-bottom:8px}
.format-label{font-size:13px;font-weight:600;color:var(--text-primary)}
.format-desc{font-size:11px;color:var(--text-muted);margin-top:4px}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Export Rapports</h1>
        <p class="page-subtitle">Générer des rapports PDF ou Excel de télécompensation</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- Formulaire --}}
    <div class="card">
        <div class="table-title" style="margin-bottom:20px">Paramètres du rapport</div>

        {{-- Type de période --}}
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:6px">Type de rapport</label>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
                @foreach(['journalier'=>'Journalier','hebdomadaire'=>'Hebdomadaire','mensuel'=>'Mensuel'] as $val=>$label)
                <div class="format-card {{ $type === $val ? 'selected' : '' }}" wire:click="$set('type','{{ $val }}')">
                    <div style="font-size:12px;font-weight:600;color:var(--text-primary)">{{ $label }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Date --}}
        <div style="margin-bottom:16px">
            <label style="display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:6px">Date de référence</label>
            <input wire:model="date" type="date" class="input" style="width:100%">
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                @if($type === 'journalier') Le rapport couvrira uniquement cette journée
                @elseif($type === 'hebdomadaire') Le rapport couvrira la semaine contenant cette date
                @else Le rapport couvrira le mois contenant cette date
                @endif
            </div>
        </div>

        {{-- Format --}}
        <div style="margin-bottom:20px">
            <label style="display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:8px">Format d'export</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div class="format-card {{ $format === 'pdf' ? 'selected' : '' }}" wire:click="$set('format','pdf')">
                    <div class="format-icon">📄</div>
                    <div class="format-label">PDF</div>
                    <div class="format-desc">Rapport formaté, imprimable, avec signature électronique</div>
                </div>
                <div class="format-card {{ $format === 'xlsx' ? 'selected' : '' }}" wire:click="$set('format','xlsx')">
                    <div class="format-icon">📊</div>
                    <div class="format-label">Excel</div>
                    <div class="format-desc">4 feuilles : tableau de bord, fichiers, transactions, rejets</div>
                </div>
            </div>
        </div>

        @if($erreur)
        <div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:16px;color:var(--red);font-size:12px">
            ✗ {{ $erreur }}
        </div>
        @endif

        @if($succes)
        <div style="background:var(--green-dim);border:1px solid rgba(34,197,94,0.2);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:16px;color:var(--green);font-size:12px">
            ✓ {{ $succes }}
        </div>
        @endif

        <button wire:click="generer" wire:loading.attr="disabled"
            class="btn btn-primary" style="width:100%;justify-content:center;padding:13px">
            <span wire:loading.remove>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:6px">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Générer et télécharger
            </span>
            <span wire:loading>⏳ Génération en cours...</span>
        </button>

        @if($succes)
        <a href="{{ route('rapport.telecharger') }}" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:8px">
            ↓ Télécharger le rapport
        </a>
        @endif
    </div>

    {{-- Aperçu contenu --}}
    <div class="card">
        <div class="table-title" style="margin-bottom:16px">Contenu du rapport</div>

        <div style="display:flex;flex-direction:column;gap:10px">
            @foreach([
                ['📋','Récapitulatif général','Total fichiers, transactions, montants, taux de rejet'],
                ['📊','Répartition par type','Virements, prélèvements, chèques, LDC, papillons'],
                ['📁','Listing fichiers','Tous les fichiers avec statut, uploadeur, valideur'],
                ['💳','Listing transactions','RIB donneur/bénéficiaire, montant, statut (Excel seulement)'],
                ['⚠️','Récapitulatif rejets','Code rejet, motif, étape de détection'],
                ['🔏','Signature électronique','Date, heure, utilisateur (PDF seulement)'],
            ] as [$icon, $titre, $desc])
            <div style="display:flex;gap:10px;padding:10px;background:var(--bg-input);border-radius:var(--radius-sm)">
                <span style="font-size:18px;flex-shrink:0">{{ $icon }}</span>
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ $titre }}</div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $desc }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('rapport-pret', ({ format }) => {
        window.location.href = '/rapport/telecharger';
    });
});
</script>
</div>
