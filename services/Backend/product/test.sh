#!/bin/bash
set -e

# Build and start services for testing
# echo "Starting services with docker-compose..."
# docker-compose -f docker-compose-test.yml up -d

# Wait for product service to be ready
# echo "Waiting for product service to be available..."
# until $(curl --output /dev/null --silent --head --fail http://localhost:8080/health); do
#     printf '.'
#     sleep 2
# done
# echo -e "\nProduct service is up!"

# # Test the health endpoint
# echo -e "\nTesting health endpoint:"
# curl -s http://localhost:8080/health | jq

# Create a new product
echo -e "\nCreating a new product:"
CREATE_RESPONSE=$(curl -s -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -d '{"name": "Test Product", "price": 99.99, "category_id": "1", "description": "This is a test product"}')
echo $CREATE_RESPONSE | jq
PRODUCT_ID=$(echo $CREATE_RESPONSE | jq -r '.id')

# Get the product by ID
echo -e "\nFetching the product by ID ($PRODUCT_ID):"
curl -s http://localhost:8080/api/products/$PRODUCT_ID | jq

# Update the product
echo -e "\nUpdating the product:"
curl -s -X PUT http://localhost:8080/api/products/$PRODUCT_ID \
  -H "Content-Type: application/json" \
  -d '{"name": "Updated Product", "price": 149.99, "description": "This product has been updated"}' | jq

# Get all products
echo -e "\nFetching all products:"
curl -s http://localhost:8080/api/products | jq

# Get products by category
echo -e "\nFetching products for category ID 1:"
curl -s http://localhost:8080/api/products/category/1 | jq

# Test with invalid category ID
echo -e "\nTesting with invalid category (should be mocked to succeed):"
curl -s -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -d '{"name": "Another Product", "price": 199.99, "category_id": "999", "description": "This should work with our mock server"}' | jq

# Delete the product
echo -e "\nDeleting the product:"
curl -s -X DELETE http://localhost:8080/api/products/$PRODUCT_ID | jq

# Verify deletion
echo -e "\nVerifying product was deleted by trying to fetch it:"
curl -s http://localhost:8080/api/products/$PRODUCT_ID | jq

# Clean up
echo -e "\nTests completed! Stopping containers..."
# docker-compose -f docker-compose-test.yml down -v

echo -e "\nTests completed successfully!"