<div>
<style>
.notif-panel{position:fixed;top:var(--header-h);right:16px;width:360px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 16px 48px rgba(0,0,0,0.5);z-index:500;max-height:480px;overflow:hidden;display:flex;flex-direction:column}
.notif-head{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.notif-body{overflow-y:auto;flex:1}
.notif-item{display:flex;gap:10px;padding:12px 18px;border-bottom:1px solid rgba(255,255,255,0.03);cursor:pointer;transition:background 0.15s}
.notif-item:hover{background:rgba(255,255,255,0.03)}
.notif-item.non-lu{background:rgba(201,168,76,0.05)}
.notif-dot{width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0;margin-top:5px}
.notif-dot.lu{background:transparent;border:1.5px solid var(--border)}
</style>

{{-- Bouton cloche avec badge --}}
<div style="position:relative">
    <button wire:click="togglePanel"
        style="position:relative;width:36px;height:36px;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--text-secondary);transition:all 0.15s"
        onmouseover="this.style.borderColor='var(--gold)'"
        onmouseout="this.style.borderColor='var(--border)'">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        @if($count > 0)
        <span style="position:absolute;top:5px;right:5px;width:8px;height:8px;background:var(--gold);border-radius:50%;border:1.5px solid var(--bg-surface)"></span>
        @endif
    </button>

    {{-- Panel notifications --}}
    @if($showPanel)
    <div class="notif-panel">
        <div class="notif-head">
            <span style="font-family:var(--font-display);font-size:14px;font-weight:600;color:var(--text-primary)">
                Notifications @if($count > 0)<span style="color:var(--gold)">({{ $count }})</span>@endif
            </span>
            @if($count > 0)
            <button wire:click="toutMarquerLu" style="font-size:11px;color:var(--text-muted);background:none;border:none;cursor:pointer">
                Tout marquer lu
            </button>
            @endif
        </div>
        <div class="notif-body">
            @forelse($notifications as $n)
            <div class="notif-item {{ !$n->lu ? 'non-lu' : '' }}" wire:click="marquerLu({{ $n->id }})">
                <div class="notif-dot {{ $n->lu ? 'lu' : '' }}"></div>
                <div style="flex:1">
                    <div style="font-size:12.5px;font-weight:{{ !$n->lu ? '600' : '400' }};color:var(--text-primary)">
                        {{ $n->titre }}
                    </div>
                    <div style="font-size:11.5px;color:var(--text-secondary);margin-top:2px;line-height:1.5">
                        {{ Str::limit($n->message, 80) }}
                    </div>
                    <div style="font-size:10.5px;color:var(--text-muted);margin-top:4px">
                        {{ $n->created_at?->diffForHumans() }}
                        @if($n->fichier)
                        · <a href="{{ route('fichiers.show', $n->fichier_id) }}" style="color:var(--gold);text-decoration:none">Voir le fichier</a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:32px 20px;color:var(--text-muted);font-size:13px">
                Aucune notification
            </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
</div>
