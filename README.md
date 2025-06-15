# Microservices Architecture with Complete Observability

## Overview

This repository contains a production-ready microservices application with Vue.js frontend, Kong API Gateway, and comprehensive observability stack. The architecture demonstrates modern cloud-native patterns with monitoring, tracing, and logging without modifying business code.

![Architecture Diagram](/image-01.png)

## Excalidraw Link

[Architecture Diagram](https://link.excalidraw.com/readonly/g5iAivNBVTKzOqsIadPd)

## 🏗️ Architecture Components

### Frontend
- **Vue.js Application** - Single-page application with modern UI
- **Kong Gateway Integration** - All API calls routed through Kong

### Backend Services
- **Category Service** - Product category management
- **Product Service** - Product catalog and inventory
- **Review Service** - Customer reviews and ratings
- **Frontend Service** - Vue.js application serving

### API Gateway
- **Kong Gateway** - API management, routing, and plugin ecosystem
- **PostgreSQL** - Kong configuration database
- **Per-Service Plugins** - Prometheus metrics and OpenTelemetry tracing

### Observability Stack
- **OpenTelemetry Collector** - Metrics and traces collection
- **Prometheus** - Metrics storage and querying
- **Jaeger** - Distributed tracing and visualization
- **Grafana** - Dashboards and metrics visualization
- **OpenSearch** - Log aggregation and search

### Infrastructure
- **DigitalOcean Kubernetes** - Managed Kubernetes cluster
- **Helm Charts** - Package management for observability components
- **LoadBalancer Services** - External access to Kong and services

## 🔄 Data Flow Architecture

### Request Flow
```
Client → Kong Gateway → Microservice → Database
```

### Observability Flow
```
Application Metrics: Kong Plugins → Prometheus → Grafana
Distributed Tracing: Kong Plugins → OTLP Collector → Jaeger  
Infrastructure Metrics: OTLP DaemonSet → Prometheus → Grafana
Logs: Services → OTLP Collector → OpenSearch
```

## 🚀 Quick Start

### Prerequisites
- Docker and Kubernetes cluster
- Helm 3.x
- kubectl configured for your cluster

### 1. Deploy Core Services
```bash
# Deploy microservices
kubectl apply -f kubernetes/category/
kubectl apply -f kubernetes/product/
kubectl apply -f kubernetes/review/
kubectl apply -f kubernetes/frontend/
```

### 2. Deploy Kong API Gateway
```bash
# Add Kong Helm repository
helm repo add kong https://charts.konghq.com
helm repo update

# Deploy Kong
helm install kong kong/kong -f kubernetes/kong/values.yaml -n kong --create-namespace
```

### 3. Deploy Observability Stack
```bash
# Deploy OpenTelemetry Collectors
helm install otel-collector-app open-telemetry/opentelemetry-collector -f kubernetes/observability/otlp-collector.yaml -n observability --create-namespace
helm install otel-collector-infra open-telemetry/opentelemetry-collector -f kubernetes/observability/otlp-collector-daemonset.yaml -n observability

# Deploy Jaeger
kubectl apply -f kubernetes/observability/jaeger-simple.yaml

# Deploy Prometheus
helm install prometheus prometheus-community/prometheus -f kubernetes/observability/prometheus-helm.yaml -n observability

# Deploy Grafana  
helm install grafana grafana/grafana -f kubernetes/observability/grafana-helm.yaml -n observability

# Deploy OpenSearch
helm install opensearch opensearch/opensearch -f kubernetes/observability/opensearch-values.yaml -n observability
```

### 4. Configure Kong Plugins
```bash
# Enable Prometheus plugin per service
curl -X POST http://KONG_ADMIN_URL:8001/services/category-service/plugins \
  --data "name=prometheus" \
  --data "config.per_consumer=false"

# Enable OpenTelemetry plugin per service  
curl -X POST http://KONG_ADMIN_URL:8001/services/category-service/plugins \
  -H "Content-Type: application/json" \
  -d '{
    "name": "opentelemetry",
    "config": {
      "endpoint": "http://otel-collector-app-opentelemetry-collector.observability.svc.cluster.local:4318/v1/traces",
      "resource_attributes": {
        "service.name": "category-service",
        "service.version": "1.0"
      }
    }
  }'
```

## 📊 Observability Access

### Dashboards & UIs
```bash
# Grafana (Metrics & Dashboards)
kubectl port-forward svc/grafana 3000:3000 -n observability
# Access: http://localhost:3000 (admin/grafana-admin)

# Jaeger (Distributed Tracing)
kubectl port-forward svc/jaeger-query 16686:16686 -n observability  
# Access: http://localhost:16686

# Prometheus (Metrics Storage)
kubectl port-forward svc/prometheus-server 9090:9090 -n observability
# Access: http://localhost:9090

# OpenSearch Dashboards (Logs)
kubectl port-forward svc/opensearch-dashboards 5601:5601 -n observability
# Access: http://localhost:5601
```

### Application Access
```bash
# Kong Admin API
kubectl get svc kong-kong-admin -n kong
# Access: http://EXTERNAL_IP:8001

# Kong Proxy (Application Gateway)  
kubectl get svc kong-kong-proxy -n kong
# Access: http://EXTERNAL_IP:8000

# Frontend Application
# Access: http://KONG_PROXY_IP:8000/
```

## 🔍 Monitoring Features

### Application Metrics (Kong Plugins)
- **Request Rate** - Requests per second by service
- **Response Status Codes** - 2xx, 4xx, 5xx distribution  
- **Latency Metrics** - P95, P99 response times
- **Error Rates** - Error percentage by service
- **Bandwidth Usage** - Ingress/egress by service

### Infrastructure Metrics (OTLP DaemonSet)
- **CPU & Memory** - Node and pod resource usage
- **Disk I/O** - Read/write operations and utilization
- **Network I/O** - Packet and byte throughput
- **Container Metrics** - Per-container resource consumption

### Distributed Tracing (OpenTelemetry)
- **Request Flows** - End-to-end request traces
- **Service Dependencies** - Service interaction mapping
- **Performance Analysis** - Bottleneck identification
- **Error Tracking** - Failed request investigation

## 📈 Sample Grafana Queries

### Kong Application Metrics
```promql
# Request Rate by Service
rate(kong_http_requests_total[5m])

# 95th Percentile Latency
histogram_quantile(0.95, sum(rate(kong_latency_bucket[5m])) by (le, service))

# Error Rate Percentage
sum(rate(kong_http_requests_total{code=~"5.."}[5m])) / sum(rate(kong_http_requests_total[5m])) * 100

# Service Request Volume
sum by (service) (kong_http_requests_total)
```

### Infrastructure Metrics
```promql
# CPU Usage by Node
100 - (avg(irate(system_cpu_time_seconds_total{state="idle"}[5m])) * 100)

# Memory Usage
system_memory_utilization_ratio

# Container CPU Usage
rate(container_cpu_usage_seconds_total{container!="POD"}[5m]) * 100
```

## 🛠️ Development

### API Endpoints

#### Category Service
- `GET /category/` - List categories
- `POST /category/` - Create category
- `GET /category/{id}` - Get category
- `PUT /category/{id}` - Update category
- `DELETE /category/{id}` - Delete category

#### Product Service  
- `GET /product/` - List products
- `POST /product/` - Create product
- `GET /product/{id}` - Get product
- `PUT /product/{id}` - Update product
- `DELETE /product/{id}` - Delete product

#### Review Service
- `GET /review/` - List reviews
- `POST /review/` - Create review
- `GET /review/{id}` - Get review
- `PUT /review/{id}` - Update review
- `DELETE /review/{id}` - Delete review

### Service Communication
All services communicate through Kong Gateway with automatic:
- **Load balancing** across service replicas
- **Metrics collection** via Prometheus plugin
- **Trace generation** via OpenTelemetry plugin
- **Request/response logging** for debugging

## 🔧 Configuration Files

### Core Kubernetes Manifests
- `kubernetes/category/` - Category service deployment
- `kubernetes/product/` - Product service deployment  
- `kubernetes/review/` - Review service deployment
- `kubernetes/frontend/` - Frontend service deployment

### Kong Configuration
- `kubernetes/kong/values.yaml` - Kong Helm values
- Kong services and routes configured via Admin API

### Observability Configuration
- `kubernetes/observability/otlp-collector.yaml` - Application telemetry collector
- `kubernetes/observability/otlp-collector-daemonset.yaml` - Infrastructure metrics collector
- `kubernetes/observability/prometheus-helm.yaml` - Prometheus configuration with Kong scraping
- `kubernetes/observability/grafana-helm.yaml` - Grafana with Prometheus data source
- `kubernetes/observability/jaeger-simple.yaml` - Jaeger all-in-one deployment
- `kubernetes/observability/opensearch-values.yaml` - OpenSearch logging stack

## 📋 Operational Commands

### Check Service Health
```bash
# All observability services
kubectl get all -n observability

# Kong services
kubectl get all -n kong

# Application services  
kubectl get all -n default
```

### Kong Plugin Management
```bash
# List plugins for a service
curl -s http://KONG_ADMIN:8001/services/category-service/plugins

# Check Kong metrics endpoint
curl -s http://KONG_ADMIN:8001/metrics | head -20

# View Kong configuration
curl -s http://KONG_ADMIN:8001/services
curl -s http://KONG_ADMIN:8001/routes
```

### Troubleshooting
```bash
# Check OTLP collector logs
kubectl logs -l app.kubernetes.io/name=opentelemetry-collector -n observability

# Check Prometheus targets
curl http://localhost:9090/api/v1/targets

# Verify Jaeger is receiving traces
curl http://localhost:16686/api/services

# Test service connectivity
kubectl exec -it POD_NAME -- curl http://service-name:port/health
```

## 🎯 Key Benefits

### Zero-Code Observability
- **No application changes required** - All observability via Kong plugins
- **Automatic metrics collection** - Request/response metrics for all services
- **Distributed tracing** - End-to-end request visibility
- **Centralized logging** - All service logs in one place

### Production-Ready Features
- **High availability** - Multiple replicas with load balancing
- **Resource optimization** - Configured for small cluster environments
- **Security** - TLS termination at Kong Gateway
- **Scalability** - Horizontal pod autoscaling ready

### Operational Excellence
- **Real-time monitoring** - Live dashboards and alerts
- **Performance optimization** - Latency and error rate tracking
- **Capacity planning** - Resource utilization metrics
- **Debugging** - Trace analysis and log correlation

## 🔮 Future Enhancements

- [ ] **Alerting** - Prometheus AlertManager with Slack/email notifications
- [ ] **API Documentation** - OpenAPI/Swagger integration with Kong
- [ ] **Security Policies** - Rate limiting, authentication, and authorization
- [ ] **CI/CD Pipeline** - GitOps with ArgoCD or Flux
- [ ] **Load Testing** - Automated performance testing with k6
- [ ] **Multi-environment** - Development, staging, production separation
- [ ] **Service Mesh** - Istio integration for advanced traffic management
- [ ] **Cost Optimization** - Resource optimization and cluster autoscaling

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

For questions and support:
- Create an issue in this repository
- Check the troubleshooting section above
- Review Kubernetes and Kong documentation