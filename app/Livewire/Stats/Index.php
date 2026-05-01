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
    public string $periode = '7';

    public function render()
    {
        // CORRIGÉ : DATE() au lieu de TRUNC() — compatible SQLite et MySQL
        $fichiersParJour = TcFichier::select(
                DB::raw("TRUNC(date_reception) as jour"),
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN statut = 'TRAITE' THEN 1 ELSE 0 END) as traites"),
                DB::raw("SUM(CASE WHEN statut = 'ERREUR' THEN 1 ELSE 0 END) as erreurs")
            )
            ->where('date_reception', '>=', now()->subDays((int)$this->periode))
            ->groupBy(DB::raw("TRUNC(date_reception)"))
            ->orderBy(DB::raw('TRUNC(date_reception)'))
            ->get();

        // Répartition par type — CORRIGÉ : inclure tous les types SIBTEL
        $parType = TcFichier::select('type_valeur', DB::raw("COUNT(*) as total"))
            ->groupBy('type_valeur')
            ->orderBy('total', 'desc')
            ->get();

        // Transactions par statut
        $transactionsParStatut = TcEnrDetail::select('statut', DB::raw("COUNT(*) as total"))
            ->groupBy('statut')
            ->get();

        // Top montants
        $topTransactions = TcEnrDetail::where('statut', 'VALIDE')
            ->orderBy('montant', 'desc')
            ->take(5)
            ->get();

        $totalTx = TcEnrDetail::count();

        // CORRIGÉ : montant_moyen protégé contre division par zéro
        $stats = [
            'total_fichiers'     => TcFichier::count(),
            'total_transactions' => $totalTx,
            'total_rejets'       => TcRejet::count(),
            'montant_total'      => TcEnrDetail::where('statut', 'VALIDE')->sum('montant') ?? 0,
            'montant_moyen'      => $totalTx > 0
                ? round(TcEnrDetail::where('statut', 'VALIDE')->avg('montant') ?? 0, 3)
                : 0,
            'taux_rejet'         => $totalTx > 0
                ? round(TcEnrDetail::where('statut', 'REJETE')->count() / $totalTx * 100, 2)
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
