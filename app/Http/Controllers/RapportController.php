<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExportService;

class RapportController extends Controller
{
    public function index()
    {
        return view('rapport.index');
    }

    public function generer(Request $request, ExportService $service)
    {
        $date   = $request->get('date', now()->format('Y-m-d'));
        $type   = $request->get('type', 'journalier');
        $format = $request->get('format', 'pdf');

        try {
            $donnees = $service->getDonneesRapport($date, $type);

            if ($format === 'pdf') {
                $path     = $service->genererPdf($donnees);
                $filename = 'rapport_btl_' . now()->format('Ymd_His') . '.pdf';
                $mime     = 'application/pdf';
            } else {
                $path     = $service->genererExcel($donnees);
                $filename = 'rapport_btl_' . now()->format('Ymd_His') . '.xlsx';
                $mime     = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            }

            return response()->download($path, $filename, ['Content-Type' => $mime])
                ->deleteFileAfterSend(false);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur génération : ' . $e->getMessage());
        }
    }
}
