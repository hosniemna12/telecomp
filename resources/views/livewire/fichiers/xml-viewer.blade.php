<div>
<style>
.xml-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.xml-title{font-family:var(--font-display);font-size:22px;font-weight:700;color:var(--text-primary)}
.xml-subtitle{font-size:12px;color:var(--text-muted);margin-top:4px}
.xml-badge{display:inline-block;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;background:var(--gold-dim);color:var(--gold-light);border:1px solid var(--gold)}
.xml-editor{background:#0d1117;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.xml-toolbar{background:#161b22;border-bottom:1px solid var(--border);padding:10px 16px;display:flex;align-items:center;justify-content:space-between}
.xml-dots{display:flex;gap:6px}
.xml-dot{width:12px;height:12px;border-radius:50%}
.xml-dot.red{background:#ff5f56}
.xml-dot.yellow{background:#ffbd2e}
.xml-dot.green{background:#27c93f}
.xml-filename{font-size:11px;color:#8b949e;font-family:monospace}
.xml-content{overflow:auto;max-height:75vh}
.xml-table{width:100%;border-collapse:collapse;font-family:monospace;font-size:12px}
.xml-table tr:hover td{background:rgba(255,255,255,0.03)}
.xml-line-num{text-align:right;color:#3d444d;padding:2px 12px;border-right:1px solid #21262d;width:45px;user-select:none;vertical-align:top}
.xml-line-content{padding:2px 16px;white-space:pre;color:#e6edf3}
.xml-tag{color:#7ee787}
.xml-attr{color:#79c0ff}
.xml-val{color:#a5d6ff}
.xml-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px;text-align:center}
.xml-empty-icon{width:48px;height:48px;color:var(--text-muted);margin-bottom:12px}
.back-btn{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);text-decoration:none;padding:6px 12px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--bg-input);transition:all 0.15s}
.back-btn:hover{color:var(--text-primary);border-color:var(--gold)}
</style>

{{-- Bouton retour --}}
<div style="margin-bottom:20px">
    <a href="{{ route('fichiers.show', $fichier->id) }}" class="back-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 19l-7-7 7-7"/>
        </svg>
        Retour aux détails
    </a>
</div>

{{-- En-tête --}}
<div class="xml-header">
    <div>
        <h1 class="xml-title">Visualiseur XML ISO 20022</h1>
        <p class="xml-subtitle">{{ $fichier->nom_fichier }}</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
        <span class="xml-badge">{{ $typeMessage }}</span>
        <button wire:click="telecharger" class="btn btn-secondary btn-sm">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Télécharger
        </button>
        <button onclick="copyXml()" class="btn btn-secondary btn-sm">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px">
                <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
            <span id="copy-label">Copier</span>
        </button>
    </div>
</div>

{{-- Éditeur XML --}}
@if($xmlFormate)
<div class="xml-editor">
    <div class="xml-toolbar">
        <div class="xml-dots">
            <span class="xml-dot red"></span>
            <span class="xml-dot yellow"></span>
            <span class="xml-dot green"></span>
        </div>
        <span class="xml-filename">{{ str_replace('.ENV', '.xml', $fichier->nom_fichier) }}</span>
        <span style="font-size:11px;color:#8b949e">XML ISO 20022</span>
    </div>
    <div class="xml-content">
        <table class="xml-table">
            <tbody>
                @foreach(explode("\n", $xmlFormate) as $numero => $ligne)
                <tr>
                    <td class="xml-line-num">{{ $numero + 1 }}</td>
                    <td class="xml-line-content">@php
                        $l = htmlspecialchars($ligne);
                        $l = preg_replace('/(&lt;\/?)([\w:.]+)/', '$1<span class="xml-tag">$2</span>', $l);
                        $l = preg_replace('/([\w:]+)=(&quot;[^&]*&quot;)/', '<span class="xml-attr">$1</span>=<span class="xml-val">$2</span>', $l);
                        echo $l;
                    @endphp</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Infos --}}
<div style="display:flex;gap:16px;margin-top:12px">
    <div style="font-size:12px;color:var(--text-muted)">
        <span style="color:var(--text-secondary)">Lignes :</span>
        {{ count(explode("\n", $xmlFormate)) }}
    </div>
    <div style="font-size:12px;color:var(--text-muted)">
        <span style="color:var(--text-secondary)">Taille :</span>
        {{ number_format(strlen($xmlFormate) / 1024, 1) }} Ko
    </div>
    <div style="font-size:12px;color:var(--text-muted)">
        <span style="color:var(--text-secondary)">Type :</span>
        {{ $typeMessage }}
    </div>
</div>

@else
<div class="card">
    <div class="xml-empty">
        <svg class="xml-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
        </svg>
        <div style="font-size:14px;font-weight:500;color:var(--text-secondary)">Aucun XML généré pour ce fichier</div>
        <a href="{{ route('fichiers.show', $fichier->id) }}" class="back-btn" style="margin-top:12px">
            Retour aux détails
        </a>
    </div>
</div>
@endif

<script>
function copyXml() {
    const lines = document.querySelectorAll('.xml-line-content');
    let text = '';
    lines.forEach(l => text += l.innerText + '\n');
    navigator.clipboard.writeText(text).then(() => {
        document.getElementById('copy-label').textContent = 'Copié !';
        setTimeout(() => document.getElementById('copy-label').textContent = 'Copier', 2000);
    });
}
</script>
</div>
