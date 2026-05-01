<?php

namespace App\Livewire\Fichiers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $recherche   = '';
    public string $typeValeur  = '';
    public string $statut      = '';
    public string $dateDebut   = '';
    public string $dateFin     = '';
    public bool   $showFiltres = false;

    protected $queryString = [
        'recherche'  => ['except' => ''],
        'typeValeur' => ['except' => ''],
        'statut'     => ['except' => ''],
    ];

    public function updatingRecherche(): void  { $this->resetPage(); }
    public function updatingTypeValeur(): void { $this->resetPage(); }
    public function updatingStatut(): void     { $this->resetPage(); }
    public function updatingDateDebut(): void  { $this->resetPage(); }
    public function updatingDateFin(): void    { $this->resetPage(); }

    public function toggleFiltres(): void
    {
        $this->showFiltres = !$this->showFiltres;
    }

    public function reinitialiser(): void
    {
        $this->recherche  = '';
        $this->typeValeur = '';
        $this->statut     = '';
        $this->dateDebut  = '';
        $this->dateFin    = '';
        $this->resetPage();
    }

    public function nbFiltresActifs(): int
    {
        $nb = 0;
        if ($this->recherche)  $nb++;
        if ($this->typeValeur) $nb++;
        if ($this->statut)     $nb++;
        if ($this->dateDebut)  $nb++;
        if ($this->dateFin)    $nb++;
        return $nb;
    }

    public function render()
    {
        $fichiers = TcFichier::withCount(['enregistrementsDetails', 'rejets'])
            ->when($this->recherche, fn($q) =>
                $q->where('nom_fichier', 'like', '%' . $this->recherche . '%')
            )
            // CORRIGÉ : type_valeur est string(2), comparaison directe
            ->when($this->typeValeur, fn($q) =>
                $q->where('type_valeur', $this->typeValeur)
            )
            ->when($this->statut, fn($q) =>
                $q->where('statut', $this->statut)
            )
            ->when($this->dateDebut, fn($q) =>
                $q->whereDate('date_reception', '>=', $this->dateDebut)
            )
            ->when($this->dateFin, fn($q) =>
                $q->whereDate('date_reception', '<=', $this->dateFin)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total'          => TcFichier::count(),
            'traites'        => TcFichier::whereIn('statut', ['TRAITE','VALIDE','EN_ATTENTE_VALIDATION','TRAITE_PARTIEL'])->count(),
            'traite_partiel' => TcFichier::where('statut', 'TRAITE_PARTIEL')->count(),
            'erreurs'        => TcFichier::where('statut', 'ERREUR')->count(),
        ];

        return view('livewire.fichiers.index', compact('fichiers', 'stats'));
    }
}
