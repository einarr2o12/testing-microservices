#!/bin/bash

echo "Deploying Kong with logging configuration..."
helm upgrade --install kong kong/kong -f ../kong/values.yaml -n kong --create-namespace

echo "Deploying OpenTelemetry Collector..."
helm repo add open-telemetry https://open-telemetry.github.io/opentelemetry-helm-charts
helm upgrade --install opentelemetry-collector open-telemetry/opentelemetry-collector -f otlp-collector.yaml -n observability --create-namespace

echo "Deploying OpenSearch..."
helm repo add opensearch https://opensearch-project.github.io/helm-charts/
helm upgrade --install opensearch opensearch/opensearch -f opensearch-values.yaml -n observability --create-namespace

echo "Waiting for OpenSearch to be ready..."
kubectl wait --for=condition=ready pod -l app=opensearch-cluster-master -n observability --timeout=300s

echo "Deploying OpenSearch Dashboard..."
helm upgrade --install opensearch-dashboards opensearch/opensearch-dashboards -f opensearch-dashboard.yaml -n observability

echo "Waiting for all components to be ready..."
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=opentelemetry-collector -n observability --timeout=300s
kubectl wait --for=condition=ready pod -l app.kubernetes.io/name=opensearch-dashboards -n observability --timeout=300s

echo "Getting service endpoints..."
echo "Kong Gateway: $(kubectl get svc kong-kong-proxy -n kong -o jsonpath='{.status.loadBalancer.ingress[0].ip}'):8000"
echo "Kong Admin: $(kubectl get svc kong-kong-admin -n kong -o jsonpath='{.status.loadBalancer.ingress[0].ip}'):8001"
echo "OpenSearch Dashboard: $(kubectl get svc opensearch-dashboards -n observability -o jsonpath='{.status.loadBalancer.ingress[0].ip}'):5601"

echo "Setup complete! To test:"
echo "1. Make some requests through Kong Gateway"
echo "2. Check logs in OpenSearch Dashboard at the URL above"
echo "3. Create index patterns for 'otel-logs*' in OpenSearch Dashboard"