<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email    = '';
    public string $password = '';
    public bool   $remember = false;
    public string $erreur   = '';

    protected $rules = [
        'email'    => 'required|email',
        'password' => 'required|min:6',
    ];

    protected $messages = [
        'email.required'    => 'L\'adresse email est obligatoire.',
        'email.email'       => 'L\'adresse email n\'est pas valide.',
        'password.required' => 'Le mot de passe est obligatoire.',
        'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
    ];

    public function connecter(): void
    {
        $this->validate();
        $this->erreur = '';

        if (Auth::attempt([
            'email'    => $this->email,
            'password' => $this->password,
        ], $this->remember)) {
            session()->regenerate();
            $this->redirect(route('dashboard'), navigate: true);
        } else {
            $this->erreur = 'Email ou mot de passe incorrect.';
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}