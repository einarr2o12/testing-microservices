#!/bin/bash
set -e

# # Build and start services for testing
# echo "Starting services with docker-compose..."
# docker-compose -f docker-compose.yaml up -d

# Wait for review service to be ready
echo "Waiting for review service to be available..."
until $(curl --output /dev/null --silent --head --fail http://localhost:5000/health); do
    printf '.'
    sleep 2
done
echo -e "\nReview service is up!"

# Test the health endpoint
echo -e "\nTesting health endpoint:"
curl -s http://localhost:5000/health | jq

# Create a new review
echo -e "\nCreating a new review:"
CREATE_RESPONSE=$(curl -s -X POST http://localhost:5000/api/reviews \
  -H "Content-Type: application/json" \
  -d '{"product_id": 1, "rating": 5, "comment": "Great product!", "review_metadata": {"verified_purchase": true}}')
echo $CREATE_RESPONSE | jq
REVIEW_ID=$(echo $CREATE_RESPONSE | jq -r '.id')

# Get the review by ID
echo -e "\nFetching the review by ID ($REVIEW_ID):"
curl -s http://localhost:5000/api/reviews/$REVIEW_ID | jq

# Update the review
echo -e "\nUpdating the review:"
curl -s -X PUT http://localhost:5000/api/reviews/$REVIEW_ID \
  -H "Content-Type: application/json" \
  -d '{"rating": 4, "comment": "Updated comment", "review_metadata": {"verified_purchase": true, "purchase_date": "2025-05-01"}}' | jq

# Get all reviews
echo -e "\nFetching all reviews:"
curl -s http://localhost:5000/api/reviews | jq

# Get reviews for product
echo -e "\nFetching reviews for product ID 1:"
curl -s http://localhost:5000/api/products/1/reviews | jq

# Delete the review
echo -e "\nDeleting the review:"
curl -s -X DELETE http://localhost:5000/api/reviews/$REVIEW_ID | jq

# Verify deletion
echo -e "\nVerifying review was deleted by trying to fetch it:"
curl -s http://localhost:5000/api/reviews/$REVIEW_ID

# Clean up
echo -e "\nTests completed! Stopping containers..."
docker-compose -f docker-compose-test.yml down -v

echo -e "\nTests completed successfully!"