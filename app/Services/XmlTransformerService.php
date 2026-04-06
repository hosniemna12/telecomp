<?php

namespace App\Services;

use App\Contracts\TransformerInterface;

class XmlTransformerService implements TransformerInterface
{
    private string $typeValeur = '10';

    public function transformer(array $donnees): string
    {
        $global           = $donnees['global'];
        $details          = $donnees['details'];
        $this->typeValeur = $donnees['details'][0]['type_valeur'] ?? '10';

        return match($this->typeValeur) {
            '20'                    => $this->transformerPrelevement($global, $details),
            '30', '31', '32', '33'  => $this->transformerCheque($global, $details),
            '40', '41', '42', '43'  => $this->transformerLettreChange($global, $details),
            default                 => $this->transformerVirement($global, $details),
        };
    }

    public function getTypeMessage(): string
    {
        return match($this->typeValeur) {
            '20'                    => 'pacs.003.001.09',
            '30', '31', '32', '33', '84'  => 'pacs.003.001.09',
            '40', '41', '42', '43'  => 'pacs.003.001.09',
            default                 => 'pacs.008.001.10',
        };
    }

    private function convertirDevise(string $code): string
    {
        return match($code) {
            '788' => 'TND',
            '840' => 'USD',
            '978' => 'EUR',
            '826' => 'GBP',
            default => $code,
        };
    }

    private function transformerVirement(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.008.001.10');

        $ficToFI = $xml->addChild('FIToFICstmrCdtTrf');
        $grpHdr  = $ficToFI->addChild('GrpHdr');
        $grpHdr->addChild('MsgId',    $global['numero_lot'] ?? 'LOT001');
        $grpHdr->addChild('CreDtTm',  now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs',  count($details));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', 'BSIETNTX');

        $devise = $this->convertirDevise($global['code_devise'] ?? 'TND');

        foreach ($details as $index => $detail) {
            $cdtTrf = $ficToFI->addChild('CdtTrfTxInf');
            $pmtId  = $cdtTrf->addChild('PmtId');
            $pmtId->addChild('InstrId',    str_pad($detail['numero_virement'] ?? ($index+1), 7, '0', STR_PAD_LEFT));
            $pmtId->addChild('EndToEndId', str_pad($detail['numero_virement'] ?? ($index+1), 7, '0', STR_PAD_LEFT));

            $amt = $cdtTrf->addChild('IntrBkSttlmAmt', number_format((float)($detail['montant'] ?? 0), 3, '.', ''));
            $amt->addAttribute('Ccy', $devise);

            $cdtTrf->addChild('ChrgBr', 'SLEV');

            $dbtr = $cdtTrf->addChild('Dbtr');
            $dbtr->addChild('Nm', $detail['nom_donneur'] ?? 'N/A');

            $dbtrAcct = $cdtTrf->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id', $detail['rib_donneur'] ?? '');

            $dbtrAgt   = $cdtTrf->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', 'BSIETNTX');

            $cdtr = $cdtTrf->addChild('Cdtr');
            $cdtr->addChild('Nm', $detail['nom_beneficiaire'] ?? 'N/A');

            $cdtrAcct = $cdtTrf->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id', $detail['rib_beneficiaire'] ?? '');

            $cdtrAgt   = $cdtTrf->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', 'BSIETNTX');

            if (!empty($detail['motif_operation'])) {
                $rmtInf = $cdtTrf->addChild('RmtInf');
                $rmtInf->addChild('Ustrd', trim($detail['motif_operation']));
            }

            $splmtry  = $cdtTrf->addChild('SplmtryData');
            $splmtry->addChild('PlcAndNm', 'VIREMENT');
            $envlp    = $splmtry->addChild('Envlp');
            $suppData = $envlp->addChild('SupplementaryData');
            $suppData->addChild('ReferenceContratDomiciliation', $detail['reference_dossier'] ?? '');
            $suppData->addChild('SituationDonneur', $detail['situation_donneur'] ?? '');
            $suppData->addChild('TypeCompte', $detail['type_compte'] ?? '');
        }

        return $this->formaterXml($xml->asXML());
    }

