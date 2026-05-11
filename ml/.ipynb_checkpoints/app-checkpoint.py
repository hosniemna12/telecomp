from flask import Flask, request, jsonify
import pickle
import numpy as np
import os
import sys

app = Flask(__name__)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, 'model.pkl')
ENCODERS_PATH = os.path.join(BASE_DIR, 'encoders.pkl')

# ────────────────────────────────────────────────────────────
# Chargement du modele et des encodeurs au demarrage
# ────────────────────────────────────────────────────────────
try:
    with open(MODEL_PATH, 'rb') as f:
        model = pickle.load(f)
    print(f"Modele charge depuis {MODEL_PATH}")
except FileNotFoundError:
    print(f"Erreur : {MODEL_PATH} introuvable")
    sys.exit(1)

try:
    with open(ENCODERS_PATH, 'rb') as f:
        encoders = pickle.load(f)
    print(f"Encoders charges depuis {ENCODERS_PATH}")
except FileNotFoundError:
    print(f"Erreur : {ENCODERS_PATH} introuvable")
    sys.exit(1)

print(f"Serveur pret : {model.n_estimators} arbres, {model.n_features_in_} features")
print("URL : http://127.0.0.1:5000")

# ────────────────────────────────────────────────────────────
# Configuration des features (DOIT correspondre au Notebook 2)
# ────────────────────────────────────────────────────────────
COLS_CAT = ['type_valeur', 'code_banque_don', 'code_banque_dest',
            'situation_donneur', 'type_compte']

COLS_ORDRE = [
    'type_valeur', 'montant',
    'code_banque_don', 'code_banque_dest',
    'rib_donneur_valide', 'rib_beneficiaire_valide',
    'echeance_depassee', 'meme_banque',
    'situation_donneur', 'type_compte',
]


def encoder(trans):
    """Encode une transaction (dict) en vecteur numpy."""
    t = dict(trans)
    for col in COLS_CAT:
        if col in t and col in encoders:
            try:
                t[col] = int(encoders[col].transform([str(t[col])])[0])
            except (ValueError, KeyError):
                t[col] = 0  # categorie inconnue
    return np.array([[t.get(col, 0) for col in COLS_ORDRE]])


def score_to_couleur(score):
    if score >= 70: return 'rouge'
    if score >= 40: return 'orange'
    return 'vert'


def expliquer(trans):
    """
    Genere des explications human-readable basees sur les regles SIBTEL.
    Retourne max 3 raisons triees par gravite.
    """
    raisons = []

    # RIB donneur invalide
    if trans.get('rib_donneur_valide') == 0:
        raisons.append({
            'libelle': 'RIB donneur invalide',
            'detail':  'Cle modulo 97 incorrecte (code SIBTEL 07)',
            'gravite': 'haute',
        })

    # RIB beneficiaire invalide
    if trans.get('rib_beneficiaire_valide') == 0:
        raisons.append({
            'libelle': 'RIB beneficiaire invalide',
            'detail':  'Cle modulo 97 incorrecte (code SIBTEL 07)',
            'gravite': 'haute',
        })

    # Situation du donneur
    sit = str(trans.get('situation_donneur', '0'))
    if sit == '2':
        raisons.append({
            'libelle': 'Compte cloture',
            'detail':  'Le compte donneur est cloture (code SIBTEL 02)',
            'gravite': 'haute',
        })
    elif sit == '3':
        raisons.append({
            'libelle': 'Compte bloque',
            'detail':  'Saisie judiciaire ou succession (code SIBTEL 04)',
            'gravite': 'haute',
        })
    elif sit == '1':
        raisons.append({
            'libelle': 'Donneur interdit de chequier',
            'detail':  'Inscription FCC/FIPI (code SIBTEL 08)',
            'gravite': 'haute',
        })

    # Echeance depassee
    if trans.get('echeance_depassee') == 1:
        raisons.append({
            'libelle': 'Echeance depassee',
            'detail':  'Date d echeance anterieure a aujourd hui (code SIBTEL 09)',
            'gravite': 'haute',
        })

    # Montant tres eleve
    montant = float(trans.get('montant', 0))
    if montant > 250000:
        raisons.append({
            'libelle': 'Montant tres eleve',
            'detail':  f'{montant:,.0f} TND - verification recommandee',
            'gravite': 'moyenne',
        })

    # Cheque inter-banque (risque modere)
    if (trans.get('meme_banque') == 0 and 
        str(trans.get('type_valeur', '')) in ['30', '31', '32']):
        if len(raisons) < 3:
            raisons.append({
                'libelle': 'Cheque inter-banque',
                'detail':  'Risque legerement plus eleve qu en intra-banque',
                'gravite': 'faible',
            })

    return raisons[:3]


# ────────────────────────────────────────────────────────────
# Routes API
# ────────────────────────────────────────────────────────────

@app.route('/health', methods=['GET'])
def health():
    return jsonify({
        'status': 'ok',
        'model': 'RandomForest',
        'features': len(COLS_ORDRE),
        'arbres': model.n_estimators,
    })


@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data:
        return jsonify({'error': 'JSON manquant'}), 400
    try:
        X = encoder(data)
        proba = float(model.predict_proba(X)[0][1])
        score = int(proba * 100)
        return jsonify({
            'score':        score,
            'couleur':      score_to_couleur(score),
            'rejete':       score >= 50,
            'proba':        round(proba, 4),
            'explications': expliquer(data),
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/predict-batch', methods=['POST'])
def predict_batch():
    data = request.get_json()
    if not data or 'transactions' not in data:
        return jsonify({'error': 'Champ transactions manquant'}), 400

    resultats = []
    for trans in data['transactions']:
        try:
            X = encoder(trans)
            proba = float(model.predict_proba(X)[0][1])
            score = int(proba * 100)
            resultats.append({
                'id':           trans.get('id'),
                'score':        score,
                'couleur':      score_to_couleur(score),
                'rejete':       score >= 50,
                'proba':        round(proba, 4),
                'explications': expliquer(trans),
            })
        except Exception as e:
            resultats.append({'id': trans.get('id'), 'error': str(e)})

    nb = {c: sum(1 for r in resultats if r.get('couleur') == c) for c in ['rouge', 'orange', 'vert']}
    sg = int(sum(r.get('score', 0) for r in resultats) / len(resultats)) if resultats else 0

    return jsonify({
        'transactions': resultats,
        'resume': {
            'total': len(resultats),
            'rouge': nb['rouge'],
            'orange': nb['orange'],
            'vert': nb['vert'],
            'score_global': sg,
            'couleur_globale': score_to_couleur(sg),
        }
    })


if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)