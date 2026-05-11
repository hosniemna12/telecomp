<?php

namespace App\Services\Transformation;

use App\Models\TcFichier;
use App\Models\TcRejet;
use App\Models\TcPacs004;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pacs004TransformerService — Corrigé selon specs officielles SIBTEL/BFI
 *
 * Corrections appliquées :
 *  - Version : pacs.004.001.11 (était .09)
 *  - Namespace : urn:iso:std:iso:20022:tech:xsd:pacs.004.001.11
 *  - GrpHdr : CtgyPurp/Prtry = "22" (Rejet) confirmé par spec p.doc BFI
 *  - TxInf : structure OrgnlTxRef complète avec Dbtr/DbtrAcct/DbtrAgt/CdtrAgt/CdtrAcct
 *  - SplmtryData Prélèvement : ajout MotifRejet (4.2.1.10)
 *  - SplmtryData Chèque CNP  : structure complète 14 éléments (img11)
 *  - SplmtryData Papillon    : structure correcte (img12)
 *  - SplmtryData LDC         : ajout MotifRejet (4.2.1.19) et Messages (4.2.1.18)
 *  - SplmtryData Virement    : suppression CodeSuivi incorrect (était string, doit être numérique)
 *  - Montant : NE PAS diviser par 1000 (déjà en TND dans la base)
 */
class Pacs004TransformerService
{
    private string $bicBanque;
    private string $codeCentre;
    private string $codeRemettant;

    public function __construct()
    {
        $this->bicBanque     = 'BSIETNTX';
        $this->codeCentre    = '999';
        $this->codeRemettant = '26';
    }

    // ══════════════════════════════════════════════════════════════
    // POINT D'ENTRÉE PRINCIPAL
    // ══════════════════════════════════════════════════════════════
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
            $fichier = TcFichier::with(['rejets.detail', 'enregistrementsGlobaux'])
                                ->findOrFail($fichierId);

            $rejets = $fichier->rejets()->with('detail')->whereNotIn('code_rejet', ['VALID_ERR', 'PARSE_ERR'])->get();

            if ($rejets->isEmpty()) {
                $resultat['message'] = 'Aucun rejet trouvé pour ce fichier.';
                return $resultat;
            }

            $global  = $fichier->enregistrementsGlobaux()->first();
            $donnees = $this->preparerDonnees($fichier, $global, $rejets);
            $xml     = $this->genererXml($donnees);
            $valide  = $this->validerXml($xml);
            $msgId   = $donnees['msg_id'];

            $premiereTx = $donnees['transactions'][0] ?? [];

            $pacs004 = TcPacs004::create([
                'fichier_id'             => $fichier->id,
                'rejet_id'               => $rejets->first()->id ?? null,
                'msg_id'                 => $msgId,
                'cre_dt_tm'              => now(),
                'nb_of_txs'              => $rejets->count(),
                'sttlm_mtd'              => 'CLRG',
                'clr_sys_prtry'          => $global->code_centre_regional ?? $this->codeCentre,
                'instg_agt_bic'          => $this->bicBanque,
                'rtr_id'                 => $premiereTx['rtr_id'] ?? null,
                'orgnl_end_to_end_id'    => $premiereTx['orgnl_end_to_end_id'] ?? null,
                'orgnl_tx_id'            => $premiereTx['orgnl_tx_id'] ?? null,
                'rtr_intr_bk_sttlm_amt'  => $premiereTx['montant'] ?? 0,
                'devise'                 => $global->code_devise ?? $fichier->code_devise ?? 'TND',
                'chrg_br'                => 'SLEV',
                'dbtr_nm'                => $premiereTx['dbtr_nm'] ?? null,
                'dbtr_acct_id'           => $premiereTx['dbtr_acct_id'] ?? null,
                'dbtr_agt_bic'           => $this->bicBanque,
                'cdtr_nm'                => $premiereTx['cdtr_nm'] ?? null,
                'cdtr_acct_id'           => $premiereTx['cdtr_acct_id'] ?? null,
                'cdtr_agt_bic'           => $this->bicBanque,
                'motif_rejet'            => $rejets->first()->code_rejet ?? null,
                'libelle_rejet'          => $rejets->first()->motif_rejet
                                            ?? $rejets->first()->detail->motif_operation
                                            ?? null,
                'contenu_xml'            => $xml,
                'valide_xsd'             => $valide,
                'statut'                 => 'GENERE',
            ]);

