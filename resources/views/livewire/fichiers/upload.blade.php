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
.type-card:hover { border-color: rgba(201,168,76,0.4); background: var(--gold-glow); }
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
</style>

    <div style="margin-bottom:28px">
        <h1 style="font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)">Importer un fichier T24</h1>
        <p style="font-size:13px;color:var(--text-muted);margin-top:4px">Importez un fichier ENV ou PAK généré par Temenos T24</p>
    </div>

    <div class="upload-form">
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
                    <div style="font-size:12px;color:var(--text-muted);margin-top:4px">{{ $fichier->getClientOriginalName() }} — Cliquez pour changer</div>
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

        @if(session("success"))
        <div style="background:var(--green-dim);border:1px solid rgba(34,197,94,0.2);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;color:var(--green);font-size:13px">
            ✓ {{ session("success") }}
        </div>
        @endif
        @if(session("error"))
        <div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius);padding:12px 16px;margin-bottom:16px;color:var(--red);font-size:13px">
            ✗ {{ session("error") }}
        </div>
        @endif
        @if($erreur)
        <div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:12px;color:var(--red);font-size:13px">✗ {{ $erreur }}</div>
        @endif
        <div style="font-size:11px;color:yellow;margin-bottom:8px">DEBUG: type={{ $typeValeur }} | fichier={{ $fichier ? 'OK' : 'NULL' }}</div>
        <button wire:click="traiter" wire:loading.attr="disabled"
        {{-- DEBUG --}}
        <div style="background:rgba(255,0,0,0.1);padding:10px;margin-bottom:10px;font-size:12px;color:red">
            TypeValeur: {{ $typeValeur }} | Fichier: {{ $fichier ? $fichier->getClientOriginalName() : 'null' }} | Erreur: {{ $erreur }}
        </div>
        <button wire:click="traiter" wire:loading.attr="disabled"
                class="btn btn-primary"
                style="width:100%;justify-content:center;padding:13px;font-size:14px">
            <span wire:loading.remove>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="margin-right:6px">
                    <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                </svg>
                Lancer le traitement
            </span>
            <span wire:loading>⏳ Traitement en cours...</span>
        </button>
    </div>
</div>
