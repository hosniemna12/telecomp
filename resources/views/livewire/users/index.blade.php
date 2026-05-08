<div>
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:1000;backdrop-filter:blur(4px)}
.modal-box{background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-lg);padding:28px;width:100%;max-width:460px;box-shadow:0 24px 64px rgba(0,0,0,0.5)}
.modal-title{font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:20px}
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:12px;font-weight:500;color:var(--text-secondary);margin-bottom:5px}
.role-badge{font-size:10px;font-weight:700;letter-spacing:0.8px;text-transform:uppercase}
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px}
.page-title{font-family:var(--font-display);font-size:26px;font-weight:700;color:var(--text-primary)}
.page-subtitle{font-size:13px;color:var(--text-muted);margin-top:4px}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Utilisateurs</h1>
        <p class="page-subtitle">Gestion des accès et des rôles — 4 niveaux de permission</p>
    </div>
    <button wire:click="$set('showModal', true)" class="btn btn-primary">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel utilisateur
    </button>
</div>

{{-- Legende des roles --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;font-size:12px">
        <span class="badge badge-danger role-badge" style="padding:2px 6px">Admin</span>
        <span style="color:var(--text-muted)">Accès total</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;font-size:12px">
        <span class="badge badge-gold role-badge" style="padding:2px 6px">Superviseur</span>
        <span style="color:var(--text-muted)">Supervision & validation</span>
    </div>
    <div style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:var(--bg-card);border:1px solid var(--border);border-radius:20px;font-size:12px">
        <span class="badge badge-blue role-badge" style="padding:2px 6px">Opérateur</span>
        <span style="color:var(--text-muted)">Traitement fichiers</span>
    </div>

</div>

@if(session('success'))
<div style="background:var(--green-dim);border:1px solid rgba(34,197,94,0.2);border-radius:var(--radius);padding:12px 18px;margin-bottom:20px;color:var(--green);font-size:13px">
    ✓ {{ session('success') }}
</div>
@endif
@if(session('erreur_acces'))
<div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius);padding:12px 18px;margin-bottom:20px;color:var(--red);font-size:13px">
    ✗ {{ session('erreur_acces') }}
</div>
@endif

<div class="table-wrap">
    <div class="table-head">
        <span class="table-title">{{ $users->total() }} utilisateur(s)</span>
        <div class="input-wrap" style="width:240px">
            <svg class="input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input wire:model.live="search" type="text" class="input" placeholder="Rechercher...">
        </div>
    </div>
    <table>
        <thead>
            <tr><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Permissions</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
        @forelse($users as $u)
        @php
            $roleColors = ['admin'=>'danger','superviseur'=>'gold','operateur'=>'blue'];
            $rolePerms  = ['admin'=>'Tout','superviseur'=>'Upload, Rejets, Stats','operateur'=>'Upload, Rejets'];
            $role = $u->role ?? 'operateur';
        @endphp
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:34px;height:34px;border-radius:50%;background:var(--gold-dim);border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--gold-light);font-family:var(--font-display)">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-primary)">{{ $u->name }}</div>
                </div>
            </td>
            <td style="font-size:12.5px;color:var(--text-secondary)">{{ $u->email }}</td>
            <td><span class="badge badge-{{ $roleColors[$role] ?? 'muted' }} role-badge">{{ ucfirst($role) }}</span></td>
            <td style="font-size:11.5px;color:var(--text-muted)">{{ $rolePerms[$role] ?? '—' }}</td>
            <td><span class="badge badge-success">Actif</span></td>
            <td>
                <div class="flex gap-3">
                    <button wire:click="modifier({{ $u->id }})" class="btn btn-ghost btn-sm">Modifier</button>
                    @if($u->id !== auth()->id())
                    <button wire:click="supprimer({{ $u->id }})" class="btn btn-danger btn-sm"
                            wire:confirm="Confirmer la suppression ?">Supprimer</button>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6">
            <div class="empty-state"><div class="empty-title">Aucun utilisateur trouvé</div></div>
        </td></tr>
        @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">{{ $users->links() }}</div>
    @endif
</div>

{{-- MODAL Creation/Modification --}}
@if($showModal)
<div class="modal-overlay" wire:click.self="$set('showModal', false)">
    <div class="modal-box">
        <div class="modal-title">{{ $editId ? "Modifier l'utilisateur" : 'Nouvel utilisateur' }}</div>

        @if($errors->any())
        <div style="background:var(--red-dim);border:1px solid rgba(239,68,68,0.2);border-radius:var(--radius-sm);padding:10px 14px;margin-bottom:16px;color:var(--red);font-size:12px">
            @foreach($errors->all() as $e)<div>✗ {{ $e }}</div>@endforeach
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Nom complet</label>
            <input wire:model="name" type="text" class="input" placeholder="Prénom Nom">
        </div>
        <div class="form-group">
            <label class="form-label">Adresse email</label>
            <input wire:model="email" type="email" class="input" placeholder="email@btl.com.tn">
        </div>
        @if(!$editId)
        <div class="form-group">
            <label class="form-label">Mot de passe</label>
            <input wire:model="password" type="password" class="input" placeholder="Minimum 8 caractères">
        </div>
        @endif
        <div class="form-group">
            <label class="form-label">Rôle et niveau d'accès</label>
            <select wire:model.live="role" class="input">
                <option value="">-- Choisir un rôle --</option>
                <option value="admin">Administrateur — Accès total</option>
                <option value="superviseur">Superviseur — Supervision & validation</option>
                <option value="operateur">Opérateur — Traitement fichiers</option>
            </select>

            @if($role === 'admin')
            <div style="margin-top:8px;padding:8px 12px;background:var(--red-dim);border-radius:var(--radius-sm);font-size:11.5px;color:var(--red)">
                Accès complet : gestion utilisateurs, audit, tous les fichiers
            </div>
            @elseif($role === 'superviseur')
            <div style="margin-top:8px;padding:8px 12px;background:var(--gold-dim);border-radius:var(--radius-sm);font-size:11.5px;color:var(--gold-light)">
                Upload fichiers, validation rejets, génération pacs.004, statistiques
            </div>
            @elseif($role === 'operateur')
            <div style="margin-top:8px;padding:8px 12px;background:var(--blue-dim);border-radius:var(--radius-sm);font-size:11.5px;color:var(--blue-acc)">
                Upload fichiers, traitement rejets, génération XML
            </div>
            @endif
        </div>

        <div style="display:flex;gap:10px;margin-top:20px">
            <button wire:click="sauvegarder" class="btn btn-primary" style="flex:1;justify-content:center">
                <span wire:loading.remove>{{ $editId ? 'Enregistrer' : "Créer l'utilisateur" }}</span>
                <span wire:loading>Traitement...</span>
            </button>
            <button wire:click="$set('showModal', false)" class="btn btn-secondary">Annuler</button>
        </div>
    </div>
</div>
@endif
</div>
