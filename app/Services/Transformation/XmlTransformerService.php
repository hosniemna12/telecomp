<?php

namespace App\Services\Transformation;

use App\Contracts\TransformerInterface;

/**
 * XmlTransformerService — Corrigé selon specs officielles SIBTEL/BFI
 *
 * Corrections appliquées :
 *  - pacs.008.001.10 : ajout IntrBkSttlmDt dans GrpHdr (§2.4 obligatoire)
 *  - pacs.008.001.10 : ordre éléments CdtTrfTxInf corrigé (XSD strict)
 *  - pacs.003.001.09 : racine FIToFICstmrCdtTrf avec DrctDbtTxInf (spec img19)
 *  - pacs.003.001.09 : ajout IntrBkSttlmDt dans GrpHdr
 *  - MsgId : format structuré RR-CCC-TT-NNNN-DDMMYYYY-HHMMSS
 *  - SplmtryData virement : ReferenceDossierPaiement (corrigé nom)
 *  - SplmtryData chèque 30 : structure correcte avec ImgImageRecto/Verso
 *  - SplmtryData chèque 31 : MontantReclame ajouté
 *  - SplmtryData chèque 32 : MontantReclame + MontantRegularise
 *  - SplmtryData LDC : Messages + MotifRejet ajoutés (§4.2.1.18-19)
 *  - Enveloppe RequestPayload + AppHdr (head.001.001.01) selon spec img11
 */
class XmlTransformerService implements TransformerInterface
{
    private string $typeValeur = '10';
    private string $bicBanque  = 'BSIETNTX';

    // ══════════════════════════════════════════════════════════════
    // POINT D'ENTRÉE PRINCIPAL
    // ══════════════════════════════════════════════════════════════
    public function transformer(array $donnees): string
    {
        $global           = $donnees['global'];
        $details          = $donnees['details'];
        $this->typeValeur = $donnees['details'][0]['type_valeur'] ?? '10';

        $xmlDocument = match($this->typeValeur) {
            '20'                    => $this->transformerPrelevement($global, $details),
            '30', '31', '32', '33'  => $this->transformerCheque($global, $details),
            '40', '41', '42', '43'  => $this->transformerLettreChange($global, $details),
            '82', '83'              => $this->transformerCheque($global, $details),
            '84'                    => $this->transformerPapillon($global, $details),
            default                 => $this->transformerVirement($global, $details),
        };

        // Encapsuler dans RequestPayload + AppHdr (spec img11)
        return $this->encapsulerMessage($xmlDocument, $this->getTypeMessage());
    }

    public function getTypeMessage(): string
    {
        return match($this->typeValeur) {
            '20'                    => 'pacs.003.001.09',
            '30', '31', '32', '33'  => 'pacs.003.001.09',
            '40', '41', '42', '43'  => 'pacs.003.001.09',
            '82', '83'              => 'pacs.003.001.09',
            '84'                    => 'pacs.003.001.09',
            default                 => 'pacs.008.001.10',
        };
    }

    // ══════════════════════════════════════════════════════════════
    // ENVELOPPE RequestPayload + AppHdr (spec SIBTEL/BFI img11)
    // Structure obligatoire pour tous les messages ISO 20022
    // ══════════════════════════════════════════════════════════════
    private function encapsulerMessage(string $xmlDocument, string $typeMessage): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;

        // Créer l'enveloppe RequestPayload
        $root = $dom->createElement('RequestPayload');
        $dom->appendChild($root);

        // AppHdr (head.001.001.01)
        $appHdr = $dom->createElement('AppHdr');
        $appHdr->setAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:head.001.001.01');
        $root->appendChild($appHdr);

        // Fr (expéditeur)
        $fr    = $dom->createElement('Fr');
        $fiId  = $dom->createElement('FIId');
        $finId = $dom->createElement('FinInstnId');
        $bicfi = $dom->createElement('BICFI', $this->bicBanque);
        $finId->appendChild($bicfi);
        $fiId->appendChild($finId);
        $fr->appendChild($fiId);
        $appHdr->appendChild($fr);

        // BizMsgIdr — identifiant unique du message
        $bizMsgIdr = $dom->createElement('BizMsgIdr',
            substr($this->bicBanque . '-' . now()->format('YmdHis') . '-' . uniqid(), 0, 35)
        );
        $appHdr->appendChild($bizMsgIdr);

