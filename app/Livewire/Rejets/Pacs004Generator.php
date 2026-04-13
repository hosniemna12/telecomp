<?php

namespace App\Livewire\Rejets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcPacs004;
use App\Services\Pacs004TransformerService;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Contracts\View\Factory;

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
    public function updatingTypeValeur(): void { $this->resetPage(); }

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

        } catch (\Throwable $e) {
            Log::error('Erreur génération Pacs.004', ['exception' => $e]);
            $this->messageErreur = "Erreur : " . ($e->getMessage() ?? 'Erreur inconnue');
        }

        $this->showModal = false;
    }

    // ── Voir XML ─────────────────────────────────────────────────

    public function voirXml(int $pacs004Id): void
    {
        try {
            $pacs004 = TcPacs004::findOrFail($pacs004Id);
            $this->xmlContent   = $pacs004->contenu_xml ?? '';
            $this->showXmlModal = true;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->dispatch('notification', 'Pacs.004 introuvable');
        }
    }

    public function fermerXml(): void
    {
        $this->showXmlModal = false;
        $this->xmlContent   = '';
    }

    // ── Télécharger ───────────────────────────────────────────────

    public function telecharger(int $pacs004Id): void
    {
        $this->redirect(route('pacs004.telecharger', $pacs004Id));
    }

    // ── Marquer envoyé ───────────────────────────────────────────

    public function marquerEnvoye(int $pacs004Id): void
    {
        try {
            $updated = TcPacs004::where('id', $pacs004Id)
                                ->update(['statut' => 'ENVOYE']);

            if (!$updated) {
                $this->dispatch('notification', 'Pacs.004 introuvable');
                return;
            }

            app(AuditService::class)->log(
                'PACS004_ENVOYE',
                'PACS004',
                "Pacs.004 #{$pacs004Id} marqué comme envoyé"
            );
        } catch (\Throwable $e) {
            Log::error('Erreur marquage Pacs.004', ['exception' => $e]);
            $this->dispatch('notification', 'Erreur lors de la mise à jour');
        }
    }

    // ── Render ───────────────────────────────────────────────────

    public function render(): View|Factory
    {
        try {
            // Utiliser withCount pour meilleure performance
            $fichiers = TcFichier::withCount('rejets')
                ->having('rejets_count', '>', 0)
                ->when($this->recherche, fn($q) =>
                    $q->where('tc_fichiers.nom_fichier', 'like', '%' . $this->recherche . '%')
                )
                ->when($this->typeValeur, fn($q) =>
                    $q->where('tc_fichiers.type_valeur', $this->typeValeur)
                )
                ->orderBy('tc_fichiers.created_at', 'desc')
                ->paginate(10);

            $pacs004List = TcPacs004::with('fichier')
                ->when($this->statut, fn($q) =>
                    $q->where('statut', $this->statut)
                )
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
            Log::error('Erreur rendu Pacs004Generator', ['exception' => $e]);
            return view('livewire.rejets.pacs004-generator', [
                'fichiers' => collect(),
                'pacs004List' => collect(),
                'stats' => ['total_generes' => 0, 'en_attente' => 0, 'envoyes' => 0, 'fichiers_rejet' => 0]
            ]);
        }
    }
}