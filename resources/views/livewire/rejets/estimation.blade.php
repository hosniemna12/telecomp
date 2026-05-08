<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-6">Estimation du risque de rejet</h2>

    @if ($error)
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>Erreur :</strong> {{ $error }}
        </div>
    @endif

    <form wire:submit="estimer" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Montant -->
            <div>
                <label class="block text-sm font-medium mb-1">Montant (€)</label>
                <input type="number" wire:model="montant" 
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                       step="0.01" required>
                @error('montant') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Type de valeur -->
            <div>
                <label class="block text-sm font-medium mb-1">Type de valeur</label>
                <select wire:model="type_valeur" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="virement">Virement</option>
                    <option value="cheque">Chèque</option>
                    <option value="prelevement">Prélèvement</option>
                    <option value="tirage">Tirage</option>
                </select>
                @error('type_valeur') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Code banque donneur -->
            <div>
                <label class="block text-sm font-medium mb-1">Code banque donneur</label>
                <input type="text" wire:model="code_banque_don" 
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="00000" required>
                @error('code_banque_don') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Code banque bénéficiaire -->
            <div>
                <label class="block text-sm font-medium mb-1">Code banque bénéficiaire</label>
                <input type="text" wire:model="code_banque_dest" 
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                       placeholder="00000" required>
                @error('code_banque_dest') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Sens -->
            <div>
                <label class="block text-sm font-medium mb-1">Sens</label>
                <select wire:model="sens" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="debit">Débit</option>
                    <option value="credit">Crédit</option>
                </select>
                @error('sens') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Situation donneur -->
            <div>
                <label class="block text-sm font-medium mb-1">Situation donneur</label>
                <select wire:model="situation_donneur" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="actif">Actif</option>
                    <option value="suspendu">Suspendu</option>
                    <option value="decede">Décédé</option>
                    <option value="clotured">Clôturé</option>
                </select>
                @error('situation_donneur') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Type de compte -->
            <div>
                <label class="block text-sm font-medium mb-1">Type de compte</label>
                <select wire:model="type_compte" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="courant">Courant</option>
                    <option value="epargne">Épargne</option>
                    <option value="titre">Titre</option>
                </select>
                @error('type_compte') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- RIB donneur valide -->
            <div>
                <label class="block text-sm font-medium mb-1">RIB donneur valide</label>
                <select wire:model="rib_donneur_valide" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="1">Oui</option>
                    <option value="0">Non</option>
                </select>
            </div>

            <!-- RIB bénéficiaire valide -->
            <div>
                <label class="block text-sm font-medium mb-1">RIB bénéficiaire valide</label>
                <select wire:model="rib_beneficiaire_valide" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="1">Oui</option>
                    <option value="0">Non</option>
                </select>
            </div>

            <!-- Jour de semaine -->
            <div>
                <label class="block text-sm font-medium mb-1">Jour de semaine (0-6)</label>
                <input type="number" wire:model="jour_semaine" min="0" max="6"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Fin de mois -->
            <div>
                <label class="block text-sm font-medium mb-1">Fin de mois</label>
                <select wire:model="est_fin_mois" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="0">Non</option>
                    <option value="1">Oui</option>
                </select>
            </div>

            <!-- Échéance dépassée -->
            <div>
                <label class="block text-sm font-medium mb-1">Échéance dépassée</label>
                <select wire:model="echeance_depassee" 
                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="0">Non</option>
                    <option value="1">Oui</option>
                </select>
            </div>

            <!-- Ratio provision -->
            <div>
                <label class="block text-sm font-medium mb-1">Ratio provision</label>
                <input type="number" wire:model="ratio_provision" step="0.01" min="0" max="1"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Montant provision -->
            <div>
                <label class="block text-sm font-medium mb-1">Montant provision (€)</label>
                <input type="number" wire:model="montant_provision" step="0.01"
                       class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex gap-3 mt-6">
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    wire:loading.attr="disabled">
                <span wire:loading.remove>Estimer le risque</span>
                <span wire:loading>Analyse en cours...</span>
            </button>

            <button type="button" 
                    wire:click="tester_ml"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    wire:loading.attr="disabled">
                Tester connexion ML
            </button>
        </div>
    </form>

    <!-- Résultat -->
    @if ($score !== null)
        <div class="mt-8 p-6 rounded-lg border-2" 
             :class="{
                 'bg-red-50 border-red-300': @entangle('couleur') === 'rouge',
                 'bg-orange-50 border-orange-300': @entangle('couleur') === 'orange',
                 'bg-green-50 border-green-300': @entangle('couleur') === 'vert'
             }">
            
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold">Résultat de l'estimation</h3>
                <span class="text-4xl font-bold"
                      :class="{
                          'text-red-600': @entangle('couleur') === 'rouge',
                          'text-orange-600': @entangle('couleur') === 'orange',
                          'text-green-600': @entangle('couleur') === 'vert'
                      }">
                    {{ $score }}%
                </span>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Couleur du risque</p>
                    <p class="text-lg font-semibold capitalize">
                        @if ($couleur === 'rouge')
                            🔴 Rouge (Très risqué)
                        @elseif ($couleur === 'orange')
                            🟠 Orange (Risque modéré)
                        @else
                            🟢 Vert (Faible risque)
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Probabilité de rejet</p>
                    <p class="text-lg font-semibold">{{ $proba * 100 }}%</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600">Recommandation</p>
                    <p class="text-lg font-semibold">
                        @if ($rejete)
                            ⚠️ À rejeter
                        @else
                            ✓ À accepter
                        @endif
                    </p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full transition-all duration-500"
                         :class="{
                             'bg-red-600': @entangle('couleur') === 'rouge',
                             'bg-orange-600': @entangle('couleur') === 'orange',
                             'bg-green-600': @entangle('couleur') === 'vert'
                         }"
                         :style="`width: ${@entangle('score')}%`">
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
