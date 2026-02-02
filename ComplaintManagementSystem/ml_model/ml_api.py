"""
Smart Complaint Management System
ML Model API - Priority Classification

This Flask API provides complaint priority prediction using a trained ML model.
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import pickle
import os
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.model_selection import train_test_split
from sklearn.pipeline import Pipeline
from sklearn.metrics import classification_report, accuracy_score
import warnings
warnings.filterwarnings('ignore')

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP backend

# Configuration
MODEL_PATH = 'complaint_model.pkl'
DATA_PATH = os.environ.get('DATA_PATH', 'data.csv')  # Supports Docker environment

# Global model variable
model = None

def train_model():
    """
    Train the complaint priority classification model
    """
    print("Loading training data...")
    df = pd.read_csv(DATA_PATH)
    
    print(f"Dataset shape: {df.shape}")
    print(f"Priority distribution:\n{df['priority'].value_counts()}")
    
    # Split data
    X = df['complaint_text']
    y = df['priority']
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    
    print("\nTraining model...")
    # Create pipeline with TF-IDF and Naive Bayes
    model = Pipeline([
        ('tfidf', TfidfVectorizer(
            max_features=5000,
            ngram_range=(1, 2),
            stop_words='english',
            min_df=2
        )),
        ('classifier', MultinomialNB(alpha=0.1))
    ])
    
    # Train the model
    model.fit(X_train, y_train)
    
    # Evaluate
    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    
    print(f"\nModel Accuracy: {accuracy:.4f}")
    print("\nClassification Report:")
    print(classification_report(y_test, y_pred))
    
    # Save model
    with open(MODEL_PATH, 'wb') as f:
        pickle.dump(model, f)
    print(f"\nModel saved to {MODEL_PATH}")
    
    return model

def load_model():
    """
    Load the trained model or train a new one if not found
    """
    global model
    
    if os.path.exists(MODEL_PATH):
        print("Loading existing model...")
        with open(MODEL_PATH, 'rb') as f:
            model = pickle.load(f)
        print("Model loaded successfully!")
    else:
        print("No existing model found. Training new model...")
        model = train_model()
    
    return model

@app.route('/predict', methods=['POST'])
def predict():
    """
    Predict priority for a complaint
    """
    try:
        data = request.get_json(force=True, silent=True)
        
        if not data or 'complaint_text' not in data:
            return jsonify({
                'error': 'complaint_text is required'
            }), 400
        
        complaint_text = data['complaint_text']
        
        if not complaint_text or len(complaint_text.strip()) == 0:
            return jsonify({
                'error': 'complaint_text cannot be empty'
            }), 400
        
        # Predict priority
        prediction = model.predict([complaint_text])[0]
        
        # Get probability scores
        probabilities = model.predict_proba([complaint_text])[0]
        max_prob = max(probabilities)
        
        # Get class labels
        classes = model.classes_
        priority_scores = {
            classes[i]: float(probabilities[i]) 
            for i in range(len(classes))
        }
        
        return jsonify({
            'priority': prediction,
            'confidence': float(max_prob),
            'all_scores': priority_scores,
            'model_version': 'v1.0'
        })
    
    except Exception as e:
        return jsonify({
            'error': str(e)
        }), 500

@app.route('/train', methods=['POST'])
def retrain_model():
    """
    Retrain the model with updated data
    """
    try:
        global model
        model = train_model()
        
        return jsonify({
            'message': 'Model retrained successfully',
            'model_version': 'v1.0'
        })
    
    except Exception as e:
        return jsonify({
            'error': str(e)
        }), 500

@app.route('/health', methods=['GET'])
def health_check():
    """
    Health check endpoint
    """
    return jsonify({
        'status': 'healthy',
        'model_loaded': model is not None,
        'version': 'v1.0'
    })

@app.route('/stats', methods=['GET'])
def model_stats():
    """
    Get model statistics
    """
    try:
        if model is None:
            return jsonify({
                'error': 'Model not loaded'
            }), 400
        
        # Load data for stats
        df = pd.read_csv(DATA_PATH)
        
        return jsonify({
            'total_samples': len(df),
            'priority_distribution': df['priority'].value_counts().to_dict(),
            'classes': model.classes_.tolist(),
            'model_type': 'Naive Bayes with TF-IDF'
        })
    
    except Exception as e:
        return jsonify({
            'error': str(e)
        }), 500

# Load model on module import (works with Gunicorn)
print("=" * 60)
print("Smart Complaint Management System - ML API")
print("=" * 60)
load_model()

if __name__ == '__main__':
    print("\nStarting Flask API server...")
    print("API Endpoints:")
    print("  - POST /predict      : Predict complaint priority")
    print("  - POST /train        : Retrain the model")
    print("  - GET  /health       : Health check")
    print("  - GET  /stats        : Model statistics")
    print("=" * 60)
    
    # Run Flask app
    app.run(host='0.0.0.0', port=5000, debug=True)
