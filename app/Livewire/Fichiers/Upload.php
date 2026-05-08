<?php

namespace App\Livewire\Fichiers;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Services\FichierTraitementService;
use App\Services\AuditService;

#[Layout('layouts.app')]
class Upload extends Component
{
    use WithFileUploads;

    public $fichier           = null;
    public string $typeValeur = '';
    public bool $enTraitement = false;
    public array $resultat    = [];
    public string $erreur     = '';

    protected function rules(): array
    {
        return [
            'typeValeur' => 'required|in:10,20,30,31,32,33,40,41,42,43,82,83,84',
            'fichier'    => 'required|file|max:10240',
        ];
    }

    protected $messages = [
        'typeValeur.required' => 'Veuillez sélectionner le type de fichier.',
        'typeValeur.in'       => 'Type de fichier invalide.',
        'fichier.required'    => 'Veuillez sélectionner un fichier.',
        'fichier.max'         => 'Le fichier ne doit pas dépasser 10 MB.',
    ];

    public function updatedFichier(): void
    {
        $this->resultat = [];
        $this->erreur   = '';

        if (!$this->fichier) return;

        $extension = strtolower($this->fichier->getClientOriginalExtension());
        if (!in_array($extension, ['env', 'pak', 'txt'])) {
            $this->erreur = 'Format non supporté. Utilisez un fichier .ENV ou .PAK';
            return;
        }

        $nom     = strtoupper($this->fichier->getClientOriginalName());
        $parties = explode('-', $nom);

        if (isset($parties[2]) && is_numeric($parties[2])) {
            $typeDetecte  = $parties[2];
            $typesValides = ['10','20','30','31','32','33','40','41','42','43','82','83','84'];
            if (in_array($typeDetecte, $typesValides)) {
                $this->typeValeur = $typeDetecte;
            }
        }
    }

    public function traiter(
        FichierTraitementService $service,
        AuditService $audit
    ): void {
        try {
            $this->validate();

            $extension = strtolower($this->fichier->getClientOriginalExtension());
            if (!in_array($extension, ['env', 'pak', 'txt'])) {
                $this->erreur = 'Le fichier doit être de type .ENV ou .PAK';
                return;
            }

            $this->enTraitement = true;
            $this->resultat     = [];
            $this->erreur       = '';

            $nomOriginal = $this->fichier->getClientOriginalName();

            $dossier = storage_path('app/telecompensation');
            if (!is_dir($dossier)) {
                mkdir($dossier, 0755, true);
            }

            $cheminRelatif = $this->fichier->storeAs('telecompensation', $nomOriginal, 'local');
            $cheminComplet = storage_path('app/' . $cheminRelatif);

            if (!file_exists($cheminComplet)) {
                throw new \RuntimeException("Fichier introuvable après upload : {$cheminComplet}");
            }

            $this->resultat = $service->traiter($cheminComplet, $this->typeValeur);

            if ($this->resultat['succes']) {
                $audit->log(
                    'UPLOAD', 'FICHIERS',
                    "Upload : {$nomOriginal} — Type : {$this->typeValeur} — " .
                    "{$this->resultat['stats']['valides']} valides, " .
                    "{$this->resultat['stats']['rejetes']} rejetés",
                    [],
                    ['fichier_id' => $this->resultat['fichier_id'] ?? null]
                );
                $this->erreur  = '';
                $this->fichier = null;

            } else {
                $audit->log('UPLOAD', 'FICHIERS',
                    "Échec upload : {$nomOriginal}", [], [], 'FAILED');
                $this->erreur = $this->resultat['message'] ?? 'Erreur lors du traitement.';
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $erreurs      = $e->errors();
            $this->erreur = implode(', ', array_merge(...array_values($erreurs)));
        } catch (\Exception $e) {
            $this->erreur = 'Erreur : ' . $e->getMessage();
        } finally {
            $this->enTraitement = false;
        }
    }

    public function reinitialiser(): void
    {
        $this->fichier      = null;
        $this->typeValeur   = '';
        $this->resultat     = [];
        $this->erreur       = '';
        $this->enTraitement = false;
    }

    public function render()
    {
        return view('livewire.fichiers.upload');
    }
}