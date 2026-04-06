<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditService;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $recherche  = '';
    public bool   $showModal  = false;
    public bool   $editMode   = false;
    public int    $editId     = 0;
    public string $name       = '';
    public string $email      = '';
    public string $password   = '';
    public string $role       = 'operateur';
    public string $erreurForm = '';
    public string $successMsg = '';

    protected function rules(): array
    {
        $emailRule = $this->editMode
            ? 'required|email|unique:users,email,' . $this->editId
            : 'required|email|unique:users,email';

        $passwordRule = $this->editMode
            ? 'nullable|min:6'
            : 'required|min:6';

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
        'email.required'    => 'L\'email est obligatoire.',
        'email.email'       => 'L\'email n\'est pas valide.',
        'email.unique'      => 'Cet email est déjà utilisé.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
        'role.required'     => 'Le rôle est obligatoire.',
        'role.in'           => 'Le rôle sélectionné est invalide.',
    ];

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function ouvrirModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editMode  = false;
    }

    public function editer(int $id): void
    {
        $user = User::findOrFail($id);

        if ($id === (int)auth()->user()->id) {
            $this->erreurForm = 'Vous ne pouvez pas modifier votre propre compte ici.';
            return;
        }

        $this->editId    = $id;
        $this->name      = $user->name;
        $this->email     = $user->email;
        $this->role      = $user->role;
        $this->password  = '';
        $this->editMode  = true;
        $this->showModal = true;
    }

    public function sauvegarder(): void
    {
        $this->erreurForm = '';
        $this->validate();

        $audit = app(AuditService::class);

        try {
            if ($this->editMode) {
                $user     = User::findOrFail($this->editId);
                $avant    = ['name' => $user->name, 'email' => $user->email, 'role' => $user->role];
                $data     = [
                    'name'  => $this->name,
                    'email' => $this->email,
                    'role'  => $this->role,
                ];
                if (!empty($this->password)) {
                    $data['password'] = Hash::make($this->password);
                }
                $user->update($data);

                $audit->log(
                    'USER_UPDATE', 'USERS',
                    "Modification utilisateur : {$this->email} — Role : {$this->role}",
                    $avant,
                    ['name' => $this->name, 'email' => $this->email, 'role' => $this->role]
                );

                $this->successMsg = "Utilisateur mis à jour avec succès.";

            } else {
                User::create([
                    'name'     => $this->name,
                    'email'    => $this->email,
                    'password' => Hash::make($this->password),
                    'role'     => $this->role,
                ]);

                $audit->log(
                    'USER_CREATE', 'USERS',
                    "Creation utilisateur : {$this->email} — Role : {$this->role}",
                    [],
                    ['name' => $this->name, 'email' => $this->email, 'role' => $this->role]
                );

                $this->successMsg = "Utilisateur créé avec succès.";
            }

            $this->showModal = false;
            $this->resetForm();

        } catch (\Exception $e) {
            $this->erreurForm = "Erreur : " . $e->getMessage();
        }
    }

    public function supprimer(int $id): void
    {
        if ($id === (int)auth()->id()) {
            $this->erreurForm = 'Vous ne pouvez pas supprimer votre propre compte.';
            return;
        }

        $user = User::findOrFail($id);
        $email = $user->email;
        $user->delete();

        app(AuditService::class)->log(
            'USER_DELETE', 'USERS',
            "Suppression utilisateur : {$email}",
            ['email' => $email], []
        );

        $this->successMsg = "Utilisateur supprimé.";
    }

    public function fermerModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name       = '';
        $this->email      = '';
        $this->password   = '';
        $this->role       = 'operateur';
        $this->editId     = 0;
        $this->editMode   = false;
        $this->erreurForm = '';
        $this->resetValidation();
    }

    public function render()
    {
        $users = User::query()
            ->when($this->recherche, fn($q) =>
                $q->where('name', 'like', '%' . $this->recherche . '%')
                  ->orWhere('email', 'like', '%' . $this->recherche . '%')
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