<div>

    {{-- Bouton retour --}}
    <div class="mb-6">
        <a href="{{ route('fichiers.show', $fichier->id) }}"
           class="text-slate-400 hover:text-white text-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux détails
        </a>
    </div>

    {{-- En-tête --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">XML ISO 20022</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $fichier->nom_fichier }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="bg-purple-600/20 text-purple-400 border border-purple-600/30
                         text-sm px-4 py-1.5 rounded-full">
                {{ $typeMessage }}
            </span>
            <button
                wire:click="telecharger"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm
                       px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Télécharger XML
            </button>
            <button
                onclick="copyXml()"
                class="bg-slate-700 hover:bg-slate-600 text-white text-sm
                       px-4 py-2 rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span id="copy-label">Copier</span>
            </button>
        </div>
    </div>

    {{-- Éditeur XML --}}
    @if($xmlFormate)

        <div class="bg-slate-900 border border-slate-700 rounded-xl overflow-hidden">

            {{-- Barre du haut --}}
            <div class="bg-slate-800 border-b border-slate-700 px-4 py-2
                        flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                </div>
                <span class="text-slate-500 text-xs font-mono">
                    {{ str_replace('.ENV', '.xml', $fichier->nom_fichier) }}
                </span>
                <span class="text-slate-500 text-xs">XML ISO 20022</span>
            </div>

            {{-- Contenu XML --}}
            <div class="overflow-auto max-h-screen">
                <table class="w-full text-xs font-mono">
                    <tbody>
                        @foreach(explode("\n", $xmlFormate) as $numero => $ligne)
                            <tr class="hover:bg-slate-800/50 group">
                                <td class="select-none text-right text-slate-600 px-4 py-0.5
                                           border-r border-slate-800 w-12
                                           group-hover:text-slate-500 align-top">
                                    {{ $numero + 1 }}
                                </td>
                                <td class="px-4 py-0.5 whitespace-pre text-slate-300">
                                    @php
                                        $l = $ligne;
                                        // Déclaration XML → violet
                                        $l = preg_replace(
                                            '/(&lt;\?xml[^?]*\?&gt;)/',
                                            '<span class="text-purple-400">$1</span>',
                                            $l
                                        );
                                        // Balises fermantes → bleu clair
                                        $l = preg_replace(
                                            '/(&lt;\/[a-zA-Z][a-zA-Z0-9:]*&gt;)/',
                                            '<span class="text-blue-300">$1</span>',
                                            $l
                                        );
                                        // Balises ouvrantes → bleu
                                        $l = preg_replace(
                                            '/(&lt;[a-zA-Z][a-zA-Z0-9:]*(?:\s[^&gt;]*)?\/?&gt;)/',
                                            '<span class="text-blue-400">$1</span>',
                                            $l
                                        );
                                        // Valeurs texte → blanc
                                        $l = preg_replace(
                                            '/(&gt;)([^&<\n]+)(&lt;)/',
                                            '$1<span class="text-white font-medium">$2</span>$3',
                                            $l
                                        );
                                        // Attributs xmlns → jaune
                                        $l = preg_replace(
                                            '/(xmlns(?::[a-z]+)?=&quot;[^&]*&quot;)/',
                                            '<span class="text-yellow-300">$1</span>',
                                            $l
                                        );
                                    @endphp
                                    {!! $l !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        {{-- Stats XML --}}
        <div class="mt-4 grid grid-cols-3 gap-4">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
                <div class="text-xl font-bold text-white">
                    {{ substr_count($xmlFormate, "\n") + 1 }}
                </div>
                <div class="text-slate-400 text-xs mt-1">Lignes</div>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
                <div class="text-xl font-bold text-white">
                    {{ number_format(strlen(html_entity_decode($xmlFormate)) / 1024, 2) }} KB
                </div>
                <div class="text-slate-400 text-xs mt-1">Taille</div>
            </div>
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
                <div class="text-xl font-bold text-purple-400">
                    {{ $typeMessage }}
                </div>
                <div class="text-slate-400 text-xs mt-1">Standard ISO 20022</div>
            </div>
        </div>

    @else
        <div class="bg-slate-800 border border-slate-700 rounded-xl p-16 text-center">
            <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
            <p class="text-slate-400 text-sm">Aucun XML généré pour ce fichier</p>
            <a href="{{ route('fichiers.show', $fichier->id) }}"
               class="text-blue-400 hover:text-blue-300 text-sm mt-2 inline-block transition">
                Retour aux détails
            </a>
        </div>
    @endif

</div>

{{-- Script copier --}}
<script>
function copyXml() {
    const lignes = document.querySelectorAll('table tbody tr td:last-child');
    let texte = '';
    lignes.forEach(td => {
        texte += td.innerText + '\n';
    });
    navigator.clipboard.writeText(texte).then(() => {
        const label = document.getElementById('copy-label');
        label.textContent = 'Copié !';
        setTimeout(() => label.textContent = 'Copier', 2000);
    });
}
</script>