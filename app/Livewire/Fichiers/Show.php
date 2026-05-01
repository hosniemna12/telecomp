<?php

namespace App\Livewire\Fichiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcCommentaire;
use App\Services\ValidationService;
use App\Services\XmlTransformerService;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class Show extends Component
{
    public int    $id;
    public string $nouveauCommentaire   = '';
    public string $commentaireValidation = '';
    public string $motifRejet           = '';
    public bool   $showModalValidation  = false;
    public bool   $showModalRejet       = false;

    public function mount(int $id): void
    {
        $this->id = $id;
    }

    public function ouvrirModalValidation(): void
    {
        $this->showModalValidation = true;
    }

    public function ouvrirModalRejet(): void
    {
        $this->showModalRejet = true;
    }

    public function confirmerValidation(ValidationService $service): void
    {
        $fichier = TcFichier::findOrFail($this->id);
        $service->valider($fichier, $this->commentaireValidation);

        // Générer XML automatiquement après validation
        $this->dispatch('xml-genere');

        $this->showModalValidation  = false;
        $this->commentaireValidation = '';
        session()->flash('success', 'Fichier validé — XML en cours de génération.');
    }

    public function confirmerRejet(ValidationService $service): void
    {
        if (empty(trim($this->motifRejet))) {
            session()->flash('error', 'Le motif de rejet est obligatoire.');
            return;
        }

        $fichier = TcFichier::findOrFail($this->id);
        $service->rejeter($fichier, $this->motifRejet);

        $this->showModalRejet = false;
        $this->motifRejet     = '';
        session()->flash('success', 'Fichier rejeté — l\'opérateur a été notifié.');
    }

    public function ajouterCommentaire(ValidationService $service): void
    {
        if (empty(trim($this->nouveauCommentaire))) return;

        $fichier = TcFichier::findOrFail($this->id);
        $service->commenter($fichier, $this->nouveauCommentaire);

        $this->nouveauCommentaire = '';
    }

    public function resoumettre(ValidationService $service): void
    {
        $fichier = TcFichier::findOrFail($this->id);
        $service->soumettreValidation($fichier);
        session()->flash('success', 'Fichier resoumis pour validation.');
    }

    public function render()
    {
        $fichier = TcFichier::with([
            'enregistrementsDetails',
            'xmlProduit',
            'uploader',
            'valideur',
        ])->findOrFail($this->id);

        $commentaires = TcCommentaire::with('user')
            ->where('fichier_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.fichiers.show', compact('fichier', 'commentaires'));
    }
}