    private function transformerPrelevement(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        $ficToFI = $xml->addChild('FIToFICstmrDrctDbt');
        $grpHdr  = $ficToFI->addChild('GrpHdr');
        $grpHdr->addChild('MsgId',   $global['numero_lot'] ?? 'LOT001');
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', count($details));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', '20');
        $ctgyPurp = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', 'BSIETNTX');

        $devise = $this->convertirDevise($global['code_devise'] ?? 'TND');

        foreach ($details as $index => $detail) {
            $drctDbt = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId   = $drctDbt->addChild('PmtId');
            $pmtId->addChild('InstrId',    str_pad($detail['numero_prelevement'] ?? $detail['numero_virement'] ?? ($index+1), 7, '0', STR_PAD_LEFT));
            $pmtId->addChild('EndToEndId', str_pad($detail['numero_prelevement'] ?? $detail['numero_virement'] ?? ($index+1), 7, '0', STR_PAD_LEFT));

            $amt = $drctDbt->addChild('IntrBkSttlmAmt', number_format((float)($detail['montant'] ?? 0), 3, '.', ''));
            $amt->addAttribute('Ccy', $devise);

            $drctDbt->addChild('ChrgBr', 'SLEV');

            $cdtr = $drctDbt->addChild('Cdtr');
            $cdtr->addChild('Nm', $detail['nom_beneficiaire'] ?? 'N/A');

            $cdtrAcct = $drctDbt->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id', $detail['rib_creancier'] ?? $detail['rib_beneficiaire'] ?? '');

            $cdtrAgt   = $drctDbt->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', 'BSIETNTX');

            $dbtr = $drctDbt->addChild('Dbtr');
            $dbtr->addChild('Nm', 'N/A');

            $dbtrAcct = $drctDbt->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id', $detail['rib_payeur'] ?? $detail['rib_donneur'] ?? '');

            $dbtrAgt   = $drctDbt->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', 'BSIETNTX');

            if (!empty($detail['libelle_prelevement'])) {
                $rmtInf = $drctDbt->addChild('RmtInf');
                $rmtInf->addChild('Ustrd', trim($detail['libelle_prelevement']));
            }

            $splmtry  = $drctDbt->addChild('SplmtryData');
            $splmtry->addChild('PlcAndNm', 'PRELEVEMENT');
            $envlp    = $splmtry->addChild('Envlp');
            $suppData = $envlp->addChild('SupplementaryData');
            $suppData->addChild('ReferenceContratDomiciliation', $detail['ref_contrat'] ?? '');
            $suppData->addChild('CodeNational', $detail['code_emetteur'] ?? '');
            $suppData->addChild('CodeMAJ', '1');
            $suppData->addChild('DateMAJ', now()->format('Y-m-d'));
            $suppData->addChild('ZoneLibre', '');
            $suppData->addChild('NumeroDomiciliation', $detail['ref_contrat'] ?? '');
            $suppData->addChild('CodePayeur', $detail['motif_rejet'] ?? '00000000');
            if (!empty($detail['date_echeance'])) {
                $suppData->addChild('DateEcheance', $detail['date_echeance']);
            }
        }

