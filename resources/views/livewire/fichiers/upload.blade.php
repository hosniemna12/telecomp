<div>
<style>
.type-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; }
.type-card {
    padding: 14px 16px;
    background: var(--bg-input);
    border: 2px solid var(--border);
    border-radius: var(--radius);
    cursor: pointer;
    transition: all 0.15s;
}
.type-card:hover { border-color: rgba(26,95,71,0.4); background: var(--gold-glow); }
.type-card.selected { border-color: var(--gold); background: var(--gold-dim); }
.type-card-code { font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-bottom:4px }
.type-card-name { font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:2px }
.type-card-desc { font-size:11px;color:var(--text-muted) }
.type-card-icon { width:32px;height:32px;background:var(--bg-card);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;margin-bottom:8px;color:var(--gold);font-weight:700;font-size:13px }
.type-card.selected .type-card-icon { background:var(--gold-dim) }
.drop-zone { border:2px dashed var(--border);border-radius:var(--radius);padding:48px 20px;text-align:center;cursor:pointer;transition:all 0.2s;background:var(--bg-input) }
.drop-zone:hover { border-color:var(--gold);background:var(--gold-glow) }
.drop-icon { width:48px;height:48px;background:var(--gold-dim);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:var(--gold) }
.upload-form { max-width:750px;margin:0 auto }
.btl-logo { margin-bottom:20px;text-align:center }
.btl-logo img { max-width:180px;height:auto }

/* ML Styles */
.ml-score-bar { background:var(--bg-input);border-radius:99px;height:12px;overflow:hidden;margin-top:12px }
.ml-score-fill { height:100%;border-radius:99px;transition:width 0.8s ease }
.ml-badge { padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;display:inline-block }
.ml-stat-box { border-radius:10px;padding:16px;text-align:center }
.ml-stat-number { font-size:26px;font-weight:800;line-height:1 }
.ml-stat-label { font-size:11px;color:var(--text-muted);margin-top:4px }

