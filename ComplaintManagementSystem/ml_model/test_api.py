"""
Test Script for ML API
Smart Complaint Management System
"""

import requests
import json

# Configuration
API_URL = "http://localhost:5000"

def test_health():
    """Test health check endpoint"""
    print("Testing /health endpoint...")
    response = requests.get(f"{API_URL}/health")
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    print("-" * 60)

def test_stats():
    """Test statistics endpoint"""
    print("Testing /stats endpoint...")
    response = requests.get(f"{API_URL}/stats")
    print(f"Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    print("-" * 60)

def test_predict():
    """Test prediction endpoint"""
    print("Testing /predict endpoint...")
    
    test_cases = [
        "Server is down and not responding",
        "Could you add dark mode feature?",
        "Application is slow during peak hours",
        "Hello, how are you doing today?",
        "Critical security breach detected in admin panel"
    ]
    
    for complaint in test_cases:
        print(f"\nComplaint: {complaint}")
        response = requests.post(
            f"{API_URL}/predict",
            json={"complaint_text": complaint},
            headers={"Content-Type": "application/json"}
        )
        print(f"Status Code: {response.status_code}")
        print(f"Response: {json.dumps(response.json(), indent=2)}")
    print("-" * 60)

def test_error_handling():
    """Test error handling"""
    print("Testing error handling...")
    
    # Empty complaint
    response = requests.post(
        f"{API_URL}/predict",
        json={"complaint_text": ""},
        headers={"Content-Type": "application/json"}
    )
    print(f"Empty complaint - Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    
    # Missing field
    response = requests.post(
        f"{API_URL}/predict",
        json={},
        headers={"Content-Type": "application/json"}
    )
    print(f"\nMissing field - Status Code: {response.status_code}")
    print(f"Response: {json.dumps(response.json(), indent=2)}")
    print("-" * 60)

if __name__ == "__main__":
    print("=" * 60)
    print("ML API Test Suite")
    print("=" * 60)
    
    try:
        test_health()
        test_stats()
        test_predict()
        test_error_handling()
        
        print("\n" + "=" * 60)
        print("All tests completed!")
        print("=" * 60)
        
    except requests.exceptions.ConnectionError:
        print("Error: Cannot connect to ML API.")
        print("Make sure the API is running on http://localhost:5000")
    except Exception as e:
        print(f"Error: {str(e)}")
