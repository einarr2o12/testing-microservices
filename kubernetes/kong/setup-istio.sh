#!/bin/bash

# Kong API Gateway Setup Script with Istio Integration
# This script configures Kong to route traffic through Istio service mesh

# Kong Admin API endpoint
KONG_ADMIN_URL="http://146.190.203.203:8001"

# Istio Ingress Gateway endpoint (internal cluster access)
ISTIO_GATEWAY="http://istio-ingressgateway.istio-system.svc.cluster.local:80"

echo "Updating Kong services to route through Istio..."

# Update Category Service to route through Istio
curl -X PATCH http://146.190.203.203:8001/services/category-service \
  --data "url=${ISTIO_GATEWAY}/api/categories"

# Update Product Service to route through Istio  
curl -X PATCH http://146.190.203.203:8001/services/product-service \
  --data "url=${ISTIO_GATEWAY}/api/products"

# Update Review Service to route through Istio
curl -X PATCH http://146.190.203.203:8001/services/review-service \
  --data "url=${ISTIO_GATEWAY}/api/reviews"

# Update Frontend Service to route through Istio
curl -X PATCH http://146.190.203.203:8001/services/frontend-service \
  --data "url=${ISTIO_GATEWAY}/"

echo "Kong services updated to route through Istio ingress gateway."
echo "Traffic flow: External -> Kong -> Istio Gateway -> Services with sidecars"