/* Top Raisons */
.ml-raison-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:10px 14px;
    background:var(--bg-input);
    border-radius:8px;
    border-left:3px solid;
    transition:all 0.2s;
}
.ml-raison-row:hover { transform: translateX(2px); }
.ml-raison-libelle { font-size:13px;font-weight:600;color:var(--text-primary) }
.ml-raison-detail { font-size:11px;color:var(--text-muted);margin-top:2px }
.ml-raison-badge { font-size:11px;font-weight:700;padding:4px 10px;border-radius:99px;white-space:nowrap }
</style>


    <div style="margin-bottom:28px">
        <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)">Importer un fichier T24</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Importez un fichier ENV ou PAK généré par Temenos T24</p>
    </div>

    <div class="upload-form">

        {{-- ÉTAPE 1 — Type de valeur --}}
        <div class="card" style="margin-bottom:20px">
            <div style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:14px">
                1 — Sélectionner le type de valeur
            </div>
            <div class="type-grid">
                @foreach([
                    ["10","Virement","Code 10 — 280 car.","V"],
                    ["20","Prélèvement","Code 20 — 200 car.","P"],
                    ["30","Chèque","Code 30 — 160 car.","C"],
                    ["31","CNP Partiel","Code 31 — 160 car.","N"],
                    ["32","ARP","Code 32 — 160 car.","A"],
                    ["40","Lettre de change","Code 40-43 — 380 car.","L"],
                    ["84","Papillon","Code 84 — 280 car.","B"],
                ] as [$code, $name, $desc, $icon])
                <div class="type-card {{ $typeValeur === $code ? 'selected' : '' }}"
                     wire:click="$set('typeValeur', '{{ $code }}')">
                    <div class="type-card-icon">{{ $icon }}</div>
                    <div class="type-card-code">Code {{ $code }}</div>
                    <div class="type-card-name">{{ $name }}</div>
                    <div class="type-card-desc">{{ $desc }}</div>
                </div>
                @endforeach
            </div>
            @error("typeValeur")
                <div style="color:var(--red);font-size:12px;margin-top:8px">{{ $message }}</div>
            @enderror
        </div>

        {{-- ÉTAPE 2 — Choisir le fichier --}}
        <div class="card" style="margin-bottom:20px">
            <div style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary);margin-bottom:14px">
                2 — Choisir le fichier
            </div>
            <div class="drop-zone" onclick="document.getElementById('file-input').click()">
                <div class="drop-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                </div>
                @if($fichier)
                    <div style="font-size:14px;font-weight:600;color:var(--gold-light)">{{ $fichier->getClientOriginalName() }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Cliquez pour changer</div>
                @else
                    <div style="font-size:14px;font-weight:500;color:var(--text-secondary)">Cliquez pour sélectionner un fichier</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">Formats acceptés : .ENV, .PAK — Max 10 MB</div>
                @endif
                <input id="file-input" type="file" wire:model="fichier" accept=".env,.pak,.ENV,.PAK" style="display:none">
            </div>
            @error("fichier")
                <div style="color:var(--red);font-size:12px;margin-top:8px">{{ $message }}</div>
            @enderror
        </div>

        {{-- Erreur --}}
        @if($erreur)
        <div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:12px;color:var(--red);font-size:13px">
            ✗ {{ $erreur }}
        </div>
        @endif

        {{-- Bouton traiter --}}
        <button wire:click="traiter" wire:loading.attr="disabled"
                class="btn btn-primary"
                style="width:100%;justify-content:center;padding:13px;font-size:14px">
            <span wire:loading.remove>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:6px">
                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
                Lancer le traitement
            </span>
            <span wire:loading>⏳ Traitement + Analyse IA en cours...</span>
        </button>

        {{-- ═══════════════════════════════════════════════════════ --}}
        {{-- RÉSULTATS APRÈS TRAITEMENT                             --}}
        {{-- ═══════════════════════════════════════════════════════ --}}
        @if(!empty($resultat) && $resultat['succes'])

        <div style="margin-top:28px">

            {{-- Succès header --}}
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px">
                <div style="width:36px;height:36px;background:#22C55E20;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px">✅</div>
                <div>
                    <div style="font-weight:700;color:var(--text-primary);font-size:15px">Fichier traité avec succès</div>
                    <div style="font-size:12px;color:var(--text-muted)">Statut : <strong>{{ $resultat['stats']['statut'] }}</strong></div>
                </div>
            </div>

            {{-- Stats transactions --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
                <div class="card" style="text-align:center;padding:20px">
                    <div style="font-size:32px;font-weight:800;color:#22C55E">{{ $resultat['stats']['valides'] }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">✓ Valides</div>
                </div>
                <div class="card" style="text-align:center;padding:20px">
                    <div style="font-size:32px;font-weight:800;color:#EF4444">{{ $resultat['stats']['rejetes'] }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">✗ Rejetées</div>
                </div>
                <div class="card" style="text-align:center;padding:20px">
                    <div style="font-size:32px;font-weight:800;color:var(--gold)">{{ $resultat['stats']['total'] }}</div>
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">∑ Total</div>
                </div>
            </div>

            {{-- ═══ BLOC IA ═══ --}}
            @if(!empty($resultat['ml']) && $resultat['ml']['disponible'])
            @php
                $ml             = $resultat['ml'];
                $score          = $ml['score_global'];
                $couleurGlobale = $score >= 70 ? 'rouge' : ($score >= 40 ? 'orange' : 'vert');
                $hex            = match($couleurGlobale) {
                    'rouge'  => '#EF4444',
                    'orange' => '#F59E0B',
                    default  => '#22C55E',
                };
                $label = match($couleurGlobale) {
                    'rouge'  => '⚠ Risque élevé de rejet',
                    'orange' => '⚡ Risque modéré',
                    default  => '✓ Risque faible',
                };
                $conseil = match($couleurGlobale) {
                    'rouge'  => 'Plusieurs transactions présentent un risque élevé. Vérifiez les RIBs et les provisions.',
                    'orange' => 'Certaines transactions méritent une attention particulière avant validation.',
                    default  => 'Le fichier présente un faible risque global de rejet.',
                };
            @endphp

            <div class="card" style="margin-bottom:16px;border:2px solid {{ $hex }}30;background:linear-gradient(135deg, var(--bg-card) 0%, {{ $hex }}08 100%)">

                {{-- Header IA --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
                    <div style="display:flex;align-items:center;gap:12px">

                        <div>
                            <div style="font-weight:700;color:var(--text-primary);font-size:15px">Analyse IA — Risque de Rejet</div>
                            <div style="font-size:11px;color:var(--text-muted)">Modèle RandomForest · {{ $ml['total'] }} transactions analysées</div>
                        </div>
                    </div>
                    <div class="ml-badge" style="background:{{ $hex }}20;color:{{ $hex }}">
                        {{ $label }}
                    </div>
                </div>

                {{-- Score global --}}
                <div style="text-align:center;margin-bottom:24px;padding:20px;background:{{ $hex }}10;border-radius:12px">
                    <div style="font-size:56px;font-weight:900;color:{{ $hex }};line-height:1;letter-spacing:-2px">
                        {{ $score }}<span style="font-size:28px;font-weight:600">%</span>
                    </div>
                    <div style="font-size:13px;color:var(--text-muted);margin-top:6px">Score de risque global</div>

                    {{-- Barre --}}
                    <div class="ml-score-bar" style="margin-top:14px;max-width:300px;margin-left:auto;margin-right:auto">
                        <div class="ml-score-fill" style="width:{{ $score }}%;background:{{ $hex }}"></div>
                    </div>

                    {{-- Conseil --}}
                    <div style="margin-top:12px;font-size:12px;color:{{ $hex }};font-style:italic">
                        {{ $conseil }}
                    </div>
                </div>

                {{-- Répartition par couleur --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px">
                    <div class="ml-stat-box" style="background:#EF444415;border:1px solid #EF444430">
                        <div class="ml-stat-number" style="color:#EF4444">{{ $ml['rouge'] }}</div>
                        <div class="ml-stat-label">🔴 Risque élevé<br><span style="font-size:10px">(score ≥ 70)</span></div>
                    </div>
                    <div class="ml-stat-box" style="background:#F59E0B15;border:1px solid #F59E0B30">
                        <div class="ml-stat-number" style="color:#F59E0B">{{ $ml['orange'] }}</div>
                        <div class="ml-stat-label">🟡 Risque modéré<br><span style="font-size:10px">(score 40–69)</span></div>
                    </div>
                    <div class="ml-stat-box" style="background:#22C55E15;border:1px solid #22C55E30">
                        <div class="ml-stat-number" style="color:#22C55E">{{ $ml['vert'] }}</div>
                        <div class="ml-stat-label">🟢 Risque faible<br><span style="font-size:10px">(score &lt; 40)</span></div>
                    </div>
                </div>

                {{-- Barre de répartition visuelle --}}
                @if($ml['total'] > 0)
                <div style="margin-bottom:16px">
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:6px">Répartition des transactions</div>
                    <div style="display:flex;height:8px;border-radius:99px;overflow:hidden;gap:2px">
                        @if($ml['rouge'] > 0)
                        <div style="flex:{{ $ml['rouge'] }};background:#EF4444;border-radius:99px 0 0 99px"></div>
                        @endif
                        @if($ml['orange'] > 0)
                        <div style="flex:{{ $ml['orange'] }};background:#F59E0B"></div>
                        @endif
                        @if($ml['vert'] > 0)
                        <div style="flex:{{ $ml['vert'] }};background:#22C55E;border-radius:0 99px 99px 0"></div>
                        @endif
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:10px;color:var(--text-muted);margin-top:4px">
                        <span>🔴 {{ $ml['rouge'] > 0 ? round($ml['rouge']/$ml['total']*100) : 0 }}%</span>
                        <span>🟡 {{ $ml['orange'] > 0 ? round($ml['orange']/$ml['total']*100) : 0 }}%</span>
                        <span>🟢 {{ $ml['vert'] > 0 ? round($ml['vert']/$ml['total']*100) : 0 }}%</span>
                    </div>
                </div>
                @endif

                {{-- ═══ TOP RAISONS IDENTIFIÉES ═══ --}}
                @if(!empty($ml['top_raisons']))
                <div style="margin-bottom:16px;padding:16px;background:var(--bg-input);border-radius:10px;border:1px solid var(--border)">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
                        <span style="font-size:16px">🔍</span>
                        <span style="font-size:13px;font-weight:700;color:var(--text-primary)">Principales raisons identifiées par l'IA</span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:8px">
                        @foreach($ml['top_raisons'] as $raison)
                            @php
                                $gravColor = match($raison['gravite'] ?? 'faible') {
                                    'haute'   => '#EF4444',
                                    'moyenne' => '#F59E0B',
                                    default   => '#6B7280',
                                };
                                $gravLabel = match($raison['gravite'] ?? 'faible') {
                                    'haute'   => 'Critique',
                                    'moyenne' => 'Modéré',
                                    default   => 'Faible',
                                };
                            @endphp
                            <div class="ml-raison-row" style="border-left-color:{{ $gravColor }}">
                                <div style="flex:1;min-width:0">
                                    <div class="ml-raison-libelle">{{ $raison['libelle'] }}</div>
                                    @if(!empty($raison['detail']))
                                        <div class="ml-raison-detail">{{ $raison['detail'] }}</div>
                                    @endif
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;margin-left:12px">
                                    <span class="ml-raison-badge" style="color:{{ $gravColor }};background:{{ $gravColor }}15">
                                        {{ $gravLabel }}
                                    </span>
                                    <span class="ml-raison-badge" style="color:var(--text-secondary);background:var(--bg-card)">
                                        {{ $raison['occurences'] }} txn{{ $raison['occurences'] > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Note technique --}}
                <div style="background:var(--bg-input);border-radius:8px;padding:10px 14px;font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:8px">
                    <span>ℹ️</span>
                    <span>L'estimation IA est indicative. Elle se base sur les caractéristiques des transactions historiques.</span>
                </div>
            </div>

            @else
            {{-- ML non disponible --}}
            <div style="background:var(--bg-card);border:1px dashed var(--border);border-radius:10px;padding:16px;text-align:center;margin-bottom:16px">
                <div style="font-size:24px;margin-bottom:8px">🤖</div>
                <div style="font-size:13px;color:var(--text-muted);font-weight:500">Serveur IA non disponible</div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:4px">
                    Lancez le serveur Flask : <code style="background:var(--bg-input);padding:2px 6px;border-radius:4px">python ml/app.py</code>
                </div>
            </div>
            @endif

            {{-- Bouton voir détails --}}
            @if(!empty($resultat['fichier_id']))
            <a href="{{ route('fichiers.show', $resultat['fichier_id']) }}"
               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:13px;background:var(--gold-dim);color:var(--gold);border-radius:10px;font-weight:600;font-size:14px;text-decoration:none;border:1px solid var(--gold)30;transition:all 0.2s">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                Voir le détail complet du fichier
            </a>
            @endif

        </div>
        @endif

    </div>
</div>