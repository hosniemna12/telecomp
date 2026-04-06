<?php

namespace App\Livewire\Stats;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcEnrDetail;
use App\Models\TcRejet;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $periode = '7'; // jours

    public function render()
    {
        // Fichiers par jour (7 ou 30 derniers jours)
        $fichiersParJour = TcFichier::select(
                DB::raw("TRUNC(date_reception) as jour"),
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN statut = 'TRAITE' THEN 1 ELSE 0 END) as traites"),
                DB::raw("SUM(CASE WHEN statut = 'ERREUR' THEN 1 ELSE 0 END) as erreurs")
            )
            ->where('date_reception', '>=',
                now()->subDays((int)$this->periode))
            ->groupBy(DB::raw("TRUNC(date_reception)"))
            ->orderBy('jour')
            ->get();

        // Répartition par type
        $parType = TcFichier::select('type_valeur',
                DB::raw("COUNT(*) as total"))
            ->groupBy('type_valeur')
            ->get();

        // Transactions par statut
        $transactionsParStatut = TcEnrDetail::select('statut',
                DB::raw("COUNT(*) as total"))
            ->groupBy('statut')
            ->get();

        // Top montants
        $topTransactions = TcEnrDetail::where('statut', 'VALIDE')
            ->orderBy('montant', 'desc')
            ->take(5)
            ->get();

        // Stats globales
        $stats = [
            'total_fichiers'     => TcFichier::count(),
            'total_transactions' => TcEnrDetail::count(),
            'total_rejets'       => TcRejet::count(),
            'montant_total'      => TcEnrDetail::sum('montant'),
            'montant_moyen'      => TcEnrDetail::avg('montant'),
            'taux_rejet'         => TcEnrDetail::count() > 0
                ? round(TcEnrDetail::where('statut','REJETE')->count() / TcEnrDetail::count() * 100, 2)
                : 0,
        ];

        return view('livewire.stats.index', compact(
            'fichiersParJour',
            'parType',
            'transactionsParStatut',
            'topTransactions',
            'stats'
        ));
    }
}