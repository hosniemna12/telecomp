
from flask import Flask, request, jsonify
import pickle
import numpy as np
import os

app = Flask(__name__)

# Charger le modele et les encoders au demarrage
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
with open(os.path.join(BASE_DIR, 'model.pkl'), 'rb') as f:
    model = pickle.load(f)
with open(os.path.join(BASE_DIR, 'encoders.pkl'), 'rb') as f:
    encoders = pickle.load(f)

print("Modele charge, serveur pret")

COLS_CAT = ['type_valeur','code_banque_don','code_banque_dest','sens','situation_donneur','type_compte']
COLS_ORDRE = ['type_valeur','montant','code_banque_don','code_banque_dest',
              'rib_donneur_valide','rib_beneficiaire_valide','jour_semaine',
              'est_fin_mois','echeance_depassee','ratio_provision',
              'montant_provision','sens','situation_donneur','type_compte']

def encoder(trans: dict) -> np.ndarray:
    t = dict(trans)
    for col in COLS_CAT:
        if col in t and col in encoders:
            try:
                t[col] = int(encoders[col].transform([str(t[col])])[0])
            except ValueError:
                t[col] = 0
    return np.array([[t.get(col, 0) for col in COLS_ORDRE]])

def score_to_couleur(score: int) -> str:
    if score >= 70: return 'rouge'
    if score >= 40: return 'orange'
    return 'vert'

@app.route('/health', methods=['GET'])
def health():
    return jsonify({'status': 'ok', 'model': 'RandomForest', 'features': len(COLS_ORDRE)})

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
            'score':   score,
            'couleur': score_to_couleur(score),
            'rejete':  score >= 50,
            'proba':   round(proba, 4)
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
                'id':      trans.get('id'),
                'score':   score,
                'couleur': score_to_couleur(score),
                'rejete':  score >= 50,
                'proba':   round(proba, 4)
            })
        except Exception as e:
            resultats.append({'id': trans.get('id'), 'error': str(e)})
    nb_rouge  = sum(1 for r in resultats if r.get('couleur')=='rouge')
    nb_orange = sum(1 for r in resultats if r.get('couleur')=='orange')
    nb_vert   = sum(1 for r in resultats if r.get('couleur')=='vert')
    score_global = int(sum(r.get('score',0) for r in resultats) / len(resultats)) if resultats else 0
    return jsonify({
        'transactions':   resultats,
        'resume': {
            'total':        len(resultats),
            'rouge':        nb_rouge,
            'orange':       nb_orange,
            'vert':         nb_vert,
            'score_global': score_global,
            'couleur_globale': score_to_couleur(score_global)
        }
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)