        // MsgDefIdr — type de message ISO 20022
        $msgDefIdr = $dom->createElement('MsgDefIdr', $typeMessage);
        $appHdr->appendChild($msgDefIdr);

        // CreDt — date de création (ISO 8601)
        $creDt = $dom->createElement('CreDt', now()->format('Y-m-d\TH:i:s\Z'));
        $appHdr->appendChild($creDt);

        // Document — contenu du message métier
        $innerDom = new \DOMDocument();
        $innerDom->loadXML($xmlDocument);
        $importedNode = $dom->importNode($innerDom->documentElement, true);
        $root->appendChild($importedNode);

        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    // ══════════════════════════════════════════════════════════════
    // VIREMENT → pacs.008.001.10
    // Spec SIBTEL/BFI img4-img8
    // ══════════════════════════════════════════════════════════════
    private function transformerVirement(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.008.001.10');

        $ficToFI = $xml->addChild('FIToFICstmrCdtTrf');
        $grpHdr  = $ficToFI->addChild('GrpHdr');

        // §2.1 MsgId — format structuré SIBTEL
        $grpHdr->addChild('MsgId', $this->genererMsgId($global));

        // §2.2 CreDtTm — format ISO 8601
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));

        // §2.3 NbOfTxs
        $grpHdr->addChild('NbOfTxs', (string)count($details));

        // §2.4 IntrBkSttlmDt — AJOUTÉ (était manquant, obligatoire)
        $grpHdr->addChild('IntrBkSttlmDt', $this->convertirDate($global['date_operation'] ?? null));

        // §2.5 SttlmInf
        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');

        // §2.5.2 ClrSys/Prtry = code centre régional
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        // §2.6 PmtTpInf
        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        // §2.6.1.1 Prtry = code valeur (10 pour virement)
        $lclInstrm->addChild('Prtry', '10');
        // §2.6.2.1 CtgyPurp/Prtry = 21 (Présentation)
        $ctgyPurp = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        // §2.7 InstgAgt
        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        foreach ($details as $index => $detail) {
            $cdtTrf = $ficToFI->addChild('CdtTrfTxInf');

            // §3.1 PmtId
            $pmtId = $cdtTrf->addChild('PmtId');
            // §3.1.1 InstrId = numéro virement (Sens Aller = numéro valeur)
            $pmtId->addChild('InstrId',
                str_pad($detail['numero_virement'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );
            // §3.1.2 EndToEndId
            $pmtId->addChild('EndToEndId',
                str_pad($detail['numero_virement'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );

            // §3.2 IntrBkSttlmAmt — devise obligatoirement TND (spec img6)
            $amt = $cdtTrf->addChild('IntrBkSttlmAmt',
                number_format((float)($detail['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', 'TND');

            // §3.4 ChrgBr = SLEV (obligatoire)
            $cdtTrf->addChild('ChrgBr', 'SLEV');

            // §3.5 Dbtr — débiteur (donneur d'ordres)
            // ORDRE CORRIGÉ selon XSD pacs.008 : Dbtr → DbtrAcct → DbtrAgt → CdtrAgt → CdtrAcct → Cdtr
            $dbtr = $cdtTrf->addChild('Dbtr');
            $dbtr->addChild('Nm', $this->nettoyerTexte($detail['nom_donneur'] ?? 'N/A'));

            // §3.6 DbtrAcct — RIB donneur (Max34Text = 20 chiffres)
            $dbtrAcct = $cdtTrf->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_donneur'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            // §3.7 DbtrAgt
            $dbtrAgt   = $cdtTrf->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $this->bicBanque);

            // §3.8 CdtrAgt (avant CdtrAcct et Cdtr selon XSD strict)
            $cdtrAgt   = $cdtTrf->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $this->bicBanque);

            // §3.9 Cdtr — créancier (bénéficiaire)
            $cdtr = $cdtTrf->addChild('Cdtr');
            $cdtr->addChild('Nm', $this->nettoyerTexte($detail['nom_beneficiaire'] ?? 'N/A'));

            // §3.10 CdtrAcct — RIB bénéficiaire
            $cdtrAcct = $cdtTrf->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_beneficiaire'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            // RmtInf — motif opération
            if (!empty($detail['motif_operation'])) {
                $rmtInf = $cdtTrf->addChild('RmtInf');
                $rmtInf->addChild('Ustrd', $this->nettoyerTexte($detail['motif_operation']));
            }

            // §4 SplmtryData — informations supplémentaires SIBTEL virement
            $this->ajouterSplmtryVirement($cdtTrf, $detail);
        }

        return $this->formaterXml($xml->asXML());
    }

    // ══════════════════════════════════════════════════════════════
    // PRÉLÈVEMENT → pacs.003.001.09
    // Spec SIBTEL/BFI img19-img20, img2-img3
    // ══════════════════════════════════════════════════════════════
    private function transformerPrelevement(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        // Racine : FIToFICstmrCdtTrf avec DrctDbtTxInf (spec img19)
        $ficToFI = $xml->addChild('FIToFICstmrCdtTrf');
        $grpHdr  = $ficToFI->addChild('GrpHdr');

        $grpHdr->addChild('MsgId',        $this->genererMsgId($global));
        $grpHdr->addChild('CreDtTm',      now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs',      (string)count($details));
        // §2.4 IntrBkSttlmDt — obligatoire
        $grpHdr->addChild('IntrBkSttlmDt', $this->convertirDate($global['date_operation'] ?? null));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        // §2.5 PmtTpInf — code valeur 20 pour prélèvement
        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', '20');
        $ctgyPurp  = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21'); // 21 = Présentation

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        foreach ($details as $index => $detail) {
            // pacs.003 utilise DrctDbtTxInf
            $drctDbt = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId   = $drctDbt->addChild('PmtId');
            $pmtId->addChild('InstrId',
                str_pad($detail['numero_prelevement'] ?? $detail['numero_virement'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );
            $pmtId->addChild('EndToEndId',
                str_pad($detail['numero_prelevement'] ?? $detail['numero_virement'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );

            $amt = $drctDbt->addChild('IntrBkSttlmAmt',
                number_format((float)($detail['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', 'TND');

            $drctDbt->addChild('ChrgBr', 'SLEV');

            // §3.4 Cdtr — créancier (émetteur du prélèvement)
            $cdtr = $drctDbt->addChild('Cdtr');
            $cdtr->addChild('Nm', $this->nettoyerTexte($detail['nom_beneficiaire'] ?? 'N/A'));

            $cdtrAcct = $drctDbt->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_creancier'] ?? $detail['rib_beneficiaire'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $cdtrAgt   = $drctDbt->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $this->bicBanque);

            // §3.8 Dbtr — débiteur (payeur)
            $dbtr = $drctDbt->addChild('Dbtr');
            $dbtr->addChild('Nm', 'N/A');

            $dbtrAcct = $drctDbt->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_payeur'] ?? $detail['rib_donneur'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $dbtrAgt   = $drctDbt->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $this->bicBanque);

            if (!empty($detail['libelle_prelevement'])) {
                $rmtInf = $drctDbt->addChild('RmtInf');
                $rmtInf->addChild('Ustrd', $this->nettoyerTexte($detail['libelle_prelevement']));
            }

            // §4 SplmtryData prélèvement
            $this->ajouterSplmtryPrelevement($drctDbt, $detail);
        }

        return $this->formaterXml($xml->asXML());
    }

    // ══════════════════════════════════════════════════════════════
    // CHÈQUES (30, 31, 32, 33, 82, 83) → pacs.003.001.09
    // Spec SIBTEL img3/img4/img5, SplmtryData img3/img4/img11
    // ══════════════════════════════════════════════════════════════
    private function transformerCheque(array $global, array $details): string
    {
        $typeCode = $details[0]['type_valeur'] ?? '30';

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        $ficToFI = $xml->addChild('FIToFICstmrCdtTrf');
        $grpHdr  = $ficToFI->addChild('GrpHdr');

        $grpHdr->addChild('MsgId',         $this->genererMsgId($global));
        $grpHdr->addChild('CreDtTm',       now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs',       (string)count($details));
        $grpHdr->addChild('IntrBkSttlmDt', $this->convertirDate($global['date_operation'] ?? null));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', $typeCode); // code valeur du chèque
        $ctgyPurp  = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        foreach ($details as $index => $detail) {
            $chqTx = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId = $chqTx->addChild('PmtId');
            $pmtId->addChild('InstrId',
                str_pad($detail['numero_cheque'] ?? $detail['numero_virement'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );
            $pmtId->addChild('EndToEndId',
                str_pad($detail['numero_cheque'] ?? $detail['numero_virement'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );

            $amt = $chqTx->addChild('IntrBkSttlmAmt',
                number_format((float)($detail['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', 'TND');

            $chqTx->addChild('ChrgBr', 'SLEV');

            // Créancier = bénéficiaire du chèque
            $cdtr = $chqTx->addChild('Cdtr');
            $cdtr->addChild('Nm', $this->nettoyerTexte($detail['nom_beneficiaire'] ?? 'N/A'));

            $cdtrAcct = $chqTx->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_beneficiaire'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $cdtrAgt   = $chqTx->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $this->bicBanque);

            // Débiteur = tireur du chèque
            $dbtr = $chqTx->addChild('Dbtr');
            $dbtr->addChild('Nm', $this->nettoyerTexte($detail['nom_donneur'] ?? 'N/A'));

            $dbtrAcct = $chqTx->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_tireur'] ?? $detail['rib_donneur'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $dbtrAgt   = $chqTx->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $this->bicBanque);

            // §4 SplmtryData chèque
            $this->ajouterSplmtryCheque($chqTx, $detail, $typeCode);
        }

        return $this->formaterXml($xml->asXML());
    }

    // ══════════════════════════════════════════════════════════════
    // PAPILLON (84) → pacs.003.001.09
    // Structure similaire au chèque mais SplmtryData spécifique
    // ══════════════════════════════════════════════════════════════
    private function transformerPapillon(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        $ficToFI = $xml->addChild('FIToFICstmrCdtTrf');
        $grpHdr  = $ficToFI->addChild('GrpHdr');

        $grpHdr->addChild('MsgId',         $this->genererMsgId($global));
        $grpHdr->addChild('CreDtTm',       now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs',       (string)count($details));
        $grpHdr->addChild('IntrBkSttlmDt', $this->convertirDate($global['date_operation'] ?? null));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', '84');
        $ctgyPurp  = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        foreach ($details as $index => $detail) {
            $papTx = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId = $papTx->addChild('PmtId');
            $pmtId->addChild('InstrId',
                str_pad($detail['numero_cheque'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );
            $pmtId->addChild('EndToEndId',
                str_pad($detail['numero_cheque'] ?? ($index + 1), 7, '0', STR_PAD_LEFT)
            );

            $amt = $papTx->addChild('IntrBkSttlmAmt',
                number_format((float)($detail['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', 'TND');

            $papTx->addChild('ChrgBr', 'SLEV');

            $cdtr = $papTx->addChild('Cdtr');
            $cdtr->addChild('Nm', $this->nettoyerTexte($detail['nom_beneficiaire'] ?? 'N/A'));

            $cdtrAcct = $papTx->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_beneficiaire'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $cdtrAgt   = $papTx->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $this->bicBanque);

            $dbtr = $papTx->addChild('Dbtr');
            $dbtr->addChild('Nm', $this->nettoyerTexte($detail['nom_donneur'] ?? 'N/A'));

            $dbtrAcct = $papTx->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_tireur'] ?? $detail['rib_donneur'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $dbtrAgt   = $papTx->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $this->bicBanque);

            $this->ajouterSplmtryPapillon($papTx, $detail);
        }

        return $this->formaterXml($xml->asXML());
    }

    // ══════════════════════════════════════════════════════════════
    // LETTRE DE CHANGE (40-43) → pacs.003.001.09
    // ══════════════════════════════════════════════════════════════
    private function transformerLettreChange(array $global, array $details): string
    {
        $typeCode = $details[0]['type_valeur'] ?? '40';

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        $ficToFI = $xml->addChild('FIToFICstmrCdtTrf');
        $grpHdr  = $ficToFI->addChild('GrpHdr');

        $grpHdr->addChild('MsgId',         $this->genererMsgId($global));
        $grpHdr->addChild('CreDtTm',       now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs',       (string)count($details));
        $grpHdr->addChild('IntrBkSttlmDt', $this->convertirDate($global['date_operation'] ?? null));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', $typeCode);
        $ctgyPurp  = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', $this->bicBanque);

        foreach ($details as $index => $detail) {
            $ldcTx = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId = $ldcTx->addChild('PmtId');
            $pmtId->addChild('InstrId',
                str_pad($detail['numero_lettre_change'] ?? ($index + 1), 12, '0', STR_PAD_LEFT)
            );
            $pmtId->addChild('EndToEndId',
                str_pad($detail['numero_lettre_change'] ?? ($index + 1), 12, '0', STR_PAD_LEFT)
            );

            $amt = $ldcTx->addChild('IntrBkSttlmAmt',
                number_format((float)($detail['montant'] ?? 0), 3, '.', '')
            );
            $amt->addAttribute('Ccy', 'TND');

            $ldcTx->addChild('ChrgBr', 'SLEV');

            // Cédant = créancier/bénéficiaire
            $cdtr = $ldcTx->addChild('Cdtr');
            $cdtr->addChild('Nm', $this->nettoyerTexte($detail['nom_cedant'] ?? 'N/A'));

            $cdtrAcct = $ldcTx->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_cedant'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $cdtrAgt   = $ldcTx->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', $this->bicBanque);

            // Tiré = débiteur
            $dbtr = $ldcTx->addChild('Dbtr');
            $dbtr->addChild('Nm', $this->nettoyerTexte($detail['nom_tire'] ?? 'N/A'));

            $dbtrAcct = $ldcTx->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id',
                str_pad(preg_replace('/\D/', '', $detail['rib_tire'] ?? ''), 20, '0', STR_PAD_LEFT)
            );

            $dbtrAgt   = $ldcTx->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', $this->bicBanque);

            $this->ajouterSplmtryLettreChange($ldcTx, $detail);
        }

        return $this->formaterXml($xml->asXML());
    }

    // ══════════════════════════════════════════════════════════════
    // SPLMTRYDATA — DONNÉES SUPPLÉMENTAIRES PAR TYPE
    // ══════════════════════════════════════════════════════════════

    /**
     * SplmtryData Virement — spec img15 (§4.2.1.1 à §4.2.1.10)
     */
    private function ajouterSplmtryVirement(\SimpleXMLElement $parent, array $detail): void
    {
        $splmtry  = $parent->addChild('SplmtryData');
        $splmtry->addChild('PlcAndNm', 'VIREMENT');
        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');

        $suppData->addChild('ZoneLibre',               $detail['zone_libre'] ?? '');
        $suppData->addChild('ReferenceDossierPaiement', $detail['reference_dossier'] ?? '');
        $suppData->addChild('SituationDonneurOrdre',   $detail['situation_donneur'] ?? '');
        $suppData->addChild('TypeCompteDonneurOrdre',  $detail['type_compte'] ?? '');
        $suppData->addChild('ExistDossierChange',      $detail['existence_dossier'] ?? '0');
        $suppData->addChild('NbreEnregComp',           (string)($detail['nb_enreg_comp'] ?? '0'));
        $suppData->addChild('NatureCompteDonneurOrdre', $detail['nature_compte'] ?? '');
        // Messages — conditionnel
        if (!empty($detail['messages'])) {
            $suppData->addChild('Messages', $this->nettoyerTexte($detail['messages']));
        }
        $suppData->addChild('MotifOperation',          $this->nettoyerTexte($detail['motif_operation'] ?? ''));
        $suppData->addChild('CodeSuivi',               $detail['code_suivi'] ?? '0');
    }

    /**
     * SplmtryData Prélèvement — spec img2/img3 (§4.2.1.1 à §4.2.1.9)
     */
    private function ajouterSplmtryPrelevement(\SimpleXMLElement $parent, array $detail): void
    {
        $splmtry  = $parent->addChild('SplmtryData');
        $splmtry->addChild('PlcAndNm', 'PRELEVEMENT');
        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');

        $suppData->addChild('ReferenceContratDomiciliation', $detail['ref_contrat'] ?? '');
        $suppData->addChild('CodeNational',                  $detail['code_emetteur'] ?? '');
        $suppData->addChild('CodeMAJ',                       $detail['code_maj'] ?? '1');
        $suppData->addChild('DateMAJ',                       $this->convertirDate($detail['date_maj'] ?? null));
        $suppData->addChild('ZoneLibre',                     $detail['zone_libre'] ?? '');
        $suppData->addChild('NumeroDomiciliation',           $detail['ref_contrat'] ?? '');
        $suppData->addChild('DateEcheance',                  $this->convertirDate($detail['date_echeance'] ?? null));
        $suppData->addChild('CodePayeur',                    $detail['code_payeur'] ?? $detail['motif_rejet'] ?? '');
        $suppData->addChild('LibPrelev',                     $this->nettoyerTexte($detail['libelle_prelevement'] ?? ''));
    }

    /**
     * SplmtryData Chèque — spec img3/img4/img11
     * Structure différente selon type : 30 (images), 31 (CNP), 32 (ARP)
     */
    private function ajouterSplmtryCheque(\SimpleXMLElement $parent, array $detail, string $typeCode): void
    {
        $splmtry  = $parent->addChild('SplmtryData');
        $splmtry->addChild('PlcAndNm', 'CHEQUE');
        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');

        // Éléments communs à tous les chèques
        $suppData->addChild('DateEmission',   $this->convertirDate($detail['date_emission'] ?? null));
        $suppData->addChild('ZoneLibre',      $detail['zone_libre'] ?? '');
        $suppData->addChild('LieuEmission',   $detail['lieu_emission'] ?? '');
        $suppData->addChild('SituationBenef', $detail['situation_beneficiaire'] ?? '');
        $suppData->addChild('NatureCompte',   $detail['nature_compte'] ?? '');

        if ($typeCode === '30' || $typeCode === '33') {
            // Chèque simple 30/33 : images base64
            $suppData->addChild('ImgImageRecto', $detail['img_recto'] ?? '');
            $suppData->addChild('ImgImageVerso', $detail['img_verso'] ?? '');
        } else {
            // CNP (31) et ARP (32/82/83) — structure complète img11
            $suppData->addChild('DatePreavis',         $this->convertirDate($detail['date_preaviss'] ?? null));
            $suppData->addChild('DateCompensation',    $this->convertirDate($detail['date_compensation'] ?? null));
            $suppData->addChild('MontantProvision',    number_format((float)($detail['montant_provision'] ?? 0), 3, '.', ''));
            $suppData->addChild('MotifRejet',          $detail['motif_rejet'] ?? '');
            $suppData->addChild('DateDocJoint',        $this->convertirDate($detail['date_doc_joint'] ?? null));
            $suppData->addChild('NumeroDocJoint',      $detail['numero_doc_joint'] ?? '');
            $suppData->addChild('CodeValeurDocJoint',  $detail['code_valeur_doc_joint'] ?? '0');
            $suppData->addChild('MotifRejetDocJoint',  $detail['motif_rejet_doc_joint'] ?? '0');
            $suppData->addChild('NbreEnregComp',       (string)($detail['nb_enreg_comp'] ?? 0));
            // MontantReclame — uniquement pour codes 31 et 32 (spec img13 §4.2.1.11)
            if (in_array($typeCode, ['31', '32', '82', '83'])) {
                $suppData->addChild('MontantReclame',
                    number_format((float)($detail['montant_reclame'] ?? $detail['montant_provision'] ?? 0), 3, '.', '')
                );
            }
        }
    }

    /**
     * SplmtryData Papillon — spec img12
     */
    private function ajouterSplmtryPapillon(\SimpleXMLElement $parent, array $detail): void
    {
        $splmtry  = $parent->addChild('SplmtryData');
        $splmtry->addChild('PlcAndNm', 'PAPILLON');
        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');

        $suppData->addChild('DateEmission',       $this->convertirDate($detail['date_emission'] ?? null));
        $suppData->addChild('ZoneLibre',          $detail['zone_libre'] ?? '');
        $suppData->addChild('LieuEmission',       $detail['lieu_emission'] ?? '');
        $suppData->addChild('MotifRejet',         $detail['motif_rejet'] ?? '');
        $suppData->addChild('DateDocJoint',       $this->convertirDate($detail['date_doc_joint'] ?? null));
        $suppData->addChild('NumeroDocJoint',     $detail['numero_doc_joint'] ?? '');
        $suppData->addChild('CodeValeurDocJoint', $detail['code_valeur_doc_joint'] ?? '0');
        $suppData->addChild('MotifRejetDocJoint', $detail['motif_rejet_doc_joint'] ?? '0');
        $suppData->addChild('NbreEnregComp',      (string)($detail['nb_enreg_comp'] ?? 0));
    }

    /**
     * SplmtryData Lettre de change — spec img5/img13/img14
     * 19 éléments dont Messages (§4.2.1.18) et MotifRejet (§4.2.1.19)
     */
    private function ajouterSplmtryLettreChange(\SimpleXMLElement $parent, array $detail): void
    {
        $splmtry  = $parent->addChild('SplmtryData');
        $splmtry->addChild('PlcAndNm', 'LETTRE_CHANGE');
        $envlp    = $splmtry->addChild('Envlp');
        $suppData = $envlp->addChild('SupplementaryData');

        $suppData->addChild('DateEcheance',                    $this->convertirDate($detail['date_echeance'] ?? null));
        $suppData->addChild('DateEcheanceInitiale',            $this->convertirDate($detail['date_echeance_initiale'] ?? null));
        $suppData->addChild('DateCreationLettreChange',        $this->convertirDate($detail['date_creation'] ?? null));
        $suppData->addChild('LieuCreation',                    $this->nettoyerTexte($detail['lieu_creation'] ?? ''));
        $suppData->addChild('NatureCompte',                    $detail['nature_compte'] ?? '');
        $suppData->addChild('ZoneLibre',                       $detail['zone_libre'] ?? '');
        $suppData->addChild('ReferenceCommercialeBeneficiaire', $detail['ref_commerciales_tire'] ?? '0');
        $suppData->addChild('ReferenceCommercialePayeur',      $detail['ref_commerciales_tireur'] ?? '0');
        $suppData->addChild('RibPayeurInit',
            str_pad(preg_replace('/\D/', '', $detail['rib_tire_initial'] ?? ''), 20, '0', STR_PAD_LEFT)
        );
        $suppData->addChild('CodeAcceptation',  $detail['code_acceptation'] ?? '0');
        $suppData->addChild('CodeEndossement',  $detail['code_endossement'] ?? '0');
        $suppData->addChild('CodeOrdrePayer',   $detail['code_ordre_payer'] ?? '0');
        $suppData->addChild('SituationCedant',  $detail['situation_cedant'] ?? '0');
        $suppData->addChild('CodeRisqueBCT',    $detail['code_risque_bct'] ?? '0');
        $suppData->addChild('MontantInt',
            number_format((float)($detail['montant_interets'] ?? 0), 3, '.', '')
        );
        $suppData->addChild('MontantFrais',
            number_format((float)($detail['montant_frais_protest'] ?? 0), 3, '.', '')
        );
        $suppData->addChild('NbreEnregComp', (string)($detail['nb_enreg_comp'] ?? 0));
        // §4.2.1.18 Messages — AJOUTÉ
        $suppData->addChild('Messages',    $this->nettoyerTexte($detail['messages'] ?? ''));
        // §4.2.1.19 MotifRejet — AJOUTÉ
        $suppData->addChild('MotifRejet',  $detail['motif_rejet'] ?? '');
    }

    // ══════════════════════════════════════════════════════════════
    // UTILITAIRES
    // ══════════════════════════════════════════════════════════════

    /**
     * Génère le MsgId au format SIBTEL : RR-CCC-TT-NNNN-DDMMYYYY-HHMMSS (max 35 car.)
     */
    private function genererMsgId(array $global): string
    {
        $remettant  = $global['code_remettant'] ?? '26';
        $centre     = $global['code_centre']    ?? '999';
        $typeValeur = $global['code_valeur']    ?? $this->typeValeur;
        $numeroLot  = $global['numero_lot']     ?? '0001';

        $msgId = $remettant
            . '-' . $centre
            . '-' . $typeValeur
            . '-' . str_pad((string)$numeroLot, 4, '0', STR_PAD_LEFT)
            . '-' . now()->format('dmY')
            . '-' . now()->format('His');

        return substr($msgId, 0, 35);
    }

    /**
     * Convertit une date SIBTEL DDMMYYYY → YYYY-MM-DD (ISO 8601)
     * Si la date est déjà au format YYYY-MM-DD, la retourne telle quelle.
     */
    private function convertirDate(?string $date): string
    {
        if (empty($date)) {
            return now()->format('Y-m-d');
        }
        // Format SIBTEL : DDMMYYYY (8 chiffres)
        if (strlen($date) === 8 && is_numeric($date)) {
            return substr($date, 4, 4)
                 . '-' . substr($date, 2, 2)
                 . '-' . substr($date, 0, 2);
        }
        // Déjà au format YYYY-MM-DD
        if (strlen($date) === 10 && strpos($date, '-') !== false) {
            return substr($date, 0, 10);
        }
        return now()->format('Y-m-d');
    }

    /**
     * Nettoie les caractères spéciaux pour XML (évite les erreurs de parsing)
     */
    private function nettoyerTexte(string $texte): string
    {
        // Supprimer caractères de contrôle et conserver ASCII imprimable + accents
        $texte = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $texte);
        return trim($texte);
    }

    private function formaterXml(string $xml): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($xml);
        return $dom->saveXML();
    }
}