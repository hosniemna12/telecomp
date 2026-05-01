<?php

namespace App\Services;

use App\Models\TcFichier;
use App\Models\TcEnrDetail;
use App\Models\TcRejet;
use Carbon\Carbon;

class ExportService
{
    private string $python = 'C:/Users/hosni/AppData/Local/Programs/Python/Python313/python.exe';

    private function cleanString(?string $val): string
    {
        if ($val === null) return '';
        // Nettoyer les caractères non-UTF8
        $clean = iconv('UTF-8', 'UTF-8//IGNORE', $val);
        return $clean !== false ? $clean : '';
    }

    public function getDonneesRapport(string $date = null, string $type = 'journalier'): array
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        switch ($type) {
            case 'hebdomadaire':
                $debut = $date->copy()->startOfWeek();
                $fin   = $date->copy()->endOfWeek();
                break;
            case 'mensuel':
                $debut = $date->copy()->startOfMonth();
                $fin   = $date->copy()->endOfMonth();
                break;
            default:
                $debut = $date->copy()->startOfDay();
                $fin   = $date->copy()->endOfDay();
        }

        $fichiers = TcFichier::with(['enregistrementsDetails', 'rejets', 'uploader', 'valideur'])
            ->whereBetween('created_at', [$debut, $fin])
            ->orderBy('created_at', 'desc')
            ->get();

        $details = TcEnrDetail::whereHas('fichier', function($q) use ($debut, $fin) {
            $q->whereBetween('created_at', [$debut, $fin]);
        })->get();

        $rejets = TcRejet::whereHas('fichier', function($q) use ($debut, $fin) {
            $q->whereBetween('created_at', [$debut, $fin]);
        })->with('fichier')->get();

        $typeNames = ['10'=>'Virement','20'=>'Prélèvement','30'=>'Chèque','31'=>'CNP','32'=>'ARP','40'=>'LDC','84'=>'Papillon'];

        return [
            'periode'    => ['debut' => $debut, 'fin' => $fin, 'type' => $type],
            'fichiers'   => $fichiers,
            'details'    => $details,
            'rejets'     => $rejets,
            'type_names' => $typeNames,
            'stats'      => [
                'total_fichiers'     => $fichiers->count(),
                'total_traites'      => $fichiers->whereIn('statut', ['TRAITE','VALIDE'])->count(),
                'total_erreurs'      => $fichiers->where('statut', 'ERREUR')->count(),
                'total_en_attente'   => $fichiers->where('statut', 'EN_ATTENTE_VALIDATION')->count(),
                'total_transactions' => $details->count(),
                'total_valides'      => $details->where('statut', 'VALIDE')->count(),
                'total_rejetes'      => $details->where('statut', 'REJETE')->count(),
                'montant_total'      => (float)$details->where('statut', 'VALIDE')->sum('montant'),
                'total_rejets'       => $rejets->count(),
            ],
            'par_type'   => $fichiers->groupBy('type_valeur')->map(function($group) use ($typeNames) {
                $tv = $group->first()->type_valeur;
                return [
                    'type'    => $typeNames[$tv] ?? $tv,
                    'count'   => $group->count(),
                    'montant' => (float)$group->sum('montant_total'),
                ];
            }),
            'genere_le'  => now()->format('d/m/Y H:i:s'),
            'genere_par' => auth()->user()->name ?? 'Système',
        ];
    }

    private function preparerDonneesPython(array $donnees): array
    {
        return [
            'periode'    => [
                'debut' => $donnees['periode']['debut']->format('d/m/Y'),
                'fin'   => $donnees['periode']['fin']->format('d/m/Y'),
                'type'  => $donnees['periode']['type'],
            ],
            'stats'      => $donnees['stats'],
            'par_type'   => $donnees['par_type']->toArray(),
            'genere_le'  => $donnees['genere_le'],
            'genere_par' => $this->cleanString($donnees['genere_par']),
            'fichiers'   => $donnees['fichiers']->map(fn($f) => [
                'nom'          => $this->cleanString($f->nom_fichier),
                'type'         => $this->cleanString($donnees['type_names'][$f->type_valeur] ?? $f->type_valeur),
                'statut'       => $this->cleanString($f->statut),
                'transactions' => (int)$f->nb_transactions,
                'rejets'       => (int)$f->nb_rejets,
                'montant'      => (float)$f->montant_total,
                'date'         => $f->created_at?->format('d/m/Y H:i') ?? '',
                'uploade_par'  => $this->cleanString($f->uploader?->name ?? ''),
                'valide_par'   => $this->cleanString($f->valideur?->name ?? ''),
            ])->toArray(),
            'details'    => $donnees['details']->map(fn($d) => [
                'fichier_id'       => (int)$d->fichier_id,
                'rib_donneur'      => $this->cleanString($d->rib_donneur),
                'nom_donneur'      => $this->cleanString($d->nom_donneur),
                'rib_beneficiaire' => $this->cleanString($d->rib_beneficiaire),
                'nom_beneficiaire' => $this->cleanString($d->nom_beneficiaire),
                'montant'          => (float)$d->montant,
                'statut'           => $this->cleanString($d->statut),
                'motif_operation'  => $this->cleanString($d->motif_operation),
            ])->toArray(),
            'rejets'     => $donnees['rejets']->map(fn($r) => [
                'fichier' => $this->cleanString($r->fichier?->nom_fichier ?? ''),
                'code'    => $this->cleanString($r->code_rejet),
                'motif'   => $this->cleanString($r->motif_rejet),
                'etape'   => $this->cleanString($r->etape_detection),
            ])->take(50)->toArray(),
        ];
    }

    public function genererPdf(array $donnees): string
    {
        $tempDir  = storage_path('app/temp');
        $jsonPath = $tempDir . '/rapport_data.json';
        $pdfPath  = $tempDir . '/rapport_btl.pdf';

        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $data = $this->preparerDonneesPython($donnees);
        file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));

        $scriptPath = base_path('scripts/generate_pdf.py');
        $python     = $this->python;
        $cmd        = "\"{$python}\" \"{$scriptPath}\" \"{$jsonPath}\" \"{$pdfPath}\" 2>&1";

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($pdfPath)) {
            throw new \RuntimeException("Erreur génération PDF: " . implode("\n", $output));
        }

        return $pdfPath;
    }

    public function genererExcel(array $donnees): string
    {
        $tempDir   = storage_path('app/temp');
        $jsonPath  = $tempDir . '/rapport_data.json';
        $xlsxPath  = $tempDir . '/rapport_btl.xlsx';

        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $data = $this->preparerDonneesPython($donnees);
        file_put_contents($jsonPath, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));

        $scriptPath = base_path('scripts/generate_excel.py');
        $python     = $this->python;
        $cmd        = "\"{$python}\" \"{$scriptPath}\" \"{$jsonPath}\" \"{$xlsxPath}\" 2>&1";

        exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($xlsxPath)) {
            throw new \RuntimeException("Erreur génération Excel: " . implode("\n", $output));
        }

        return $xlsxPath;
    }
}
