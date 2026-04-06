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
        ];
    }

    protected $messages = [
        'typeValeur.required' => 'Veuillez sélectionner le type de fichier.',
        'typeValeur.in'       => 'Type de fichier invalide.',
    ];

    public function updatedFichier(): void
    {
        $this->resultat = [];
        $this->erreur   = '';

        if ($this->fichier) {
            $nom     = strtoupper($this->fichier->getClientOriginalName());
            $parties = explode('-', $nom);
            if (isset($parties[2]) && is_numeric($parties[2])) {
                $type = $parties[2];
                if (in_array($type, ['10', '20', '30', '31', '32'])) {
                    $this->typeValeur = $type;
                }
            }
        }
    }

    public function traiter(): void
    {
        $audit = app(AuditService::class);

        try {
            $this->validate();

            if (!$this->fichier) {
                $this->erreur = 'Veuillez sélectionner un fichier.';
                return;
            }

            $extension = strtolower($this->fichier->getClientOriginalExtension());
            if (!in_array($extension, ['env', 'txt', 'pak', 'ENV', 'PAK'])) {
                $this->erreur = 'Le fichier doit être de type .env, .pak ou .txt.';
                return;
            }

            $this->enTraitement = true;
            $this->resultat     = [];
            $this->erreur       = '';

            $nomOriginal = $this->fichier->getClientOriginalName();
            $dossier     = storage_path('app/telecompensation');

            if (!is_dir($dossier)) {
                mkdir($dossier, 0755, true);
            }

            $cheminRelatif = $this->fichier->storeAs(
                'telecompensation',
                $nomOriginal,
                'local'
            );

            $cheminComplet = storage_path('app/' . $cheminRelatif);

            if (!file_exists($cheminComplet)) {
                throw new \RuntimeException("Fichier introuvable : {$cheminComplet}");
            }

            $service        = app(FichierTraitementService::class);
            $this->resultat = $service->traiter($cheminComplet, $this->typeValeur);

            if ($this->resultat['succes']) {
                $audit->log(
                    'UPLOAD', 'FICHIERS',
                    "Upload fichier : {$nomOriginal} — Type : {$this->typeValeur} — " .
                    "{$this->resultat['stats']['valides']} valides, " .
                    "{$this->resultat['stats']['rejetes']} rejetes",
                    [],
                    ['fichier_id' => $this->resultat['fichier_id'] ?? null]
                );
                $this->erreur  = '';
                $this->fichier = null;
            } else {
                $audit->log(
                    'UPLOAD', 'FICHIERS',
                    "Echec upload : {$nomOriginal}",
                    [], [], 'FAILED'
                );
                $this->erreur = $this->resultat['message'] ?? 'Erreur lors du traitement.';
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->erreur = 'Erreur de validation : ' .
                implode(', ', array_values($e->errors())[0] ?? []);
        } catch (\Exception $e) {
            try {
                $audit->log('UPLOAD', 'FICHIERS',
                    "Exception : " . $e->getMessage(), [], [], 'FAILED');
            } catch (\Exception $e2) {}
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