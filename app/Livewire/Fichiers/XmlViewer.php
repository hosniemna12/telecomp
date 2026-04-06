<?php

namespace App\Livewire\Fichiers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\TcFichier;
use App\Models\TcXmlProduit;

#[Layout('layouts.app')]
class XmlViewer extends Component
{
    public int    $fichierId;
    public string $xmlFormate  = '';
    public string $typeMessage = '';
    public bool   $copie       = false;

    public function mount(int $id): void
    {
        $this->fichierId = $id;

        $fichier = TcFichier::with('xmlProduits')->findOrFail($id);
        $xml     = $fichier->xmlProduits->first();

        if ($xml) {
            $this->typeMessage = $xml->type_message;
            $this->xmlFormate  = $this->formaterXml($xml->contenu_xml);
        }
    }

    public function telecharger(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fichier = TcFichier::with('xmlProduits')->findOrFail($this->fichierId);
        $xml     = $fichier->xmlProduits->first();

        return response()->streamDownload(function () use ($xml) {
            echo $xml->contenu_xml;
        }, str_replace('.ENV', '.xml', $fichier->nom_fichier));
    }

    private function formaterXml(string $xml): string
    {
        try {
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput       = true;
            $dom->loadXML($xml);
            return htmlspecialchars($dom->saveXML());
        } catch (\Exception $e) {
            return htmlspecialchars($xml);
        }
    }

    public function render()
    {
        $fichier = TcFichier::findOrFail($this->fichierId);
        return view('livewire.fichiers.xml-viewer', compact('fichier'));
    }
}