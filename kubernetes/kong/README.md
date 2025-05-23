# Kong API Gateway Setup

![Kong Manager](/kubernetes/kong/kong-manager.png)

## Overview

This directory contains the configuration files and setup scripts for Kong API Gateway, which serves as the entry point for all microservices in our application. Kong routes external requests to the appropriate internal services based on path-based routing rules.

## Architecture

Kong API Gateway is deployed as a Kubernetes service with the following components:

- **Kong Proxy**: Handles all incoming traffic (port 8000)
- **Kong Admin API**: Used for configuration (port 8001)
- **Kong Manager**: Web UI for administration (port 8002)
- **PostgreSQL Database**: Stores Kong's configuration

## Prerequisites

- Kubernetes cluster up and running
- Helm installed
- `kubectl` configured to communicate with your cluster

## Installation

### 1. Install Kong using Helm

```bash
# Add the Kong Helm repository
helm repo add kong https://charts.konghq.com
helm repo update

# Install Kong in the kong namespace
helm upgrade --install kong kong/kong -n kong --create-namespace -f values.yaml
```

The `values.yaml` file in this directory contains the custom configuration for our Kong installation, including:

- PostgreSQL database settings
- Service configurations
- Admin API and Manager UI settings
- OpenTelemetry integration

### 2. Verify the Installation

```bash
# Check if Kong pods are running
kubectl get pods -n kong

# Get the external IP for Kong services
kubectl get svc -n kong
```

## Configuration

### Service and Route Setup

After Kong is installed, run the `setup.sh` script to configure the services and routes:

```bash
chmod +x setup.sh
./setup.sh
```

This script creates the following configurations in Kong:

1. **Category Service**:
   - Internal URL: `http://category-service.category-service.svc.cluster.local:3000`
   - External path: `/category`

2. **Product Service**:
   - Internal URL: `http://product-service.product-service.svc.cluster.local:80`
   - External path: `/product`

3. **Review Service**:
   - Internal URL: `http://review-service.review-service.svc.cluster.local:5000`
   - External path: `/review`

4. **Frontend Service**:
   - Internal URL: `http://frontend-service.frontend.svc.cluster.local:8080`
   - External path: `/` (root path)

### Accessing the Services

Once Kong is configured, you can access the services through the Kong proxy:

- Frontend: `http://<kong-proxy-ip>/`
- Category API: `http://<kong-proxy-ip>/category`
- Product API: `http://<kong-proxy-ip>/product`
- Review API: `http://<kong-proxy-ip>/review`

### Kong Manager

Kong Manager provides a web interface for managing Kong:

- URL: `http://<kong-manager-ip>:8002`

## Advanced Configuration

### SSL/TLS

The `tls.crt` and `tls.key` files in this directory are used for Kong's internal cluster communication. For production environments, you should configure proper SSL/TLS certificates for external endpoints.

### Network Policies

Consider implementing Kubernetes Network Policies to restrict traffic between Kong and the microservices.

### Monitoring

Kong is configured with OpenTelemetry integration, sending telemetry data to the OpenTelemetry collector at `opentelemetry-collector.opentelemetry.svc.cluster.local:4317`.

## Troubleshooting

### Common Issues

1. **Kong Admin API not accessible**:
   - Check if the Admin service is exposed correctly
   - Verify network policies allow access to port 8001

2. **Routes not working**:
   - Verify the service and route configuration using Kong Manager
   - Check if the target services are running and accessible from Kong

3. **Database connection issues**:
   - Verify PostgreSQL is running
   - Check the database credentials in `values.yaml`

### Logs

```bash
# View Kong proxy logs
kubectl logs -f -l app=kong -c proxy -n kong

# View Kong admin logs
kubectl logs -f -l app=kong -c admin -n kong
```

## References

- [Kong Documentation](https://docs.konghq.com/)
- [Kong Helm Chart](https://github.com/Kong/charts/tree/main/charts/kong)
- [Kong Kubernetes Ingress Controller](https://docs.konghq.com/kubernetes-ingress-controller/latest/)