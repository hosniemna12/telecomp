<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\ExportService;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.app')]
class RapportExport extends Component
{
    public string $date      = '';
    public string $type      = 'journalier';
    public string $format    = 'pdf';
    public bool   $enCours   = false;
    public string $erreur    = '';
    public string $succes    = '';

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function generer(ExportService $service): void
    {
        $this->enCours = true;
        $this->erreur  = '';
        $this->succes  = '';

        try {
            $donnees = $service->getDonneesRapport($this->date, $this->type);

            if ($this->format === 'pdf') {
                $path = $service->genererPdf($donnees);
                $this->succes = 'PDF généré avec succès !';
                session()->put('rapport_path', $path);
                session()->put('rapport_format', 'pdf');
                $this->dispatch('rapport-pret', format: 'pdf');
            } else {
                $path = $service->genererExcel($donnees);
                $this->succes = 'Excel généré avec succès !';
                session()->put('rapport_path', $path);
                session()->put('rapport_format', 'xlsx');
                $this->dispatch('rapport-pret', format: 'xlsx');
            }
        } catch (\Exception $e) {
            $this->erreur = 'Erreur : ' . $e->getMessage();
        } finally {
            $this->enCours = false;
        }
    }

    public function render()
    {
        return view('livewire.rapport-export');
    }
}