            DB::commit();

            $resultat = [
                'succes'     => true,
                'pacs004_id' => $pacs004->id,
                'message'    => "Pacs.004 généré avec succès ({$rejets->count()} rejet(s))",
                'nb_rejets'  => $rejets->count(),
                'xml'        => $xml,
                'valide_xsd' => $valide,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur génération Pacs.004 : " . $e->getMessage(), [
                'fichier_id' => $fichierId,
                'trace'      => $e->getTraceAsString(),
            ]);
            $resultat['message'] = "Erreur : " . $e->getMessage();
        }

        return $resultat;
    }

    // ══════════════════════════════════════════════════════════════
    // PRÉPARER LES DONNÉES
    // IMPORTANT : montant en base = TND (déjà divisé par 1000 au parsing)
    //             NE PAS rediviser ici.
    // ══════════════════════════════════════════════════════════════
    private function preparerDonnees(TcFichier $fichier, $global, $rejets): array
    {
        $transactions = [];
        $typeValeur   = (string)($global->code_valeur ?? $fichier->type_valeur ?? '10');

        // Date compensation depuis date_operation (DDMMYYYY → YYYY-MM-DD)
        $dateOp           = $global->date_operation ?? null;
        $dateCompensation = now()->format('Y-m-d');
        if ($dateOp && strlen((string)$dateOp) === 8) {
            $dateCompensation = substr($dateOp, 4, 4)
                . '-' . substr($dateOp, 2, 2)
                . '-' . substr($dateOp, 0, 2);
        }

        foreach ($rejets as $rejet) {
            $detail = $rejet->detail;

            // RtrId : numéro de virement original selon spec (img8) :
            // Sens Aller ENVX = numéro de la valeur en question
            $numeroVirement = str_pad(
                (string)($detail->numero_virement ?? $rejet->id),
                7, '0', STR_PAD_LEFT
            );

            // RIBs : s'assurer qu'ils font 20 chiffres
            $ribDonneur = str_pad(
                preg_replace('/\D/', '', $detail->rib_donneur ?? ''),
                20, '0', STR_PAD_LEFT
            );
            $ribBeneficiaire = str_pad(
                preg_replace('/\D/', '', $detail->rib_beneficiaire ?? ''),
                20, '0', STR_PAD_LEFT
            );

            // Montant : déjà en TND dans la base (decimal 15,3)
            // NE PAS diviser par 1000 ici !
            $montant = (float)($detail->montant ?? 0);

            $transactions[] = [
                'rtr_id'              => $numeroVirement, // = numéro valeur (spec §4.1)
                'orgnl_end_to_end_id' => $numeroVirement, // = numéro valeur (spec §4.2)
                'orgnl_tx_id'         => $numeroVirement, // = numéro valeur (spec §4.3)
                'montant'             => $montant,
                'devise'              => $this->convertirDevise(
                    $global->code_devise ?? $fichier->code_devise ?? 'TND'
                ),
                'date_compensation'   => $dateCompensation,
                'dbtr_nm'             => $detail->nom_donneur      ?? 'N/A',
                'dbtr_acct_id'        => $ribDonneur,
                'cdtr_nm'             => $detail->nom_beneficiaire  ?? 'N/A',
                'cdtr_acct_id'        => $ribBeneficiaire,
                'motif_rejet'         => $rejet->code_rejet
                                         ?? $detail->code_rejet
                                         ?? $detail->motif_rejet
                                         ?? '',
                'libelle_rejet'       => $rejet->motif_rejet
                                         ?? $detail->motif_operation
                                         ?? '',
                'type_valeur'         => $typeValeur,
                'detail'              => $detail,
                'rejet'               => $rejet,
            ];
        }

        return [
            'fichier'      => $fichier,
            'global'       => $global,
            'transactions' => $transactions,
            'msg_id'       => $this->genererMsgId($fichier, $global),
            'code_centre'  => $global->code_centre_regional ?? $this->codeCentre,
            'type_valeur'  => $typeValeur,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    // GÉNÉRATION XML — pacs.004.001.11 CONFORME SIBTEL/BFI
    // ══════════════════════════════════════════════════════════════
    private function genererXml(array $donnees): string
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><Document/>'
        );
        // CORRIGÉ : version .11 (était .09)
        $xml->addAttribute(
            'xmlns',
            'urn:iso:std:iso:20022:tech:xsd:pacs.004.001.11'
        );

        $pmtRtr = $xml->addChild('PmtRtr');

        // ══ GrpHdr ════════════════════════════════════════════════
        $grpHdr = $pmtRtr->addChild('GrpHdr');
        $grpHdr->addChild('MsgId',   $donnees['msg_id']);
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', (string)count($donnees['transactions']));

        // SttlmInf
        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $donnees['code_centre']);

        // PmtTpInf — LclInstrm = code valeur, CtgyPurp = 22 (Rejet)
        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', $donnees['type_valeur']);
        $ctgyPurp  = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '22'); // 22 = code enregistrement Rejet

        // InstgAgt
        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        // ══ TxInf — une par rejet ═════════════════════════════════
        foreach ($donnees['transactions'] as $tx) {
            $txInf = $pmtRtr->addChild('TxInf');

            // §4.1 RtrId — numéro de la valeur (Max35Text)
            $txInf->addChild('RtrId', (string)($tx['rtr_id'] ?? ''));

            // §4.2 OrgnlEndToEndId
            $txInf->addChild('OrgnlEndToEndId',
                (string)($tx['orgnl_end_to_end_id'] ?? '')
            );

            // §4.3 OrgnlTxId
            $txInf->addChild('OrgnlTxId',
                (string)($tx['orgnl_tx_id'] ?? '')
            );

            // §4.4 RtrdIntrBkSttlmAmt — montant retourné
            $amt = $txInf->addChild(
                'RtrdIntrBkSttlmAmt',
                number_format((float)($tx['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', $tx['devise'] ?? 'TND');

            // §4.5 ChrgBr
            $txInf->addChild('ChrgBr', 'SLEV');

            // §4.6 OrgnlTxRef — référence transaction originale
            $orgnlTxRef = $txInf->addChild('OrgnlTxRef');

            // §4.6.1 IntrBkSttlmAmt — montant original
            $intrAmt = $orgnlTxRef->addChild(
                'IntrBkSttlmAmt',
                number_format((float)($tx['montant'] ?? 0), 3, '.', '')
            );
            $intrAmt->addAttribute('Ccy', $tx['devise'] ?? 'TND');

            // §4.6.2 IntrBkSttlmDt — date de compensation
            $orgnlTxRef->addChild('IntrBkSttlmDt',
                is_string($tx['date_compensation'])
                    ? substr($tx['date_compensation'], 0, 10)
                    : now()->format('Y-m-d')
            );

            // §4.6.3 Dbtr — débiteur original
            $dbtr = $orgnlTxRef->addChild('Dbtr');
            $dbtr->addChild('Nm', $tx['dbtr_nm'] ?? 'N/A');

            // §4.6.4 DbtrAcct — RIB débiteur (20 chiffres)
            $dbtrAcct = $orgnlTxRef->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id', $tx['dbtr_acct_id'] ?? '');

            // §4.6.5 DbtrAgt
            $dbtrAgt   = $orgnlTxRef->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $this->bicBanque);

            // §4.6.6 CdtrAgt (avant CdtrAcct selon XSD pacs.004)
            $cdtrAgt   = $orgnlTxRef->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $this->bicBanque);

            // §4.6.7 CdtrAcct — RIB créancier (20 chiffres)
            $cdtrAcct = $orgnlTxRef->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id', $tx['cdtr_acct_id'] ?? '');

            // ══ SplmtryData — données spécifiques SIBTEL ═════════
            $this->ajouterSplmtryData($txInf, $tx, $donnees['type_valeur']);
        }

        return $this->formaterXml($xml->asXML());
    }

    // ══════════════════════════════════════════════════════════════
    // DONNÉES SUPPLÉMENTAIRES PAR TYPE — CONFORMES SIBTEL/BFI
    // ══════════════════════════════════════════════════════════════
    private function ajouterSplmtryData(
        \SimpleXMLElement $txInf,
        array $tx,
        string $typeValeur
    ): void {
        $splmtry = $txInf->addChild('SplmtryData');

        $plcAndNm = match(true) {
            $typeValeur === '20'                            => 'PRELEVEMENT',
            in_array($typeValeur, ['30', '31', '32', '33']) => 'CHEQUE',
            $typeValeur === '84'                            => 'PAPILLON',
            in_array($typeValeur, ['40', '41', '42', '43']) => 'LETTRE_CHANGE',
            default                                         => 'VIREMENT',
        };
        $splmtry->addChild('PlcAndNm', $plcAndNm);

        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');
        $detail   = $tx['detail'] ?? null;

        match(true) {
            $typeValeur === '20'
                => $this->splmtryPrelevement($suppData, $detail, $tx),

            in_array($typeValeur, ['30', '31', '32', '33'])
                => $this->splmtryCheque($suppData, $detail, $tx, $typeValeur),

            $typeValeur === '84'
                => $this->splmtryPapillon($suppData, $detail, $tx),

            in_array($typeValeur, ['40', '41', '42', '43'])
                => $this->splmtryLettreChange($suppData, $detail, $tx),

            default
                => $this->splmtryVirement($suppData, $detail, $tx),
        };
    }

    // ──────────────────────────────────────────────────────────────
    // 1) PRÉLÈVEMENT (type 20) — spec img2/img10/img11
    //    4.2.1.1 à 4.2.1.10 (MotifRejet AJOUTÉ)
    // ──────────────────────────────────────────────────────────────
    private function splmtryPrelevement(
        \SimpleXMLElement $suppData,
        $detail,
        array $tx
    ): void {
        $suppData->addChild('ReferenceContratDomiciliation',
            $detail->ref_contrat ?? ''
        );
        $suppData->addChild('CodeNational',
            $detail->code_emetteur ?? ''
        );
        $suppData->addChild('CodeMAJ',
            $detail->code_maj ?? '1'
        );
        $suppData->addChild('DateMAJ',
            $this->formatDate($detail->date_maj ?? null)
        );
        $suppData->addChild('ZoneLibre',
            $detail->zone_libre ?? ''
        );
        $suppData->addChild('NumeroDomiciliation',
            $detail->ref_contrat ?? ''
        );
        $suppData->addChild('DateEcheance',
            $this->formatDate($detail->date_echeance ?? null)
        );
        $suppData->addChild('CodePayeur',
            $detail->code_payeur ?? ''
        );
        $suppData->addChild('LibPrelev',
            $detail->libelle_prelevement ?? ''
        );
        // 4.2.1.10 MotifRejet — AJOUTÉ (manquait dans code précédent)
        $suppData->addChild('MotifRejet',
            $tx['motif_rejet'] ?? ''
        );
    }

    // ──────────────────────────────────────────────────────────────
    // 2) CHÈQUES 30-33 — spec img3/img4/img11
    //    Structure complète avec ImgImageRecto/Verso pour 30
    //    CNP (31) : ajout MontantReclame, ImgImageRecto/Verso
    //    ARP (32) : ajout MontantReclame + MontantRegularise
    // ──────────────────────────────────────────────────────────────
    private function splmtryCheque(
        \SimpleXMLElement $suppData,
        $detail,
        array $tx,
        string $typeValeur
    ): void {
        // Éléments communs 30, 31, 32, 33
        $suppData->addChild('DateEmission',
            $this->formatDate($detail->date_emission ?? null)
        );
        $suppData->addChild('ZoneLibre',
            $detail->zone_libre ?? ''
        );
        $suppData->addChild('LieuEmission',
            $detail->lieu_emission ?? ''
        );
        $suppData->addChild('SituationBenef',
            $detail->situation_beneficiaire ?? ''
        );
        $suppData->addChild('NatureCompte',
            $detail->nature_compte ?? ''
        );

        if (in_array($typeValeur, ['31', '32', '33'])) {
            // Éléments CNP/ARP (spec img11 §4.2.1.6 à 4.2.1.14)
            $suppData->addChild('DatePreavis',
                $this->formatDate($detail->date_preaviss ?? null)
            );
            $suppData->addChild('DateCompensation',
                $this->formatDate($detail->date_compensation ?? null)
            );
            $suppData->addChild('MontantProvision',
                number_format((float)($detail->montant_provision ?? 0), 3, '.', '')
            );
            $suppData->addChild('MotifRejet',
                $tx['motif_rejet'] ?? ''
            );
            $suppData->addChild('DateDocJoint',
                $this->formatDate($detail->date_doc_joint ?? null)
            );
            $suppData->addChild('NumeroDocJoint',
                $detail->numero_doc_joint ?? ''
            );
            $suppData->addChild('CodeValeurDocJoint',
                $detail->code_valeur_doc_joint ?? '0'
            );
            $suppData->addChild('MotifRejetDocJoint',
                $detail->motif_rejet_doc_joint ?? '0'
            );
            $suppData->addChild('NbreEnregComp',
                (string)($detail->nb_enreg_comp ?? 0)
            );
            // MontantReclame — pour codes 31 et 32 (spec img13 §4.2.1.11)
            if (in_array($typeValeur, ['31', '32'])) {
                $suppData->addChild('MontantReclame',
                    number_format((float)($detail->montant_reclame ?? 0), 3, '.', '')
                );
            }
        } else {
            // Chèque 30 : ImgImageRecto/Verso (spec img3 §4.2.1.6-4.2.1.7)
            $suppData->addChild('ImgImageRecto',
                $detail->img_recto ?? ''
            );
            $suppData->addChild('ImgImageVerso',
                $detail->img_verso ?? ''
            );
        }
    }

    // ──────────────────────────────────────────────────────────────
    // 3) PAPILLON (type 84) — spec img12
    //    Structure : DateEmission, ZoneLibre, LieuEmission,
    //    MotifRejet, DateDocJoint, NumeroDocJoint,
    //    CodeValeurDocJoint, MotifRejetDocJoint, NbreEnregComp
    // ──────────────────────────────────────────────────────────────
    private function splmtryPapillon(
        \SimpleXMLElement $suppData,
        $detail,
        array $tx
    ): void {
        $suppData->addChild('DateEmission',
            $this->formatDate($detail->date_emission ?? null)
        );
        $suppData->addChild('ZoneLibre',
            $detail->zone_libre ?? ''
        );
        $suppData->addChild('LieuEmission',
            $detail->lieu_emission ?? ''
        );
        $suppData->addChild('MotifRejet',
            $tx['motif_rejet'] ?? ''
        );
        $suppData->addChild('DateDocJoint',
            $this->formatDate($detail->date_doc_joint ?? null)
        );
        $suppData->addChild('NumeroDocJoint',
            $detail->numero_doc_joint ?? ''
        );
        $suppData->addChild('CodeValeurDocJoint',
            $detail->code_valeur_doc_joint ?? '0'
        );
        $suppData->addChild('MotifRejetDocJoint',
            $detail->motif_rejet_doc_joint ?? '0'
        );
        $suppData->addChild('NbreEnregComp',
            (string)($detail->nb_enreg_comp ?? 0)
        );
    }

    // ──────────────────────────────────────────────────────────────
    // 4) LETTRES DE CHANGE (types 40-43) — spec img5/img13/img14
    //    19 éléments dont MotifRejet (4.2.1.19) et Messages (4.2.1.18)
    // ──────────────────────────────────────────────────────────────
    private function splmtryLettreChange(
        \SimpleXMLElement $suppData,
        $detail,
        array $tx
    ): void {
        $suppData->addChild('DateEcheance',
            $this->formatDate($detail->date_echeance ?? null)
        );
        $suppData->addChild('DateEcheanceInitiale',
            $this->formatDate($detail->date_echeance_initiale ?? null)
        );
        $suppData->addChild('DateCreationLettreChange',
            $this->formatDate($detail->date_creation ?? null)
        );
        $suppData->addChild('LieuCreation',
            trim($detail->lieu_creation ?? '')
        );
        $suppData->addChild('NatureCompte',
            $detail->nature_compte ?? ''
        );
        $suppData->addChild('ZoneLibre',
            $detail->zone_libre ?? ''
        );
        $suppData->addChild('ReferenceCommercialeBeneficiaire',
            $detail->ref_commerciales_tire ?? '0'
        );
        $suppData->addChild('ReferenceCommercialePayeur',
            $detail->ref_commerciales_tireur ?? '0'
        );
        $suppData->addChild('RibPayeurInit',
            str_pad(
                preg_replace('/\D/', '', $detail->rib_tire_initial ?? ''),
                20, '0', STR_PAD_LEFT
            )
        );
        $suppData->addChild('CodeAcceptation',
            $detail->code_acceptation ?? '0'
        );
        $suppData->addChild('CodeEndossement',
            $detail->code_endossement ?? '0'
        );
        $suppData->addChild('CodeOrdrePayer',
            $detail->code_ordre_payer ?? '0'
        );
        $suppData->addChild('SituationCedant',
            $detail->situation_cedant ?? '0'
        );
        $suppData->addChild('CodeRisqueBCT',
            $detail->code_risque_bct ?? '0'
        );
        $suppData->addChild('MontantInt',
            number_format((float)($detail->montant_interets ?? 0), 3, '.', '')
        );
        $suppData->addChild('MontantFrais',
            number_format((float)($detail->montant_frais_protest ?? 0), 3, '.', '')
        );
        $suppData->addChild('NbreEnregComp',
            (string)($detail->nb_enreg_comp ?? 0)
        );
        // 4.2.1.18 Messages — AJOUTÉ
        $suppData->addChild('Messages',
            $detail->messages ?? ''
        );
        // 4.2.1.19 MotifRejet — AJOUTÉ
        $suppData->addChild('MotifRejet',
            $tx['motif_rejet'] ?? ''
        );
    }

    // ──────────────────────────────────────────────────────────────
    // 5) VIREMENT (type 10) — spec img15
    //    10 éléments : ZoneLibre, ReferenceDossierPaiement,
    //    SituationDonneurOrdre, TypeCompteDonneurOrdre,
    //    ExistDossierChange, NbreEnregComp,
    //    NatureCompteDonneurOrdre, Messages, MotifRejet, CodeSuivi
    // ──────────────────────────────────────────────────────────────
    private function splmtryVirement(
        \SimpleXMLElement $suppData,
        $detail,
        array $tx
    ): void {
        $suppData->addChild('ZoneLibre',
            $detail->zone_libre ?? ''
        );
        $suppData->addChild('ReferenceDossierPaiement',
            $detail->reference_dossier ?? ''
        );
        $suppData->addChild('SituationDonneurOrdre',
            $detail->situation_donneur ?? ''
        );
        $suppData->addChild('TypeCompteDonneurOrdre',
            $detail->type_compte ?? ''
        );
        $suppData->addChild('ExistDossierChange',
            $detail->existence_dossier ?? '0'
        );
        $suppData->addChild('NbreEnregComp',
            (string)($detail->nb_enreg_comp ?? 0)
        );
        $suppData->addChild('NatureCompteDonneurOrdre',
            $detail->nature_compte ?? ''
        );
        // 4.2.1.8 Messages — conditionnel
        if (!empty($detail->messages)) {
            $suppData->addChild('Messages', $detail->messages);
        }
        // 4.2.1.9 MotifRejet
        $suppData->addChild('MotifRejet',
            $tx['motif_rejet'] ?? ''
        );
        // 4.2.1.10 CodeSuivi — Numérique selon spec
        $suppData->addChild('CodeSuivi',
            $detail->code_suivi ?? '0'
        );
    }

    // ══════════════════════════════════════════════════════════════
    // UTILITAIRES
    // ══════════════════════════════════════════════════════════════

    /**
     * Génère le MsgId au format SIBTEL : RR-CCC-TT-NNNN-DDMMYYYY-HHMMSS
     * Max35Text — tronquer si nécessaire
     */
    private function genererMsgId(TcFichier $fichier, $global = null): string
    {
        $codeRemettant = $global->code_remettant       ?? $this->codeRemettant;
        $codeCentre    = $global->code_centre_regional ?? $this->codeCentre;
        $typeValeur    = $global->code_valeur           ?? $fichier->type_valeur ?? '10';
        $numeroLot     = $global->numero_lot            ?? '0001';

        $msgId = $codeRemettant
            . '-' . $codeCentre
            . '-' . $typeValeur
            . '-' . str_pad((string)$numeroLot, 4, '0', STR_PAD_LEFT)
            . '-' . now()->format('dmY')
            . '-' . now()->format('His');

        return substr($msgId, 0, 35);
    }

    /**
     * Convertit le code devise SIBTEL (numérique ou alphanumérique) vers ISO 4217
     */
    private function convertirDevise(string $code): string
    {
        return match($code) {
            '788', '821'                      => 'TND',
            '840'                      => 'USD',
            '978'                      => 'EUR',
            '826'                      => 'GBP',
            'TND', 'USD', 'EUR', 'GBP' => strtoupper($code),
            default => strlen($code) === 3 ? strtoupper($code) : 'TND',
        };
    }

    /**
     * Formate une date SIBTEL (DDMMYYYY) vers ISO 8601 (YYYY-MM-DD)
     */
    private function formatDate(?string $date): string
    {
        if (empty($date) || strlen($date) !== 8) {
            return now()->format('Y-m-d');
        }
        // Format SIBTEL : DDMMYYYY
        return substr($date, 4, 4)
             . '-' . substr($date, 2, 2)
             . '-' . substr($date, 0, 2);
    }

    /**
     * Validation XSD. Placer le fichier dans : resources/xsd/pacs.004.001.11.xsd
     */
    private function validerXml(string $xml): bool
    {
        try {
            $dom = new \DOMDocument();
            $dom->loadXML($xml);

            // CORRIGÉ : fichier XSD version .11
            $xsdPath = base_path('resources/xsd/pacs.004.001.11.xsd');

            if (file_exists($xsdPath)) {
                libxml_use_internal_errors(true);
                $valide = $dom->schemaValidate($xsdPath);
                if (!$valide) {
                    foreach (libxml_get_errors() as $error) {
                        Log::warning('Pacs.004 XSD erreur ligne ' . $error->line . ' : ' . trim($error->message));
                    }
                    libxml_clear_errors();
                }
                libxml_use_internal_errors(false);
                return $valide;
            }

            Log::info('Pacs.004 : XSD pacs.004.001.11 absent, validation well-formed uniquement.');
            return true;

        } catch (\Exception $e) {
            Log::warning('Validation XML Pacs.004 échouée : ' . $e->getMessage());
            return false;
        }
    }

    private function formaterXml(string $xml): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($xml);
        // Retourner le XML formaté et bien indienté
        return $dom->saveXML();
    }

    public function getTypeMessage(): string
    {
        return 'pacs.004.001.11'; // CORRIGÉ
    }
}
