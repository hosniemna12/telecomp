<?php

namespace App\Livewire\Rejets;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TcRejet;
use App\Models\TcFichier;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $recherche    = '';
    public string $etape        = '';
    public string $traite       = '';
    public string $fichierId    = '';

    protected $queryString = [
        'recherche' => ['except' => ''],
        'etape'     => ['except' => ''],
        'traite'    => ['except' => ''],
    ];

    public function updatingRecherche(): void { $this->resetPage(); }
    public function updatingEtape(): void     { $this->resetPage(); }
    public function updatingTraite(): void    { $this->resetPage(); }

    public function marquerTraite(int $id): void
    {
        TcRejet::where('id', $id)->update([
            'traite'          => true,
            'date_traitement' => now(),
        ]);
        $this->dispatch('rejet-traite');
        app(\App\Services\AuditService::class)->log(
    'REJET_TRAITE', 'REJETS',
    "Rejet #$id marque comme traite"
);
    }

    public function marquerTousTraites(): void
    {
        TcRejet::where('traite', false)->update([
            'traite'          => true,
            'date_traitement' => now(),
        ]);
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
                $q->where('motif_rejet', 'like', '%' . $this->recherche . '%')
                  ->orWhere('code_rejet', 'like', '%' . $this->recherche . '%')
            )
            ->when($this->etape, fn($q) =>
                $q->where('etape_detection', $this->etape)
            )
            ->when($this->traite !== '', fn($q) =>
                $q->where('traite', $this->traite === '1')
            )
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