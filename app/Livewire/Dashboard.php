<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcEnrDetail;
use App\Models\TcRejet;
use App\Models\TcXmlProduit;
use App\Models\TcLogsTraitement;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $periode = '30';

    public function refresh(): void {}

    public function render()
    {
        $stats = [
            'fichiers'       => TcFichier::count(),
            'transactions'   => TcEnrDetail::count(),
            'rejets'         => TcRejet::where('traite', false)->count(),
            'xml'            => TcXmlProduit::count(),
            'traites'        => TcFichier::where('statut', 'TRAITE')->count(),
            'traite_partiel' => TcFichier::where('statut', 'TRAITE_PARTIEL')->count(),
            'erreurs'        => TcFichier::where('statut', 'ERREUR')->count(),
            'en_cours'       => TcFichier::where('statut', 'EN_COURS')->count(),
            'montant_total'  => TcEnrDetail::where('statut', 'VALIDE')->sum('montant') ?? 0,
        ];

        $derniersFichiers = TcFichier::latest()->take(8)->get();

        $derniersLogs = TcLogsTraitement::with('fichier')
            ->latest('created_at')
            ->take(5)
            ->get();

        $statsParType = [
            'virements'    => TcFichier::where('type_valeur', '10')->count(),
            'prelevements' => TcFichier::where('type_valeur', '20')->count(),
            'cheques'      => TcFichier::whereIn('type_valeur', ['30','31','32','33'])->count(),
            'ldc'          => TcFichier::whereIn('type_valeur', ['40','41','42','43'])->count(),
            'papillons'    => TcFichier::where('type_valeur', '84')->count(),
        ];

        // Détecte le driver BD (Oracle utilise TRUNC, SQLite utilise DATE)
        $isOracle = DB::getDriverName() === 'oracle';
        $dateFunc = $isOracle ? 'TRUNC' : 'DATE';
        
        $fichiersParJour = TcFichier::select(
                DB::raw("TRUNC(date_reception) as jour"),
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN statut = 'TRAITE' THEN 1 ELSE 0 END) as traites"),
                DB::raw("SUM(CASE WHEN statut = 'ERREUR' THEN 1 ELSE 0 END) as erreurs")
            )
            ->where('date_reception', '>=', now()->subDays((int)$this->periode))
            ->groupBy(DB::raw("TRUNC(date_reception)"))
            ->orderBy(DB::raw("TRUNC(date_reception)"))
            ->get();

        $topTransactions = TcEnrDetail::where('statut', 'VALIDE')
            ->orderBy('montant', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', compact(
            'stats',
            'derniersFichiers',
            'derniersLogs',
            'statsParType',
            'fichiersParJour',
            'topTransactions'
        ));
    }
}