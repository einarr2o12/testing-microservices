# Claude Code Session History - Microservices Observability Setup

## Project Overview
**Goal**: Implement zero-code observability for k8s microservices using Kong Gateway + Istio Service Mesh + SigNoz

**Architecture**: Vue.js frontend + Kong Gateway + 4 microservices (product, category, review, frontend) + SigNoz observability stack

## Session History & Progress

### Initial State Assessment
- **Existing Setup**: Kong Gateway, 4 services, SigNoz, ClickHouse, OpenTelemetry collector
- **Problem**: No traces appearing in SigNoz despite infrastructure being deployed
- **User Goal**: Zero-code observability without modifying application source code

### Phase 1: Explored Kong OpenTelemetry Plugin (❌ Failed)
- Attempted Kong's OpenTelemetry plugin but Kong OSS limitations
- User feedback: "we don't need prometheus" - focus only on SigNoz

### Phase 2: Istio Service Mesh Implementation (✅ Success)

#### Step 1: Istio Installation & Configuration
```bash
# Istio installation with SigNoz integration
istioctl install -f /Users/einarr/microservices/demo/k8s-microservices/kubernetes/observability/istio-only.yaml -y
kubectl apply -f /Users/einarr/microservices/demo/k8s-microservices/kubernetes/observability/telemetry-only.yaml
```

**Key Files Created:**
- `kubernetes/observability/istio-only.yaml` - Istio installation config with OTEL provider
- `kubernetes/observability/telemetry-only.yaml` - Telemetry configuration for tracing
- `kubernetes/observability/telemetry-workload.yaml` - Per-namespace telemetry config

#### Step 2: Istio Configuration Details
**OpenTelemetry Provider Configuration:**
```yaml
extensionProviders:
- name: otel
  opentelemetry:
    service: signoz-otel-collector.observability.svc.cluster.local
    port: 4318  # HTTP OTLP (not 4317 gRPC)
    http:
      path: "/v1/traces"
      timeout: 5s
defaultProviders:
  tracing:
  - otel
```

**Critical Learning**: Used HTTP OTLP (port 4318) not gRPC (port 4317) based on user feedback: "grpc why grpc? there is no grpc use case"

#### Step 3: Sidecar Injection Success
All services now show **2/2 containers** (app + istio-proxy sidecar):
```bash
kubectl get pods -A | grep -E "(2/2|product|category|review|frontend)"
```

### Phase 3: Kong-Istio Integration (✅ Success)

#### Problem Discovery
Initial issue: Kong was routing directly to services, bypassing Istio sidecars completely.

#### Solution: Kong → Istio Gateway Routing
**Files Created:**
- `kubernetes/observability/istio-gateway.yaml` - Istio Gateway + VirtualService configuration
- `kubernetes/kong/setup-istio.sh` - Script to update Kong services

**Kong Service Updates:**
```bash
# Updated Kong services to route through Istio
ISTIO_GATEWAY="http://istio-ingressgateway.istio-system.svc.cluster.local:80"
curl -X PATCH http://146.190.203.203:8001/services/product-service --data "url=${ISTIO_GATEWAY}/api/products"
```

**Critical Fix**: Updated Kong routes to use `strip_path=true` to prevent path duplication.

### Phase 4: Service ConfigMap Updates (✅ Success)

#### Problem: Inter-service Communication Bypassing Mesh
Services were calling each other directly via Kubernetes DNS, bypassing Istio sidecars.

#### Solution: Route Inter-service Calls Through Istio Gateway
**Updated ConfigMaps:**
- `kubernetes/product/configMap.yaml`: `CATEGORY_SERVICE_URL` → Istio gateway URL
- `kubernetes/review/configMap.yaml`: `PRODUCT_SERVICE_URL` → Istio gateway URL

**User Insight**: "If we need to add some header, we should do in kong api gateway layer" - confirmed Kong as the right place for tracing headers, not source code modification.

### Phase 5: Kong Plugin Issue & Resolution (✅ Fixed)

#### Problem: Kong Request-Transformer Plugin Error
```
[error] [request-transformer] [string "TMP"]:1: ')' expected near 'rand'
```
Kong returning 500 errors due to malformed header syntax in request-transformer plugin.

#### Solution: Removed Broken Plugin
```bash
curl -X DELETE http://146.190.203.203:8001/plugins/13197aeb-5c61-4d44-8732-150114b6097b
```

