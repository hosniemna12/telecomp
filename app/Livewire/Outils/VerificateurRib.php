<?php

namespace App\Livewire\Outils;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\RibValidatorService;

#[Layout('layouts.app')]
class VerificateurRib extends Component
{
    public string $rib      = '';
    public array  $resultat = [];
    public bool   $verifie  = false;

    public function verifier(RibValidatorService $validator): void
    {
        $this->verifie  = true;
        $this->resultat = $validator->valider($this->rib);
    }

    public function reinitialiser(): void
    {
        $this->rib      = '';
        $this->resultat = [];
        $this->verifie  = false;
    }

    public function render()
    {
        return view('livewire.outils.verificateur-rib');
    }
}