<?php

namespace App\Livewire\Rejets;

use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Http;
use App\Models\TcRejet;

class Estimation extends Component
{
    #[Validate('required|integer')]
    public $montant;

    #[Validate('required|string')]
    public $type_valeur;

    #[Validate('required|string')]
    public $code_banque_don;

    #[Validate('required|string')]
    public $code_banque_dest;

    #[Validate('required|string')]
    public $sens;

    #[Validate('required|string')]
    public $situation_donneur;

    #[Validate('required|string')]
    public $type_compte;

    #[Validate('required|integer')]
    public $rib_donneur_valide = 1;

    #[Validate('required|integer')]
    public $rib_beneficiaire_valide = 1;

    #[Validate('required|integer')]
    public $jour_semaine = 1;

    #[Validate('required|integer')]
    public $est_fin_mois = 0;

    #[Validate('required|integer')]
    public $echeance_depassee = 0;

    #[Validate('required|numeric')]
    public $ratio_provision = 0.5;

    #[Validate('required|numeric')]
    public $montant_provision = 0;

    public $score = null;
    public $couleur = null;
    public $rejete = null;
    public $proba = null;
    public $loading = false;
    public $error = null;

    /**
     * Appelle le serveur ML pour prédire le risque de rejet
     */
    public function estimer()
    {
        $this->validate();
        $this->loading = true;
        $this->error = null;

        try {
            // Préparer les données pour le ML
            $data = [
                'montant'                  => (int) $this->montant,
                'type_valeur'              => $this->type_valeur,
                'code_banque_don'          => $this->code_banque_don,
                'code_banque_dest'         => $this->code_banque_dest,
                'rib_donneur_valide'       => (int) $this->rib_donneur_valide,
                'rib_beneficiaire_valide'  => (int) $this->rib_beneficiaire_valide,
                'jour_semaine'             => (int) $this->jour_semaine,
                'est_fin_mois'             => (int) $this->est_fin_mois,
                'echeance_depassee'        => (int) $this->echeance_depassee,
                'ratio_provision'          => (float) $this->ratio_provision,
                'montant_provision'        => (float) $this->montant_provision,
                'sens'                     => $this->sens,
                'situation_donneur'        => $this->situation_donneur,
                'type_compte'              => $this->type_compte,
            ];

            // Appeler le serveur ML Flask
            $mlServerUrl = config('services.ml.url') ?? 'http://ml:5000';
            $response = Http::timeout(10)
                ->post("{$mlServerUrl}/predict", $data);

            if ($response->successful()) {
                $result = $response->json();
                $this->score = $result['score'];
                $this->couleur = $result['couleur'];
                $this->rejete = $result['rejete'];
                $this->proba = $result['proba'];

                // Log de l'estimation
                \Illuminate\Support\Facades\Log::info('Estimation ML effectuée', [
                    'montant' => $this->montant,
                    'score' => $this->score,
                    'couleur' => $this->couleur,
                ]);
            } else {
                $this->error = "Erreur serveur ML: " . $response->status();
            }
        } catch (\Exception $e) {
            $this->error = "Erreur de connexion: " . $e->getMessage();
            \Illuminate\Support\Facades\Log::error('Erreur ML', ['exception' => $e]);
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Estimation batch pour plusieurs transactions
     */
    public function estimer_batch($transactions)
    {
        try {
            $mlServerUrl = config('services.ml.url') ?? 'http://ml:5000';
            $response = Http::timeout(30)
                ->post("{$mlServerUrl}/predict-batch", [
                    'transactions' => $transactions
                ]);

            if ($response->successful()) {
                return $response->json();
            }
            return ['error' => $response->status()];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Teste la connexion au serveur ML
     */
    public function tester_ml()
    {
        try {
            $mlServerUrl = config('services.ml.url') ?? 'http://ml:5000';
            $response = Http::timeout(5)->get("{$mlServerUrl}/health");
            
            if ($response->successful()) {
                $this->dispatch('notification', ['type' => 'success', 'message' => 'Serveur ML connecté ✓']);
            } else {
                $this->dispatch('notification', ['type' => 'error', 'message' => 'Serveur ML inaccessible']);
            }
        } catch (\Exception $e) {
            $this->dispatch('notification', ['type' => 'error', 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.rejets.estimation');
    }
}
