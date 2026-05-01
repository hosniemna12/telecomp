@extends('layouts.app')
@section('content')
<div>
<style>
.rapport-header{margin-bottom:32px}
.rapport-title{font-family:var(--font-display);font-size:28px;font-weight:700;color:var(--text-primary);margin-bottom:6px}
.rapport-subtitle{font-size:13px;color:var(--text-muted)}

.step-card{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);padding:24px;margin-bottom:16px}
.step-num{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;background:var(--gold-dim);border:1.5px solid var(--gold);border-radius:50%;font-family:var(--font-display);font-size:13px;font-weight:700;color:var(--gold-light);margin-right:10px;flex-shrink:0}
.step-label{font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary)}
.step-head{display:flex;align-items:center;margin-bottom:16px}

.type-btn{padding:9px 18px;background:var(--bg-input);border:1.5px solid var(--border);border-radius:var(--radius-sm);cursor:pointer;font-size:13px;color:var(--text-secondary);transition:all 0.15s;font-family:var(--font-body);font-weight:500}
.type-btn:hover{border-color:rgba(201,168,76,0.4);color:var(--text-primary)}
.type-btn.active{border-color:var(--gold);background:var(--gold-dim);color:var(--gold-light);font-weight:600}

.fmt-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.fmt-card{padding:18px;background:var(--bg-input);border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:all 0.15s;display:flex;align-items:center;gap:14px}
.fmt-card:hover{border-color:rgba(201,168,76,0.4)}
.fmt-card.active{border-color:var(--gold);background:var(--gold-dim)}
.fmt-icon{width:44px;height:44px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.fmt-icon.pdf{background:rgba(239,68,68,0.12)}
.fmt-icon.xlsx{background:rgba(34,197,94,0.12)}
.fmt-name{font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:3px}
.fmt-desc{font-size:11.5px;color:var(--text-muted)}

.preview-wrap{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.preview-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.preview-title{font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary)}
.preview-body{padding:0}
.preview-item{display:flex;align-items:center;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border);transition:background 0.15s}
.preview-item:last-child{border-bottom:none}
.preview-item:hover{background:rgba(255,255,255,0.02)}
.preview-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.preview-dot.gold{background:var(--gold)}
.preview-dot.blue{background:var(--blue-acc)}
.preview-dot.green{background:var(--green)}
.preview-dot.red{background:var(--red)}
.preview-dot.muted{background:var(--text-muted)}
.preview-item-title{font-size:13px;font-weight:500;color:var(--text-primary);margin-bottom:2px}
.preview-item-desc{font-size:11.5px;color:var(--text-muted)}
.preview-badge{margin-left:auto;font-size:10px;font-weight:600;padding:2px 8px;border-radius:10px;flex-shrink:0}
.preview-badge.pdf-only{background:rgba(239,68,68,0.12);color:var(--red)}
.preview-badge.xlsx-only{background:rgba(34,197,94,0.12);color:var(--green)}
.preview-badge.both{background:var(--gold-dim);color:var(--gold-light)}
</style>

<div class="rapport-header">
    <h1 class="rapport-title">Génération de rapports</h1>
    <p class="rapport-subtitle">Exportez les données de télécompensation en PDF officiel ou Excel analytique</p>
</div>

@if(session('error'))
<div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius);padding:12px 18px;margin-bottom:20px;color:var(--red);font-size:13px">
    ✗ {{ session('error') }}
</div>
@endif

