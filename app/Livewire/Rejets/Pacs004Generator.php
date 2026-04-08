<?php

namespace App\Livewire\Rejets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcPacs004;
use App\Services\Pacs004TransformerService;
use App\Services\AuditService;

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

    public function updatingRecherche(): void { $this->resetPage(); }
    public function updatingStatut(): void    { $this->resetPage(); }

    // ── Ouvrir modal confirmation ─────────────────────────────────

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

    // ── Générer Pacs.004 ─────────────────────────────────────────

    public function generer(): void
    {
        if (!$this->fichierId) return;

        try {
            $service  = app(Pacs004TransformerService::class);
            $resultat = $service->genererPourFichier($this->fichierId);

            if ($resultat['succes']) {
                $this->messageSucces = $resultat['message'];

                app(AuditService::class)->log(
                    'PACS004_GENERE',
                    'PACS004',
                    "Pacs.004 généré pour fichier #{$this->fichierId} — {$resultat['nb_rejets']} rejet(s)"
                );

                $this->dispatch('pacs004-genere');

            } else {
                $this->messageErreur = $resultat['message'];
            }

        } catch (\Exception $e) {
            $this->messageErreur = "Erreur : " . $e->getMessage();
        }

        $this->showModal = false;
    }

    // ── Voir XML ─────────────────────────────────────────────────

    public function voirXml(int $pacs004Id): void
    {
        $pacs004 = TcPacs004::find($pacs004Id);
        if ($pacs004) {
            $this->xmlContent   = $pacs004->contenu_xml ?? '';
            $this->showXmlModal = true;
        }
    }

    public function fermerXml(): void
    {
        $this->showXmlModal = false;
        $this->xmlContent   = '';
    }

    // ── Marquer envoyé ───────────────────────────────────────────

    public function marquerEnvoye(int $pacs004Id): void
    {
        TcPacs004::where('id', $pacs004Id)
                 ->update(['statut' => 'ENVOYE']);

        app(AuditService::class)->log(
            'PACS004_ENVOYE',
            'PACS004',
            "Pacs.004 #{$pacs004Id} marqué comme envoyé"
        );
    }

    // ── Render ───────────────────────────────────────────────────

    public function render()
    {
        // ✅ Compatible Oracle — whereHas au lieu de having()
        $fichiers = TcFichier::whereHas('rejets')
            ->when($this->recherche, fn($q) =>
                $q->where('nom_fichier', 'like', '%' . $this->recherche . '%')
            )
            ->when($this->typeValeur, fn($q) =>
                $q->where('type_valeur', $this->typeValeur)
            )
            ->withCount('rejets')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $pacs004List = TcPacs004::with('fichier')
            ->when($this->statut, fn($q) =>
                $q->where('statut', $this->statut)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'pacs004Page');

        // ✅ Compatible Oracle — whereHas au lieu de having()
        $stats = [
            'total_generes'  => TcPacs004::count(),
            'en_attente'     => TcPacs004::where('statut', 'GENERE')->count(),
            'envoyes'        => TcPacs004::where('statut', 'ENVOYE')->count(),
            'fichiers_rejet' => TcFichier::whereHas('rejets')->count(),
        ];

        return view('livewire.rejets.pacs004-generator',
            compact('fichiers', 'pacs004List', 'stats')
        );
    }
}