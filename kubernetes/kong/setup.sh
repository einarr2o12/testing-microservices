#!/bin/bash

# Kong API Gateway Setup Script
# This script configures Kong API Gateway to route traffic to microservices

# Kong Admin API endpoint
KONG_ADMIN_URL="http://137.184.251.85:8001"

# Create the Category Service
curl -X POST ${KONG_ADMIN_URL}/services \
  --data "name=category-service" \
  --data "url=http://category-service.category-service.svc.cluster.local:3000"

# # Create the Category Route
curl -X POST ${KONG_ADMIN_URL}/services/category-service/routes \
  --data "name=category-service" \
  --data "paths[]=/category"

# Create the Product Service
curl -X POST http://137.184.251.85:8001/services \
  --data "name=product-service" \
  --data "url=http://product-service.product-service.svc.cluster.local:80"

# Create the Product Route
curl -X POST http://137.184.251.85:8001/services/product-service/routes \
  --data "name=product-service" \
  --data "paths[]=/product"

# Create the frontend Service
curl -X POST http://137.184.251.85:8001/services \
  --data "name=review-service" \
  --data "url=http://review-service.review-service.svc.cluster.local:5000"

# Create the Review Route
curl -X POST http://137.184.251.85:8001/services/review-service/routes \
  --data "name=review-service" \
  --data "paths[]=/review"

# Create the frontend Service
curl -X POST http://137.184.251.85:8001/services \
  --data "name=frontend-service" \
  --data "url=http://frontend-service.frontend.svc.cluster.local:8080"

# Create the Review Route
curl -X POST http://137.184.251.85:8001/services/frontend-service/routes \
  --data "name=frontend-service" \
  --data "paths[]=/"

echo "Kong API Gateway configuration completed successfully."