<div style="display:grid;grid-template-columns:1.1fr 0.9fr;gap:20px;align-items:start">

    {{-- Colonne gauche : formulaire --}}
    <div>
        <form action="{{ route('rapport.generer') }}" method="GET" id="rapport-form">

            {{-- Étape 1 : Type --}}
            <div class="step-card">
                <div class="step-head">
                    <span class="step-num">1</span>
                    <span class="step-label">Sélectionner la période</span>
                </div>
                <div style="display:flex;gap:8px;margin-bottom:16px">
                    <button type="button" class="type-btn active" onclick="setType('journalier',this)">Journalier</button>
                    <button type="button" class="type-btn" onclick="setType('hebdomadaire',this)">Hebdomadaire</button>
                    <button type="button" class="type-btn" onclick="setType('mensuel',this)">Mensuel</button>
                </div>
                <input type="hidden" name="type" id="type-input" value="journalier">
                <div>
                    <label style="display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:6px">Date de référence</label>
                    <input type="date" name="date" class="input" value="{{ now()->format('Y-m-d') }}"
                        style="max-width:220px">
                    <div id="periode-hint" style="font-size:11px;color:var(--text-muted);margin-top:6px">
                        Le rapport couvrira uniquement cette journée
                    </div>
                </div>
            </div>

            {{-- Étape 2 : Format --}}
            <div class="step-card">
                <div class="step-head">
                    <span class="step-num">2</span>
                    <span class="step-label">Choisir le format d'export</span>
                </div>
                <div class="fmt-grid">
                    <div class="fmt-card active" id="fmt-pdf" onclick="setFormat('pdf')">
                        <div class="fmt-icon pdf">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="1.8">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </div>
                        <div>
                            <div class="fmt-name">PDF officiel</div>
                            <div class="fmt-desc">Rapport formaté, imprimable<br>Signature électronique BTL</div>
                        </div>
                    </div>
                    <div class="fmt-card" id="fmt-xlsx" onclick="setFormat('xlsx')">
                        <div class="fmt-icon xlsx">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="1.8">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <line x1="3" y1="9" x2="21" y2="9"/>
                                <line x1="3" y1="15" x2="21" y2="15"/>
                                <line x1="9" y1="3" x2="9" y2="21"/>
                            </svg>
                        </div>
                        <div>
                            <div class="fmt-name">Excel analytique</div>
                            <div class="fmt-desc">4 feuilles de données<br>Formules & filtres actifs</div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="format" id="format-input" value="pdf">
            </div>

            {{-- Étape 3 : Générer --}}
            <div class="step-card" style="background:var(--gold-dim);border-color:var(--gold)">
                <div class="step-head">
                    <span class="step-num">3</span>
                    <span class="step-label">Générer et télécharger</span>
                </div>
                <button type="submit" class="btn btn-primary" id="submit-btn"
                    style="width:100%;justify-content:center;padding:14px;font-size:14px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:8px">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Générer le rapport
                </button>
                <div style="font-size:11px;color:var(--gold);margin-top:10px;text-align:center">
                    Le fichier sera téléchargé automatiquement
                </div>
            </div>

        </form>
    </div>

    {{-- Colonne droite : aperçu contenu --}}
    <div class="preview-wrap">
        <div class="preview-head">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span class="preview-title">Contenu du rapport</span>
        </div>
        <div class="preview-body">
            <div class="preview-item">
                <span class="preview-dot gold"></span>
                <div>
                    <div class="preview-item-title">Récapitulatif général</div>
                    <div class="preview-item-desc">Fichiers, transactions, montants, taux de rejet</div>
                </div>
                <span class="preview-badge both">PDF + Excel</span>
            </div>
            <div class="preview-item">
                <span class="preview-dot gold"></span>
                <div>
                    <div class="preview-item-title">Répartition par type SIBTEL</div>
                    <div class="preview-item-desc">Virements, prélèvements, chèques, LDC, papillons</div>
                </div>
                <span class="preview-badge both">PDF + Excel</span>
            </div>
            <div class="preview-item">
                <span class="preview-dot blue"></span>
                <div>
                    <div class="preview-item-title">Listing des fichiers traités</div>
                    <div class="preview-item-desc">Statut, uploadeur, valideur, montant par fichier</div>
                </div>
                <span class="preview-badge both">PDF + Excel</span>
            </div>
            <div class="preview-item">
                <span class="preview-dot green"></span>
                <div>
                    <div class="preview-item-title">Détail des transactions</div>
                    <div class="preview-item-desc">RIB donneur/bénéficiaire, montants, motifs</div>
                </div>
                <span class="preview-badge xlsx-only">Excel uniquement</span>
            </div>
            <div class="preview-item">
                <span class="preview-dot red"></span>
                <div>
                    <div class="preview-item-title">Récapitulatif des rejets</div>
                    <div class="preview-item-desc">Code rejet, motif, étape de détection</div>
                </div>
                <span class="preview-badge both">PDF + Excel</span>
            </div>
            <div class="preview-item">
                <span class="preview-dot muted"></span>
                <div>
                    <div class="preview-item-title">Signature électronique</div>
                    <div class="preview-item-desc">Date, heure, nom de l'utilisateur BTL</div>
                </div>
                <span class="preview-badge pdf-only">PDF uniquement</span>
            </div>
        </div>
    </div>

</div>

<script>
function setType(val, btn) {
    document.getElementById('type-input').value = val;
    document.querySelectorAll('.type-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const hints = {
        'journalier': 'Le rapport couvrira uniquement cette journée',
        'hebdomadaire': 'Le rapport couvrira la semaine contenant cette date',
        'mensuel': 'Le rapport couvrira le mois contenant cette date'
    };
    document.getElementById('periode-hint').textContent = hints[val];
}
function setFormat(val) {
    document.getElementById('format-input').value = val;
    document.getElementById('fmt-pdf').classList.toggle('active', val === 'pdf');
    document.getElementById('fmt-xlsx').classList.toggle('active', val === 'xlsx');
}
document.getElementById('rapport-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.innerHTML = '⏳ Génération en cours...';
    btn.disabled = true;
    setTimeout(() => {
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:8px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Générer le rapport';
        btn.disabled = false;
    }, 8000);
});
</script>
</div>
@endsection
