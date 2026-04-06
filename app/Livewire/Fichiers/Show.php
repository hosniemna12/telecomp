<?php

namespace App\Livewire\Fichiers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcEnrDetail;

#[Layout('layouts.app')]
class Show extends Component
{
    use WithPagination;

    public int    $fichierId;
    public string $statutFiltre = '';
    public string $recherche    = '';

    public function mount(int $id): void
    {
        $this->fichierId = $id;
    }

    public function updatingStatutFiltre(): void
    {
        $this->resetPage();
    }

    public function updatingRecherche(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $fichier = TcFichier::with(['enrGlobaux', 'xmlProduits', 'logs'])
            ->withCount([
                'enrDetails as total_transactions',
                'enrDetails as transactions_valides' => fn($q) =>
                    $q->where('statut', 'VALIDE'),
                'enrDetails as transactions_rejetees' => fn($q) =>
                    $q->where('statut', 'REJETE'),
            ])
            ->findOrFail($this->fichierId);

        $transactions = TcEnrDetail::where('fichier_id', $this->fichierId)
            ->when($this->statutFiltre, fn($q) =>
                $q->where('statut', $this->statutFiltre)
            )
            ->when($this->recherche, fn($q) =>
                $q->where(function($q) {
                    $q->where('nom_donneur', 'like', '%' . $this->recherche . '%')
                      ->orWhere('nom_beneficiaire', 'like', '%' . $this->recherche . '%')
                      ->orWhere('rib_donneur', 'like', '%' . $this->recherche . '%')
                      ->orWhere('rib_beneficiaire', 'like', '%' . $this->recherche . '%');
                })
            )
            ->orderBy('numero_virement')
            ->paginate(15);

        return view('livewire.fichiers.show', compact('fichier', 'transactions'));
    }
}