<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search     = '';
    public bool   $showModal  = false;
    public bool   $editMode   = false;
    public int    $editId     = 0;
    public string $name       = '';
    public string $email      = '';
    public string $password   = '';
    public string $role       = 'operateur';

    protected function rules(): array
    {
        $emailRule    = $this->editMode
            ? 'required|email|unique:users,email,' . $this->editId
            : 'required|email|unique:users,email';

        $passwordRule = $this->editMode ? 'nullable|min:6' : 'required|min:6';

        return [
            'name'     => 'required|min:3',
            'email'    => $emailRule,
            'password' => $passwordRule,
            'role'     => 'required|in:admin,operateur,superviseur',
        ];
    }

    protected $messages = [
        'name.required'     => 'Le nom est obligatoire.',
        'name.min'          => 'Le nom doit contenir au moins 3 caractères.',
        'email.required'    => "L'email est obligatoire.",
        'email.email'       => "L'email n'est pas valide.",
        'email.unique'      => 'Cet email est déjà utilisé.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        'role.required'     => 'Le rôle est obligatoire.',
        'role.in'           => 'Le rôle sélectionné est invalide.',
    ];

    public function updatingSearch(): void { $this->resetPage(); }

    // Alias pour la vue (wire:click="modifier()")
    public function modifier(int $id): void
    {
        $this->editer($id);
    }

    public function editer(int $id): void
    {
        if ($id === (int)Auth::id()) {
            session()->flash('error', 'Vous ne pouvez pas modifier votre propre compte ici.');
            return;
        }

        $user            = User::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->role;
        $this->password  = '';
        $this->editMode  = true;
        $this->showModal = true;
    }

    public function sauvegarder(AuditService $audit): void
    {
        $this->validate();

        try {
            if ($this->editMode) {
                $user  = User::findOrFail($this->editId);
                $avant = ['name' => $user->name, 'email' => $user->email, 'role' => $user->role];

                $data = ['name' => $this->name, 'email' => $this->email, 'role' => $this->role];
                if (!empty($this->password)) {
                    $data['password'] = Hash::make($this->password);
                }
                $user->update($data);

                $audit->log('USER_UPDATE', 'USERS',
                    "Modification utilisateur : {$this->email} — Rôle : {$this->role}",
                    $avant,
                    ['name' => $this->name, 'email' => $this->email, 'role' => $this->role]
                );

                session()->flash('success', "Utilisateur mis à jour avec succès.");

            } else {
                User::create([
                    'name'     => $this->name,
                    'email'    => $this->email,
                    'password' => Hash::make($this->password),
                    'role'     => $this->role,
                ]);

                $audit->log('USER_CREATE', 'USERS',
                    "Création utilisateur : {$this->email} — Rôle : {$this->role}",
                    [],
                    ['name' => $this->name, 'email' => $this->email, 'role' => $this->role]
                );

                session()->flash('success', "Utilisateur créé avec succès.");
            }

            $this->showModal = false;
            $this->resetForm();

        } catch (\Exception $e) {
            session()->flash('error', "Erreur : " . $e->getMessage());
        }
    }

    public function supprimer(int $id, AuditService $audit): void
    {
        if ($id === (int)Auth::id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return;
        }

        $user  = User::findOrFail($id);
        $email = $user->email;
        $user->delete();

        $audit->log('USER_DELETE', 'USERS',
            "Suppression utilisateur : {$email}",
            ['email' => $email],
            []
        );

        session()->flash('success', "Utilisateur supprimé.");
    }

    public function fermerModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name      = '';
        $this->email     = '';
        $this->password  = '';
        $this->role      = 'operateur';
        $this->editId    = 0;
        $this->editMode  = false;
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total'        => User::count(),
            'admins'       => User::where('role', 'admin')->count(),
            'operateurs'   => User::where('role', 'operateur')->count(),
            'superviseurs' => User::where('role', 'superviseur')->count(),
        ];

        return view('livewire.users.index', compact('users', 'stats'));
    }
}
