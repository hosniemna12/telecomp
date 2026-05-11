<?php

namespace App\Livewire\Rejets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcRejet;
use App\Services\Audit\AuditService;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $etape     = '';
    public string $traite    = '';

    protected $queryString = [
        'recherche' => ['except' => ''],
        'etape'     => ['except' => ''],
        'traite'    => ['except' => ''],
    ];

    public function updatingRecherche(): void { $this->resetPage(); }
    public function updatingEtape(): void     { $this->resetPage(); }
    public function updatingTraite(): void    { $this->resetPage(); }

    /**
     * Injection AuditService dans la méthode action — bonne pratique Livewire
     */
    public function marquerTraite(int $id, AuditService $audit): void
    {
        $rejet = TcRejet::findOrFail($id);
        $rejet->update([
            'traite'          => true,
            'date_traitement' => now(),
        ]);

        // ↓ Injection utilisée directement — pas de app()
        $audit->log(
            'REJET_TRAITE', 'REJETS',
            "Rejet #{$id} marqué comme traité — Code : {$rejet->code_rejet}"
        );

        $this->dispatch('rejet-traite');
    }

    public function marquerTousTraites(AuditService $audit): void
    {
        $nb = TcRejet::where('traite', false)->count();

        TcRejet::where('traite', false)->update([
            'traite'          => true,
            'date_traitement' => now(),
        ]);

        $audit->log(
            'REJETS_TOUS_TRAITES', 'REJETS',
            "{$nb} rejet(s) marqués comme traités"
        );

        $this->dispatch('rejets-traites');
    }

    public function reinitialiser(): void
    {
        $this->recherche = '';
        $this->etape     = '';
        $this->traite    = '';
        $this->resetPage();
    }

    public function render()
    {
        $rejets = TcRejet::with(['fichier', 'detail'])
            ->when($this->recherche, fn($q) =>
                $q->where(function ($q) {
                    $terme = '%' . $this->recherche . '%';
                    $q->where('motif_rejet', 'like', $terme)
                      ->orWhere('code_rejet', 'like', $terme);
                })
            )
            ->when($this->etape,  fn($q) => $q->where('etape_detection', $this->etape))
            ->when($this->traite !== '', fn($q) => $q->where('traite', $this->traite === '1'))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total'       => TcRejet::count(),
            'non_traites' => TcRejet::where('traite', false)->count(),
            'traites'     => TcRejet::where('traite', true)->count(),
            'parsing'     => TcRejet::where('etape_detection', 'PARSING')->count(),
            'validation'  => TcRejet::where('etape_detection', 'VALIDATION')->count(),
        ];

        return view('livewire.rejets.index', compact('rejets', 'stats'));
    }
}