<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TcRejet;
use App\Models\TcFichier;

class Notifications extends Component
{
    public int  $nbRejets     = 0;
    public int  $nbEnCours    = 0;
    public bool $showDropdown = false;

    public function refresh(): void
    {
        $this->nbRejets  = TcRejet::where('traite', false)->count();
        $this->nbEnCours = TcFichier::where('statut', 'EN_COURS')->count();
    }

    public function toggleDropdown(): void
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function render()
    {
        $this->refresh();

        $derniersRejets = TcRejet::with('fichier')
            ->where('traite', false)
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.notifications', compact('derniersRejets'));
    }
}