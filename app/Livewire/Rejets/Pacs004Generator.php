<?php

namespace App\Livewire\Rejets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcPacs004;
use App\Services\Transformation\Pacs004TransformerService;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class Pacs004Generator extends Component
{
    use WithPagination;

    public string $recherche  = '';
    public string $statut     = '';
    public string $typeValeur = '';

    public bool   $showModal     = false;
    public bool   $showXmlModal  = false;
    public string $xmlContent    = '';
    public ?int   $fichierId     = null;
    public string $messageSucces = '';
    public string $messageErreur = '';

    protected $queryString = [
        'recherche'  => ['except' => ''],
        'statut'     => ['except' => ''],
        'typeValeur' => ['except' => ''],
    ];

    public function updatingRecherche(): void  { $this->resetPage(); }
    public function updatingStatut(): void     { $this->resetPage(); }
    public function updatingTypeValeur(): void { $this->resetPage(); }

    public function confirmerGeneration(int $fichierId): void
    {
        $this->fichierId     = $fichierId;
        $this->showModal     = true;
        $this->messageSucces = '';
        $this->messageErreur = '';
    }

    public function annuler(): void
    {
        $this->showModal = false;
        $this->fichierId = null;
    }

    /**
     * Injection dans la méthode — Livewire résout via le conteneur IoC
     * Pacs004TransformerService est une classe concrète (pas d'interface)
     * donc pas besoin de liaison dans AppServiceProvider.
     */
    public function generer(
        Pacs004TransformerService $service,
        AuditService $audit
    ): void {
        if (!$this->fichierId) return;

        try {
            // ↓ Injection utilisée directement — pas de app()
            $resultat = $service->genererPourFichier($this->fichierId);

            if ($resultat['succes']) {
                $this->messageSucces = $resultat['message'];

                if (isset($resultat['valide_xsd']) && $resultat['valide_xsd'] === false) {
                    $this->messageSucces .= ' ⚠️ Validation XSD échouée — vérifier les logs.';
                }

                $audit->log(
                    'PACS004_GENERE', 'PACS004',
                    "Pacs.004 généré pour fichier #{$this->fichierId} — {$resultat['nb_rejets']} rejet(s)"
                );

                $this->dispatch('pacs004-genere');

            } else {
                $this->messageErreur = $resultat['message'];
            }

        } catch (\Throwable $e) {
            Log::error('Erreur génération Pacs.004', [
                'exception'  => $e->getMessage(),
                'fichier_id' => $this->fichierId,
            ]);
            $this->messageErreur = "Erreur : " . $e->getMessage();
        }

        $this->showModal = false;
    }

    public function voirXml(int $pacs004Id): void
    {
        try {
            $pacs004            = TcPacs004::findOrFail($pacs004Id);
            $this->xmlContent   = $pacs004->contenu_xml ?? '';
            $this->showXmlModal = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->messageErreur = 'Pacs.004 introuvable.';
        }
    }

    public function fermerXml(): void
    {
        $this->showXmlModal = false;
        $this->xmlContent   = '';
    }

    /**
     * Injection AuditService dans la méthode action
     */
    public function marquerEnvoye(int $pacs004Id, AuditService $audit): void
    {
        try {
            $pacs004 = TcPacs004::findOrFail($pacs004Id);
            $pacs004->update(['statut' => 'ENVOYE']);

            $audit->log(
                'PACS004_ENVOYE', 'PACS004',
                "Pacs.004 #{$pacs004Id} marqué comme envoyé — MsgId : {$pacs004->msg_id}"
            );

            $this->messageSucces = "Pacs.004 marqué comme envoyé.";

        } catch (\Throwable $e) {
            Log::error('Erreur marquage Pacs.004', ['exception' => $e->getMessage()]);
            $this->messageErreur = 'Erreur lors de la mise à jour.';
        }
    }

    public function render()
    {
        try {
            $fichiers = TcFichier::withCount('rejets')
                ->has('rejets')
                ->when($this->recherche, fn($q) =>
                    $q->where('nom_fichier', 'like', '%' . $this->recherche . '%')
                )
                ->when($this->typeValeur, fn($q) =>
                    $q->where('type_valeur', $this->typeValeur)
                )
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $pacs004List = TcPacs004::with('fichier')
                ->when($this->statut, fn($q) => $q->where('statut', $this->statut))
                ->orderBy('created_at', 'desc')
                ->paginate(10, ['*'], 'pacs004Page');

            $stats = [
                'total_generes'  => TcPacs004::count(),
                'en_attente'     => TcPacs004::where('statut', 'GENERE')->count(),
                'envoyes'        => TcPacs004::where('statut', 'ENVOYE')->count(),
                'fichiers_rejet' => TcFichier::has('rejets')->count(),
            ];

            return view('livewire.rejets.pacs004-generator',
                compact('fichiers', 'pacs004List', 'stats')
            );

        } catch (\Throwable $e) {
            Log::error('Erreur rendu Pacs004Generator', ['exception' => $e->getMessage()]);
            return view('livewire.rejets.pacs004-generator', [
                'fichiers'    => TcFichier::paginate(0),
                'pacs004List' => TcPacs004::paginate(0),
                'stats'       => ['total_generes' => 0, 'en_attente' => 0, 'envoyes' => 0, 'fichiers_rejet' => 0],
            ]);
        }
    }
}
