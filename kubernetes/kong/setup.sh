#!/bin/bash

# Kong API Gateway Setup Script
# This script configures Kong API Gateway to route traffic to microservices

# Kong Admin API endpoint
KONG_ADMIN_URL="http://209.38.57.91:8001"

# Istio Gateway URL
ISTIO_GATEWAY="http://istio-ingressgateway.istio-system.svc.cluster.local:80"

# Create the Category Service (route through Istio)
curl -X POST http://209.38.57.91:8001/services \
  --data "name=category-service" \
  --data "url=${ISTIO_GATEWAY}/api/categories"

# Create the Category Route
curl -X POST http://209.38.57.91:8001/services/category-service/routes \
  --data "name=category-service" \
  --data "paths[]=/api/categories" \
  --data "strip_path=true"

# Create the Product Service (route through Istio)
curl -X POST http://209.38.57.91:8001/services \
  --data "name=product-service" \
  --data "url=${ISTIO_GATEWAY}/api/products"

# Create the Product Route
curl -X POST http://209.38.57.91:8001/services/product-service/routes \
  --data "name=product-service" \
  --data "paths[]=/api/products" \
  --data "strip_path=true"

# Create the Review Service (route through Istio)
curl -X POST http://209.38.57.91:8001/services \
  --data "name=review-service" \
  --data "url=${ISTIO_GATEWAY}/api/reviews"

# Create the Review Route
curl -X POST http://209.38.57.91:8001/services/review-service/routes \
  --data "name=review-service" \
  --data "paths[]=/api/reviews" \
  --data "strip_path=true"

# Create the Frontend Service (route through Istio)
curl -X POST http://209.38.57.91:8001/services \
  --data "name=frontend-service" \
  --data "url=${ISTIO_GATEWAY}/"

# Create the Frontend Route
curl -X POST http://209.38.57.91:8001/services/frontend-service/routes \
  --data "name=frontend-service" \
  --data "paths[]=/" \
  --data "strip_path=true"

echo "Kong API Gateway configuration completed successfully."