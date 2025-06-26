#!/bin/bash

# Kong API Gateway Setup Script for Laravel Monolithic App with Istio Integration
# This script configures Kong to route traffic to Laravel app through Istio service mesh

# Kong Admin API endpoint (update with your Kong admin URL)
KONG_ADMIN_URL="http://146.190.203.203:8001"

# Istio Ingress Gateway endpoint (internal cluster access)
ISTIO_GATEWAY="http://istio-ingressgateway.istio-system.svc.cluster.local:80"

echo "Configuring Kong for Laravel Monolithic App through Istio..."

# Remove old microservices (if they exist)
echo "Cleaning up old microservices..."
curl -X DELETE ${KONG_ADMIN_URL}/services/category-service || true
curl -X DELETE ${KONG_ADMIN_URL}/services/product-service || true
curl -X DELETE ${KONG_ADMIN_URL}/services/review-service || true
curl -X DELETE ${KONG_ADMIN_URL}/services/frontend-service || true

# Create Laravel App Service
echo "Creating Laravel App service..."
curl -X POST ${KONG_ADMIN_URL}/services \
  --data "name=laravel-app" \
  --data "url=${ISTIO_GATEWAY}"

# Create routes for Laravel API endpoints
echo "Creating Laravel API routes..."

# Categories API route
curl -X POST ${KONG_ADMIN_URL}/services/laravel-app/routes \
  --data "name=categories-route" \
  --data "paths[]=/api/categories" \
  --data "strip_path=false" \
  --data "preserve_host=false"

# Products API route
curl -X POST ${KONG_ADMIN_URL}/services/laravel-app/routes \
  --data "name=products-route" \
  --data "paths[]=/api/products" \
  --data "strip_path=false" \
  --data "preserve_host=false"

# Reviews API route
curl -X POST ${KONG_ADMIN_URL}/services/laravel-app/routes \
  --data "name=reviews-route" \
  --data "paths[]=/api/reviews" \
  --data "strip_path=false" \
  --data "preserve_host=false"

# General API route (catch-all for other API endpoints)
curl -X POST ${KONG_ADMIN_URL}/services/laravel-app/routes \
  --data "name=api-route" \
  --data "paths[]=/api" \
  --data "strip_path=false" \
  --data "preserve_host=false"

# Frontend route (catch-all for non-API requests)
curl -X POST ${KONG_ADMIN_URL}/services/laravel-app/routes \
  --data "name=frontend-route" \
  --data "paths[]=/" \
  --data "strip_path=false" \
  --data "preserve_host=false"

echo ""
echo "Kong configuration completed for Laravel Monolithic App!"
echo ""
echo "Traffic flow: External -> Kong Gateway -> Istio Gateway -> Laravel App with Istio sidecar"
echo ""
echo "Test the setup:"
echo "curl http://YOUR_KONG_PROXY_IP:8000/api/categories"
echo "curl http://YOUR_KONG_PROXY_IP:8000/api/products" 
echo "curl http://YOUR_KONG_PROXY_IP:8000/api/reviews"
echo ""
echo "Check Kong services:"
echo "curl ${KONG_ADMIN_URL}/services | jq '.data[] | {name: .name, host: .host, path: .path}'"