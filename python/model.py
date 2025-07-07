# python/model.py
import sys
import json
import joblib
import os
import numpy as np
import pandas as pd

MODEL_STORAGE_PATH = r'C:\Users\USER\Documents\MachineLearning'

def clean_symptom(symptom):
    """Nettoie et standardise les noms de symptômes comme dans le modèle d'entraînement"""
    if pd.isna(symptom):
        return symptom
    return symptom.strip().lower().replace(' ', '_').replace('__', '_')

def get_top_predictions(model, encoder, probabilities, top_n=3):
    """Retourne les top N prédictions avec leurs probabilités"""
    top_indices = np.argsort(probabilities)[-top_n:][::-1]
    predictions = []

    for idx in top_indices:
        disease_name = encoder.inverse_transform([idx])[0]
        probability = float(probabilities[idx])
        predictions.append({
            'maladie': disease_name,
            'probabilite': probability,
            'pourcentage': round(probability * 100, 1)
        })

    return predictions

try:
    # Chargement des modèles
    model = joblib.load(os.path.join(MODEL_STORAGE_PATH, 'random_forest_moderate.pkl'))
    label_encoder = joblib.load(os.path.join(MODEL_STORAGE_PATH, 'label_encoder_moderate.pkl'))
    all_symptoms = joblib.load(os.path.join(MODEL_STORAGE_PATH, 'symptoms_list.pkl'))

    # Récupération des données d'entrée
    input_data = sys.argv[1]
    data = json.loads(input_data)
    user_symptoms = data['symptoms']
    patient_info = data.get('patient_info', {})

    # Nettoyer les symptômes de l'utilisateur
    cleaned_user_symptoms = [clean_symptom(symptom) for symptom in user_symptoms]

    # Préparation des features avec noms de colonnes
    features = pd.DataFrame([0] * len(all_symptoms), index=all_symptoms, columns=['value']).T
    for symptom in cleaned_user_symptoms:
        if symptom in all_symptoms:
            features[symptom] = 1

    # Prédiction principale
    prediction_encoded = model.predict(features)[0]
    main_prediction = label_encoder.inverse_transform([prediction_encoded])[0]

    # Calcul des probabilités et confiance
    confidence = None
    top_predictions = []

    if hasattr(model, 'predict_proba'):
        probabilities = model.predict_proba(features)[0]
        confidence = float(np.max(probabilities))
        top_predictions = get_top_predictions(model, label_encoder, probabilities, 3)

    # Déterminer le niveau de confiance
    confidence_level = "inconnu"
    if confidence is not None:
        if confidence >= 0.8:
            confidence_level = "tres_eleve"
        elif confidence >= 0.6:
            confidence_level = "eleve"
        elif confidence >= 0.4:
            confidence_level = "moyen"
        elif confidence >= 0.2:
            confidence_level = "faible"
        else:
            confidence_level = "tres_faible"

    # Compter les symptômes reconnus
    recognized_symptoms = [s for s in cleaned_user_symptoms if s in all_symptoms]
    unrecognized_symptoms = [s for s in cleaned_user_symptoms if s not in all_symptoms]

    # Résultat structuré
    result = {
        'success': True,
        'maladie': main_prediction,
        'symptomes': user_symptoms,
        'symptomes_nettoyes': cleaned_user_symptoms,
        'symptomes_reconnus': recognized_symptoms,
        'symptomes_non_reconnus': unrecognized_symptoms,
        'confidence': confidence,
        'niveau_confiance': confidence_level,
        'top_predictions': top_predictions,
        'nombre_features_actives': int(features.values.sum()),
        'patient_info': patient_info,
        'metadata': {
            'model_type': 'RandomForest',
            'total_symptoms_db': len(all_symptoms),
            'symptoms_matched': len(recognized_symptoms),
            'matching_rate': round(len(recognized_symptoms) / len(cleaned_user_symptoms) * 100, 1) if cleaned_user_symptoms else 0
        }
    }

    print(json.dumps(result))

except FileNotFoundError as e:
    error_result = {
        'success': False,
        'error': f'Fichier modèle non trouvé: {str(e)}',
        'error_type': 'file_not_found'
    }
    print(json.dumps(error_result))

except json.JSONDecodeError as e:
    error_result = {
        'success': False,
        'error': f'Erreur de décodage JSON: {str(e)}',
        'error_type': 'json_decode_error'
    }
    print(json.dumps(error_result))

except Exception as e:
    error_result = {
        'success': False,
        'error': f'Erreur inattendue: {str(e)}',
        'error_type': 'unexpected_error'
    }
    print(json.dumps(error_result))
