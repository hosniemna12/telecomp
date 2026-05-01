<?php

namespace App\Livewire\Audit;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\AuditTrail;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $action    = '';
    public string $module    = '';
    public string $statut    = '';
    public string $dateDebut = '';
    public string $dateFin   = '';

    protected $queryString = [
        'recherche' => ['except' => ''],
        'action'    => ['except' => ''],
        'module'    => ['except' => ''],
        'statut'    => ['except' => ''],
    ];

    public function updatingRecherche(): void { $this->resetPage(); }
    public function updatingAction(): void    { $this->resetPage(); }
    public function updatingModule(): void    { $this->resetPage(); }
    public function updatingStatut(): void    { $this->resetPage(); }

    public function reinitialiser(): void
    {
        $this->recherche = '';
        $this->action    = '';
        $this->module    = '';
        $this->statut    = '';
        $this->dateDebut = '';
        $this->dateFin   = '';
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditTrail::when($this->recherche, fn($q) =>
                $q->where(function ($q) {
                    $terme = '%' . $this->recherche . '%';
                    $q->where('description', 'like', $terme)
                      ->orWhere('user_email', 'like', $terme);
                })
            )
            ->when($this->action,    fn($q) => $q->where('action', $this->action))
            ->when($this->module,    fn($q) => $q->where('module', $this->module))
            // CORRIGÉ : 'statut' au lieu de 'statut_action'
            ->when($this->statut,    fn($q) => $q->where('statut', $this->statut))
            ->when($this->dateDebut, fn($q) => $q->whereDate('created_at', '>=', $this->dateDebut))
            ->when($this->dateFin,   fn($q) => $q->whereDate('created_at', '<=', $this->dateFin))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // CORRIGÉ : 'statut' au lieu de 'statut_action'
        $stats = [
            'total'   => AuditTrail::count(),
            'success' => AuditTrail::where('statut', 'SUCCESS')->count(),
            'failed'  => AuditTrail::where('statut', 'FAILED')->count(),
            'today'   => AuditTrail::whereDate('created_at', today())->count(),
        ];

        return view('livewire.audit.index', compact('logs', 'stats'));
    }
}