## Current Architecture Status ✅

### Traffic Flow (Working)
```
External Request → Kong Gateway (157.230.195.52:8000) 
    ↓
Istio Ingress Gateway (istio-ingressgateway.istio-system.svc.cluster.local)
    ↓
VirtualService Routing (/api/products, /api/categories, /api/reviews, /)
    ↓
Services with Istio Sidecars (2/2 containers)
    ↓
Inter-service calls also route through Istio Gateway (for tracing)
```

### Infrastructure Components ✅
- **Kong Gateway**: External LoadBalancer (157.230.195.52:8000)
- **Istio Service Mesh**: All services have sidecars, traffic flows through mesh
- **SigNoz**: Observability platform ready to receive traces
- **ClickHouse**: Database healthy, 0 traces currently (infrastructure ready)
- **OTEL Collector**: Configured and running (signoz-otel-collector.observability.svc)

### Service Status ✅
All services responding correctly:
- Products: `curl http://157.230.195.52:8000/api/products` ✅
- Categories: `curl http://157.230.195.52:8000/api/categories` ✅  
- Reviews: `curl http://157.230.195.52:8000/api/reviews` ✅
- Frontend: `curl http://157.230.195.52:8000/` ✅

## Key Credentials & Endpoints

### Kong Gateway
- **Proxy**: http://157.230.195.52:8000
- **Admin API**: http://146.190.203.203:8001
- **Manager**: http://159.89.211.166:8002

### ArgoCD
- **URL**: http://167.99.29.166
- **Username**: admin
- **Password**: 4pgzVt2GElGzHFkb

### SigNoz & ClickHouse
- **SigNoz**: Check traces via kubectl commands
- **ClickHouse**: `kubectl exec -n observability chi-signoz-clickhouse-cluster-0-0-0 -c clickhouse -- clickhouse-client -q "SELECT count() FROM signoz_traces.signoz_index_v2"`

## Next Steps / Remaining Work

### Tracing Headers Implementation
**Status**: Infrastructure ready, need to add tracing headers at Kong Gateway layer

**Approach**: Add Kong plugin to inject Istio tracing headers:
- `x-request-id` - Unique request identifier  
- `x-b3-traceid` - Trace ID for entire request flow
- `x-b3-spanid` - Span ID for particular request
- `x-b3-sampled` - Whether trace should be sampled (1 = yes)

**Implementation Location**: Kong API Gateway (not source code modification)

### Verification Commands
```bash
# Check if services have sidecars
kubectl get pods -A | grep -E "(2/2|product|category|review)"

# Test API access
curl http://157.230.195.52:8000/api/products

# Check trace count in ClickHouse  
kubectl exec -n observability chi-signoz-clickhouse-cluster-0-0-0 -c clickhouse -- clickhouse-client -q "SELECT count() FROM signoz_traces.signoz_index_v2"

# Check Kong services configuration
curl http://146.190.203.203:8001/services | jq '.data[] | {name: .name, host: .host, path: .path}'

# Generate traffic for testing
curl http://157.230.195.52:8000/api/products && curl http://157.230.195.52:8000/api/categories && curl http://157.230.195.52:8000/api/reviews
```

## Key Learnings & User Feedback

1. **"we don't need prometheus"** - Focus only on SigNoz integration
2. **"we shouldn't do inject istio sidecars on kong"** - Kong external, services internal mesh
3. **"grpc why grpc? there is no grpc use case"** - Use HTTP OTLP (port 4318) not gRPC (4317)
4. **"we didn't had jaeger/zipkin. Need to focus only we had SigNoz"** - No Jaeger/Zipkin formats
5. **"I don't want to touch the business code"** - Zero-code observability approach
6. **"If we need to add some header, we should do in kong api gateway layer"** - Kong for headers, not app code

## Architecture Success ✅

The zero-code observability infrastructure is successfully implemented:
- ✅ Kong Gateway routing through Istio
- ✅ All services have Istio sidecars  
- ✅ Traffic flows through service mesh
- ✅ SigNoz + ClickHouse ready for traces
- ✅ ConfigMaps updated for mesh communication
- ✅ No business code modifications required

**Current Status**: Infrastructure ready for distributed tracing. Next step is implementing Kong tracing headers to initiate trace context.