        return $this->formaterXml($xml->asXML());
    }

    private function transformerCheque(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        $ficToFI = $xml->addChild('FIToFICstmrDrctDbt');
        $grpHdr  = $ficToFI->addChild('GrpHdr');
        $grpHdr->addChild('MsgId',   $global['numero_lot'] ?? 'LOT001');
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', count($details));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', $details[0]['type_valeur'] ?? '30');
        $ctgyPurp = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', 'BSIETNTX');

        $devise = $this->convertirDevise($global['code_devise'] ?? 'TND');

        foreach ($details as $index => $detail) {
            $chqTx = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId = $chqTx->addChild('PmtId');
            $pmtId->addChild('InstrId',    str_pad($detail['numero_cheque'] ?? $detail['numero_virement'] ?? ($index+1), 7, '0', STR_PAD_LEFT));
            $pmtId->addChild('EndToEndId', str_pad($detail['numero_cheque'] ?? $detail['numero_virement'] ?? ($index+1), 7, '0', STR_PAD_LEFT));

            $amt = $chqTx->addChild('IntrBkSttlmAmt', number_format((float)($detail['montant'] ?? 0), 3, '.', ''));
            $amt->addAttribute('Ccy', $devise);

            $chqTx->addChild('ChrgBr', 'SLEV');

            $cdtr = $chqTx->addChild('Cdtr');
            $cdtr->addChild('Nm', $detail['nom_beneficiaire'] ?? 'N/A');

            $cdtrAcct = $chqTx->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id', $detail['rib_beneficiaire'] ?? '');

            $cdtrAgt   = $chqTx->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', 'BSIETNTX');

            $dbtr = $chqTx->addChild('Dbtr');
            $dbtr->addChild('Nm', $detail['nom_donneur'] ?? 'N/A');

            $dbtrAcct = $chqTx->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id', $detail['rib_tireur'] ?? $detail['rib_donneur'] ?? '');

            $dbtrAgt   = $chqTx->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', 'BSIETNTX');

            $splmtry  = $chqTx->addChild('SplmtryData');
            $splmtry->addChild('PlcAndNm', 'CHEQUE');
            $envlp    = $splmtry->addChild('Envlp');
            $suppData = $envlp->addChild('SupplementaryData');
            if (!empty($detail['date_emission'])) {
                $suppData->addChild('DateEmission', $detail['date_emission']);
            }
            $suppData->addChild('ZoneLibre', '');
            if (!empty($detail['lieu_emission'])) {
                $suppData->addChild('LieuEmission', $detail['lieu_emission']);
            }
            $suppData->addChild('SituationBenef', $detail['situation_beneficiaire'] ?? '');
            $suppData->addChild('NatureCompte', $detail['nature_compte'] ?? '');
            if (!empty($detail['montant_provision'])) {
                $suppData->addChild('MontantProvision', number_format((float)$detail['montant_provision'], 3, '.', ''));
            }
            if (!empty($detail['motif_rejet'])) {
                $suppData->addChild('MotifRejet', $detail['motif_rejet']);
            }
        }

        return $this->formaterXml($xml->asXML());
    }

    private function transformerLettreChange(array $global, array $details): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Document/>');
        $xml->addAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pacs.003.001.09');

        $ficToFI = $xml->addChild('FIToFICstmrDrctDbt');
        $grpHdr  = $ficToFI->addChild('GrpHdr');
        $grpHdr->addChild('MsgId',   $global['numero_lot'] ?? 'LOT001');
        $grpHdr->addChild('CreDtTm', now()->format('Y-m-d\TH:i:s'));
        $grpHdr->addChild('NbOfTxs', count($details));

        $sttlmInf = $grpHdr->addChild('SttlmInf');
        $sttlmInf->addChild('SttlmMtd', 'CLRG');
        $clrSys = $sttlmInf->addChild('ClrSys');
        $clrSys->addChild('Prtry', $global['code_centre'] ?? '01');

        $pmtTpInf  = $grpHdr->addChild('PmtTpInf');
        $lclInstrm = $pmtTpInf->addChild('LclInstrm');
        $lclInstrm->addChild('Prtry', $details[0]['type_valeur'] ?? '40');
        $ctgyPurp = $pmtTpInf->addChild('CtgyPurp');
        $ctgyPurp->addChild('Prtry', '21');

        $instgAgt   = $grpHdr->addChild('InstgAgt');
        $instgAgtFi = $instgAgt->addChild('FinInstnId');
        $instgAgtFi->addChild('BICFI', 'BSIETNTX');

        $devise = $this->convertirDevise($global['code_devise'] ?? 'TND');

        foreach ($details as $index => $detail) {
            $ldcTx = $ficToFI->addChild('DrctDbtTxInf');
            $pmtId = $ldcTx->addChild('PmtId');
            $pmtId->addChild('InstrId',    str_pad($detail['numero_lettre_change'] ?? ($index+1), 12, '0', STR_PAD_LEFT));
            $pmtId->addChild('EndToEndId', str_pad($detail['numero_lettre_change'] ?? ($index+1), 12, '0', STR_PAD_LEFT));

            $amt = $ldcTx->addChild('IntrBkSttlmAmt', number_format((float)($detail['montant'] ?? 0), 3, '.', ''));
            $amt->addAttribute('Ccy', $devise);

            $ldcTx->addChild('ChrgBr', 'SLEV');

            $cdtr = $ldcTx->addChild('Cdtr');
            $cdtr->addChild('Nm', $detail['nom_cedant'] ?? 'N/A');

            $cdtrAcct = $ldcTx->addChild('CdtrAcct');
            $cdtrId   = $cdtrAcct->addChild('Id');
            $cdtrOthr = $cdtrId->addChild('Othr');
            $cdtrOthr->addChild('Id', $detail['rib_cedant'] ?? '');

            $cdtrAgt   = $ldcTx->addChild('CdtrAgt');
            $cdtrAgtFi = $cdtrAgt->addChild('FinInstnId');
            $cdtrAgtFi->addChild('BICFI', 'BSIETNTX');

            $dbtr = $ldcTx->addChild('Dbtr');
            $dbtr->addChild('Nm', $detail['nom_tire'] ?? 'N/A');

            $dbtrAcct = $ldcTx->addChild('DbtrAcct');
            $dbtrId   = $dbtrAcct->addChild('Id');
            $dbtrOthr = $dbtrId->addChild('Othr');
            $dbtrOthr->addChild('Id', $detail['rib_tire'] ?? '');

            $dbtrAgt   = $ldcTx->addChild('DbtrAgt');
            $dbtrAgtFi = $dbtrAgt->addChild('FinInstnId');
            $dbtrAgtFi->addChild('BICFI', 'BSIETNTX');

            $splmtry  = $ldcTx->addChild('SplmtryData');
            $splmtry->addChild('PlcAndNm', 'LETTRE_CHANGE');
            $envlp    = $splmtry->addChild('Envlp');
            $suppData = $envlp->addChild('SupplementaryData');
            if (!empty($detail['date_echeance'])) {
                $suppData->addChild('DateEcheance', $detail['date_echeance']);
            }
            if (!empty($detail['date_echeance_initiale'])) {
                $suppData->addChild('DateEcheanceInitiale', $detail['date_echeance_initiale']);
            }
            if (!empty($detail['date_creation'])) {
                $suppData->addChild('DateCreationLDC', $detail['date_creation']);
            }
            if (!empty($detail['lieu_creation'])) {
                $suppData->addChild('LieuCreation', trim($detail['lieu_creation']));
            }
            $suppData->addChild('NatureCompte', $detail['nature_compte'] ?? '');
            if (!empty($detail['code_risque_bct'])) {
                $suppData->addChild('CodeRisqueBCT', $detail['code_risque_bct']);
            }
            if (!empty($detail['montant_interets'])) {
                $suppData->addChild('MontantInterets', number_format((float)$detail['montant_interets'], 3, '.', ''));
            }
            if (!empty($detail['montant_frais_protest'])) {
                $suppData->addChild('MontantFraisProtest', number_format((float)$detail['montant_frais_protest'], 3, '.', ''));
            }
        }

        return $this->formaterXml($xml->asXML());
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