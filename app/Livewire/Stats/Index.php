<?php

namespace App\Livewire\Stats;

use Livewire\Component;
use App\Models\TcFichier;
use App\Models\TcEnrDetail;
use Carbon\Carbon;

class Index extends Component
{
    public string $periode = '7';

    public array $kpis           = [];
    public array $chartEvolution = [];
    public array $chartStatut    = [];
    public array $chartTypes     = [];
    public array $chartRejets    = [];

    public function mount(): void
    {
        $this->chargerStats();
    }

    public function updatedPeriode(): void
    {
        $this->chargerStats();
        $this->dispatch('stats-updated', [
            'evolution' => $this->chartEvolution,
            'statut'    => $this->chartStatut,
            'types'     => $this->chartTypes,
            'rejets'    => $this->chartRejets,
        ]);
    }

    private function chargerStats(): void
    {
        $debut = Carbon::now()->subDays((int) $this->periode);

        $totalTx  = TcEnrDetail::where('created_at', '>=', $debut)->count();
        $totalRej = TcEnrDetail::where('created_at', '>=', $debut)
                        ->where('statut', 'REJETE')->count();

        $this->kpis = [
            'total_fichiers'     => TcFichier::where('created_at', '>=', $debut)->count(),
            'total_transactions' => $totalTx,
            'montant_total'      => TcEnrDetail::where('created_at', '>=', $debut)->sum('montant'),
            'total_rejets'       => $totalRej,
            'taux_rejet'         => $totalTx > 0 ? round($totalRej / $totalTx * 100, 2) : 0,
        ];

        // Courbe évolution quotidienne
        $jours = collect();
        for ($i = (int)$this->periode - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $jours->push([
                'date'    => Carbon::parse($date)->format('d M'),
                'valides' => TcEnrDetail::whereDate('created_at', $date)
                                ->where('statut', 'VALIDE')->count(),
                'rejetes' => TcEnrDetail::whereDate('created_at', $date)
                                ->where('statut', 'REJETE')->count(),
            ]);
        }
        $this->chartEvolution = $jours->toArray();

        // Donut statut
        $this->chartStatut = [
            'valides' => $totalTx - $totalRej,
            'rejetes' => $totalRej,
        ];

        // Barres types de valeur — correspondance codes SIBTEL
        $typeLabels = [
            '10' => 'Virement',
            '20' => 'Prélèvement',
            '30' => 'Chèque',
            '31' => 'CNP',
            '32' => 'ARP',
            '40' => 'LDC',
            '84' => 'Papillon',
        ];

        $types = TcEnrDetail::where('created_at', '>=', $debut)
            ->selectRaw('type_valeur, COUNT(*) as total')
            ->groupBy('type_valeur')
            ->orderByDesc('total')
            ->get();

        $this->chartTypes = $types->map(fn($t) => [
            'label' => $typeLabels[$t->type_valeur] ?? 'Type '.$t->type_valeur,
            'value' => $t->total,
        ])->toArray();

        // Taux de rejet quotidien
        $tauxJours = collect();
        for ($i = (int)$this->periode - 1; $i >= 0; $i--) {
            $date  = Carbon::now()->subDays($i)->toDateString();
            $total = TcEnrDetail::whereDate('created_at', $date)->count();
            $rej   = TcEnrDetail::whereDate('created_at', $date)
                        ->where('statut', 'REJETE')->count();
            $tauxJours->push([
                'date' => Carbon::parse($date)->format('d M'),
                'taux' => $total > 0 ? round($rej / $total * 100, 1) : 0,
            ]);
        }
        $this->chartRejets = $tauxJours->toArray();
    }

    public function render()
    {
        return view('livewire.stats.index', ['title' => 'Statistiques']);
    }
}