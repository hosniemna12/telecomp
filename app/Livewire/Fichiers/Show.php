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
    public string $nouveauCommentaire    = '';
    public string $commentaireValidation = '';
    public string $motifRejet            = '';
    public bool   $showModalValidation   = false;
    public bool   $showModalRejet        = false;

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

        $this->dispatch('xml-genere');

        $this->showModalValidation   = false;
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

    // ✅ AJOUTÉ : Télécharger le XML généré
    public function telechargerXml(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fichier = TcFichier::with('xmlProduit')->findOrFail($this->id);

        abort_unless($fichier->xmlProduit && $fichier->xmlProduit->contenu_xml, 404, 'XML non encore généré.');

        $typeMessage = $fichier->xmlProduit->type_message ?? 'xml';
        $nomFichier  = pathinfo($fichier->nom_fichier, PATHINFO_FILENAME) . '.xml';

        return response()->streamDownload(
            function () use ($fichier) {
                echo $fichier->xmlProduit->contenu_xml;
            },
            $nomFichier,
            [
                'Content-Type'        => 'application/xml',
                'Content-Disposition' => 'attachment; filename="' . $nomFichier . '"',
            ]
        );
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