<?php

namespace App\Services\Ml;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Validation\RibValidatorService;

/**
 * Service d'estimation de rejet par ML.
 *
 * Construit le payload de 10 features attendues par le modèle Flask
 * (port 5000 par défaut), appelle l'API /predict, et applique le
 * "boost SIBTEL" : si un code de rejet officiel SIBTEL est déjà
 * détecté dans la transaction, on force le score à minimum 75 (rouge)
 * pour éviter qu'un faux négatif du modèle masque un rejet certain.
 */
class MlScoringService
{
    public function __construct(
        private RibValidatorService $ribValidator
    ) {}

    /**
     * Estime le risque de rejet d'une transaction.
     *
     * @param  array  $detail  Détail de la transaction (motif_rejet, RIBs, montant, etc.)
     * @param  array  $global  Enregistrement global (optionnel, pour contexte futur)
     * @return array  ['score'=>int|null, 'couleur'=>string|null, 'rejete'=>bool|null, 'proba'=>float|null, 'explications'=>array]
     */
    public function estimer(array $detail, array $global = []): array
    {
        try {
            $mlUrl    = config('services.ml.url', 'http://127.0.0.1:5000');
            $rib_don  = trim($detail['rib_donneur']      ?? '');
            $rib_dest = trim($detail['rib_beneficiaire'] ?? '');

            // ── Validation des RIBs par modulo 97 (delegue au RibValidatorService) ──
            $rib_don_valide  = ($rib_don  !== '' && $this->ribValidator->estValide($rib_don))  ? 1 : 0;
            $rib_dest_valide = ($rib_dest !== '' && $this->ribValidator->estValide($rib_dest)) ? 1 : 0;

            // ── Codes banques (extraits des RIBs) ─────────────────
            $code_banque_don  = strlen($rib_don)  >= 2 ? substr($rib_don,  0, 2) : '26';
            $code_banque_dest = strlen($rib_dest) >= 2 ? substr($rib_dest, 0, 2) : '26';
            $meme_banque      = ($code_banque_don === $code_banque_dest) ? 1 : 0;

            // ── Calcul echeance depassee ──────────────────────────
            $echeance_depassee = $this->calculerEcheanceDepassee($detail);

            // ── Construction du payload (10 features alignees avec le notebook) ──
            $payload = [
                'type_valeur'             => $detail['type_valeur']       ?? '10',
                'montant'                 => (float)($detail['montant']   ?? 0),
                'code_banque_don'         => $code_banque_don,
                'code_banque_dest'        => $code_banque_dest,
                'rib_donneur_valide'      => $rib_don_valide,
                'rib_beneficiaire_valide' => $rib_dest_valide,
                'echeance_depassee'       => $echeance_depassee,
                'meme_banque'             => $meme_banque,
                'situation_donneur'       => $detail['situation_donneur'] ?? '0',
                'type_compte'             => $detail['type_compte']       ?? '1',
            ];

            $response = Http::timeout(5)->post("{$mlUrl}/predict", $payload);

            if ($response->successful()) {
                return $this->appliquerBoostSibtel($response->json(), $detail);
            }

            return $this->resultatVide();

        } catch (\Exception $e) {
            Log::warning('ML estimation failed: ' . $e->getMessage());
            return $this->resultatVide();
        }
    }

    /**
     * Boost SIBTEL : si un code de rejet officiel est detecte, on force
     * le score a minimum 75 pour eviter un faux negatif du modele.
     */
    private function appliquerBoostSibtel(array $result, array $detail): array
    {
        $motifRejet = trim($detail['motif_rejet'] ?? '00000000');
        $aDejaUnRejet = !empty($motifRejet) && $motifRejet !== '00000000';

        if ($aDejaUnRejet && ($result['score'] ?? 0) < 75) {
            $result['score']   = 80;
            $result['couleur'] = 'rouge';
            $result['rejete']  = true;
            $result['explications'] = $result['explications'] ?? [];
            array_unshift($result['explications'], [
                'feature' => 'motif_rejet',
                'libelle' => 'Code de rejet SIBTEL detecte',
                'detail'  => 'Code : ' . substr($motifRejet, 0, 2),
                'gravite' => 'haute',
            ]);
            $result['explications'] = array_slice($result['explications'], 0, 3);
        }

        return $result;
    }

    /**
     * Detecte si une echeance est depassee.
     * Gere les formats Ymd (20251215) et dmY (15122025).
     */
    private function calculerEcheanceDepassee(array $detail): int
    {
        $dateEcheance = $detail['date_echeance']
                     ?? $detail['date_compensation']
                     ?? '';

        if (empty($dateEcheance) || strlen($dateEcheance) < 8) {
            return 0;
        }

        try {
            $dateStr = preg_replace('/[^0-9]/', '', $dateEcheance);
            if (strlen($dateStr) !== 8) {
                return 0;
            }

            $annee = (int)substr($dateStr, 0, 4);
            $date = ($annee >= 1900 && $annee <= 2100)
                ? \DateTime::createFromFormat('Ymd', $dateStr)
                : \DateTime::createFromFormat('dmY', $dateStr);

            return ($date && $date < new \DateTime('today')) ? 1 : 0;

        } catch (\Exception $e) {
            return 0;
        }
    }

    private function resultatVide(): array
    {
        return [
            'score'        => null,
            'couleur'      => null,
            'rejete'       => null,
            'proba'        => null,
            'explications' => [],
        ];
    }
}