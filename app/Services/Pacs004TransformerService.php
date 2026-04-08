<?php

namespace App\Services;

use App\Models\TcFichier;
use App\Models\TcRejet;
use App\Models\TcPacs004;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Pacs004TransformerService
{
    private string $bicBanque;
    private string $codeCentre;

    public function __construct()
    {
        $this->bicBanque  = 'BSIETNTX';
        $this->codeCentre = '01';
    }

    // ── Point d'entrée principal ──────────────────────────────────

    public function genererPourFichier(int $fichierId): array
    {
        $resultat = [
            'succes'     => false,
            'pacs004_id' => null,
            'message'    => '',
            'nb_rejets'  => 0,
        ];

        DB::beginTransaction();

        try {
            $fichier = TcFichier::with(['rejets.detail', 'enrGlobaux'])
                                ->findOrFail($fichierId);

            $rejets = $fichier->rejets()->with('detail')->get();

            if ($rejets->isEmpty()) {
                $resultat['message'] = 'Aucun rejet trouvé pour ce fichier.';
                return $resultat;
            }

            $global  = $fichier->enrGlobaux()->first();
            $donnees = $this->preparerDonnees($fichier, $global, $rejets);
            $xml     = $this->genererXml($donnees);
            $valide  = $this->validerXml($xml);
            $msgId   = $this->genererMsgId($fichier);

            $pacs004 = TcPacs004::create([
                'fichier_id'          => $fichier->id,
                'rejet_id'            => $rejets->first()->id ?? null,
                'msg_id'              => $msgId,
                'cre_dt_tm'           => now(),
                'nb_of_txs'           => $rejets->count(),
                'sttlm_mtd'           => 'CLRG',
                'clr_sys_prtry'       => $global->code_centre_regional ?? $this->codeCentre,
                'instg_agt_bic'       => $this->bicBanque,
                'rtr_id'              => $global->numero_lot ?? null,
                'orgnl_end_to_end_id' => $donnees['transactions'][0]['orgnl_end_to_end_id'] ?? null,
                'orgnl_tx_id'         => $donnees['transactions'][0]['orgnl_tx_id'] ?? null,
                'devise'              => $fichier->code_devise ?? 'TND',
                'chrg_br'             => 'SLEV',
                'motif_rejet'         => $rejets->first()->code_rejet ?? null,
                'libelle_rejet'       => $rejets->first()->motif_rejet ?? null,
                'contenu_xml'         => $xml,
                'valide_xsd'          => $valide,
                'statut'              => 'GENERE',
            ]);

            Log::info("Pacs.004 généré pour fichier #{$fichierId}", [
                'pacs004_id' => $pacs004->id,
                'nb_rejets'  => $rejets->count(),
                'msg_id'     => $msgId,
            ]);

            DB::commit();

            $resultat = [
                'succes'     => true,
                'pacs004_id' => $pacs004->id,
                'message'    => "Pacs.004 généré avec succès ({$rejets->count()} rejet(s))",
                'nb_rejets'  => $rejets->count(),
                'xml'        => $xml,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur génération Pacs.004 : " . $e->getMessage());
            $resultat['message'] = "Erreur : " . $e->getMessage();
        }

        return $resultat;
    }

    // ── Préparer les données ──────────────────────────────────────

    private function preparerDonnees(TcFichier $fichier, $global, $rejets): array
    {
        $transactions = [];

        foreach ($rejets as $rejet) {
            $detail = $rejet->detail;

            $transactions[] = [
                'rtr_id'              => $global->numero_lot ?? ('RTR-' . $rejet->id),
                'orgnl_end_to_end_id' => $detail->numero_virement ?? $rejet->id,
                'orgnl_tx_id'         => $detail->numero_virement ?? $rejet->id,
                'orgnl_tx_ref'        => $fichier->nom_fichier,
                'montant'             => $detail->montant ?? 0,
                'devise'              => $fichier->code_devise ?? 'TND',
                'dbtr_nm'             => $detail->nom_donneur ?? 'N/A',
                'dbtr_acct_id'        => $detail->rib_donneur ?? '',
                'dbtr_agt_bic'        => $this->bicBanque,
                'cdtr_nm'             => $detail->nom_beneficiaire ?? 'N/A',
                'cdtr_acct_id'        => $detail->rib_beneficiaire ?? '',
                'cdtr_agt_bic'        => $this->bicBanque,
                'motif_rejet'         => $rejet->code_rejet ?? '',
                'libelle_rejet'       => $rejet->motif_rejet ?? '',
                'type_valeur'         => $fichier->type_valeur ?? '',
                'detail'              => $detail,
                'rejet'               => $rejet,
            ];
        }

        return [
            'fichier'      => $fichier,
            'global'       => $global,
            'transactions' => $transactions,
            'msg_id'       => $this->genererMsgId($fichier),
            'code_centre'  => $global->code_centre_regional ?? $this->codeCentre,
        ];
    }

    // ── Générer XML Pacs.004 ──────────────────────────────────────

    private function genererXml(array $donnees): string
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><Document/>'
        );
        $xml->addAttribute(
            'xmlns',
            'urn:iso:std:iso:20022:tech:xsd:pacs.004.001.09'
        );

        $pmtRtr = $xml->addChild('PmtRtr');

        // ── GrpHdr ───────────────────────────────────────────────
        $grpHdr = $pmtRtr->addChild('GrpHdr');
        $grpHdr->addChild('MsgId',   $donnees['msg_id']);
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', count($donnees['transactions']));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $donnees['code_centre']);

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', $donnees['fichier']->type_valeur ?? '');
        $ctgyPurp  = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '22');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        // ── TxInf ────────────────────────────────────────────────
        foreach ($donnees['transactions'] as $tx) {
            $txInf = $pmtRtr->addChild('TxInf');

            $txInf->addChild('RtrId', (string)($tx['rtr_id'] ?? ''));

            $txInf->addChild('OrgnlEndToEndId', str_pad(
                (string)($tx['orgnl_end_to_end_id'] ?? ''), 7, '0', STR_PAD_LEFT
            ));

            $txInf->addChild('OrgnlTxId', str_pad(
                (string)($tx['orgnl_tx_id'] ?? ''), 7, '0', STR_PAD_LEFT
            ));

            $amt = $txInf->addChild(
                'RtrdIntrBkSttlmAmt',
                number_format((float)($tx['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', $this->convertirDevise($tx['devise'] ?? 'TND'));

            $txInf->addChild('ChrgBr', 'SLEV');

            // OrgnlTxRef
            $orgnlTxRef = $txInf->addChild('OrgnlTxRef');

            $intrAmt = $orgnlTxRef->addChild(
                'IntrBkSttlmAmt',
                number_format((float)($tx['montant'] ?? 0), 3, '.', '')
            );
            $intrAmt->addAttribute('Ccy', $this->convertirDevise($tx['devise'] ?? 'TND'));

            $dbtr    = $orgnlTxRef->addChild('Dbtr');
            $pty     = $dbtr->addChild('Pty');
            $pty->addChild('Nm', $tx['dbtr_nm'] ?? 'N/A');

            $dbtrAcct = $orgnlTxRef->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id', str_pad($tx['dbtr_acct_id'] ?? '', 20, '0', STR_PAD_LEFT));

            $dbtrAgt   = $orgnlTxRef->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $tx['dbtr_agt_bic'] ?? $this->bicBanque);

            $cdtrAgt   = $orgnlTxRef->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $tx['cdtr_agt_bic'] ?? $this->bicBanque);

            $cdtrAcct = $orgnlTxRef->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id', str_pad($tx['cdtr_acct_id'] ?? '', 20, '0', STR_PAD_LEFT));

            // SplmtryData
            $this->ajouterSplmtryData($txInf, $tx, $donnees['fichier']->type_valeur ?? '');
        }

        return $this->formaterXml($xml->asXML());
    }

    // ── Données supplémentaires ───────────────────────────────────

    private function ajouterSplmtryData(\SimpleXMLElement $txInf, array $tx, string $typeValeur): void
    {
        $splmtry  = $txInf->addChild('SplmtryData');
        $splmtry->addChild('PlcAndNm', 'REJET');
        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');

        $suppData->addChild('MotifRejet', $tx['motif_rejet'] ?? '');

        $detail = $tx['detail'] ?? null;

        match(true) {
            $typeValeur === '20'
                => $this->splmtryPrelevement($suppData, $detail),

            in_array($typeValeur, ['30','31','32','33'])
                => $this->splmtryCheque($suppData, $detail),

            in_array($typeValeur, ['40','41','42','43'])
                => $this->splmtryLettreChange($suppData, $detail),

            default
                => $this->splmtryVirement($suppData, $detail),
        };
    }

    private function splmtryPrelevement(\SimpleXMLElement $suppData, $detail): void
    {
        if (!$detail) return;
        $suppData->addChild('ReferenceContratDomiciliation', $detail->ref_contrat ?? '');
        $suppData->addChild('CodeNational',       $detail->code_emetteur ?? '');
        $suppData->addChild('CodeMAJ',            '1');
        $suppData->addChild('DateMAJ',            now()->format('Y-m-d'));
        $suppData->addChild('ZoneLibre',          '');
        $suppData->addChild('NumeroDomiciliation', $detail->ref_contrat ?? '');
        $suppData->addChild('CodePayeur',         $detail->motif_rejet ?? '');
        if (!empty($detail->date_echeance)) {
            $suppData->addChild('DateEcheance',   $detail->date_echeance);
        }
        $suppData->addChild('LibellePrelevement', $detail->libelle_prelevement ?? '');
    }

    private function splmtryCheque(\SimpleXMLElement $suppData, $detail): void
    {
        if (!$detail) return;
        if (!empty($detail->date_emission)) {
            $suppData->addChild('DateEmission',   $detail->date_emission);
        }
        $suppData->addChild('ZoneLibre',          '');
        $suppData->addChild('LieuEmission',       $detail->lieu_emission ?? '');
        $suppData->addChild('SituationBenef',     $detail->situation_beneficiaire ?? '');
        $suppData->addChild('NatureCompte',       $detail->nature_compte ?? '');
        if (!empty($detail->montant_provision)) {
            $suppData->addChild('MontantProvision', number_format((float)$detail->montant_provision, 3, '.', ''));
        }
        $suppData->addChild('NbreEnregComp',      '0');
    }

    private function splmtryLettreChange(\SimpleXMLElement $suppData, $detail): void
    {
        if (!$detail) return;
        if (!empty($detail->date_echeance)) {
            $suppData->addChild('DateEcheance',        $detail->date_echeance);
        }
        if (!empty($detail->date_echeance_initiale)) {
            $suppData->addChild('DateEcheanceInitiale', $detail->date_echeance_initiale);
        }
        $suppData->addChild('LieuCreation',            $detail->lieu_creation ?? '');
        $suppData->addChild('NatureCompte',            $detail->nature_compte ?? '');
        if (!empty($detail->montant_interets)) {
            $suppData->addChild('MontantInterets', number_format((float)$detail->montant_interets, 3, '.', ''));
        }
        $suppData->addChild('NbreEnregComp',           '0');
    }

    private function splmtryVirement(\SimpleXMLElement $suppData, $detail): void
    {
        if (!$detail) return;
        $suppData->addChild('ZoneLibre',               '');
        $suppData->addChild('ReferenceDossierPaiement', $detail->reference_dossier ?? '');
        $suppData->addChild('SituationDonneur',        $detail->situation_donneur ?? '');
        $suppData->addChild('TypeCompteDonneur',       $detail->type_compte_donneur ?? '');
        $suppData->addChild('NbreEnregComp',           '0');
        $suppData->addChild('CodeSuivi',               '');
    }

    // ── Utilitaires ───────────────────────────────────────────────

    private function genererMsgId(TcFichier $fichier): string
    {
        return 'RTR-' . str_pad($fichier->id, 6, '0', STR_PAD_LEFT)
             . '-' . now()->format('YmdHis');
    }

    private function convertirDevise(string $code): string
    {
        return match($code) {
            '788'   => 'TND',
            '840'   => 'USD',
            '978'   => 'EUR',
            '826'   => 'GBP',
            default => $code,
        };
    }

    private function validerXml(string $xml): bool
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function formaterXml(string $xml): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($xml);
        return $dom->saveXML();
    }

    public function getTypeMessage(): string
    {
        return 'pacs.004.001.09';
    }
}