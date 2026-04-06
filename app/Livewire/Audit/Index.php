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

    public function updatingRecherche(): void { $this->resetPage(); }
    public function updatingAction(): void    { $this->resetPage(); }
    public function updatingModule(): void    { $this->resetPage(); }

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
                $q->where('description', 'like', '%'.$this->recherche.'%')
                  ->orWhere('user_email', 'like', '%'.$this->recherche.'%')
            )
            ->when($this->action,    fn($q) => $q->where('action', $this->action))
            ->when($this->module,    fn($q) => $q->where('module', $this->module))
            ->when($this->statut,    fn($q) => $q->where('statut_action', $this->statut))
            ->when($this->dateDebut, fn($q) => $q->whereDate('created_at', '>=', $this->dateDebut))
            ->when($this->dateFin,   fn($q) => $q->whereDate('created_at', '<=', $this->dateFin))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total'   => AuditTrail::count(),
            'success' => AuditTrail::where('statut_action', 'SUCCESS')->count(),
            'failed'  => AuditTrail::where('statut_action', 'FAILED')->count(),
            'today'   => AuditTrail::whereDate('created_at', today())->count(),
        ];

        return view('livewire.audit.index', compact('logs', 'stats'));
    }
}