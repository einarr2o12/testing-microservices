# Cloud-Native Microservices Application

This project demonstrates a cloud-native microservices architecture with three distinct services deployed on Kubernetes. Each service uses a different programming language and database technology to showcase polyglot programming and persistence. Just only for educational purpose. That's why I add some creditrials in the code. 
### Never put creditrials in the code and never push to the github.

## Architecture Overview

![Microservices Architecture](architecture-diagram.svg)

The application consists of three microservices:

1. **Category Service** (Node.js + MongoDB)
   - Manages product categories
   - Uses document-based storage
   - Exposes RESTful API endpoints
   - Implemented with Express.js

2. **Product Service** (PHP + MySQL)
   - Manages product information
   - Uses relational data storage
   - Communicates with Category service
   - Exposes RESTful API endpoints

3. **Review Service** (Python + PostgreSQL)
   - Manages product reviews
   - Uses PostgreSQL for advanced relational features
   - Communicates with Product service
   - Exposes RESTful API endpoints with Flask

### Service Relationships

- Category Service has a one-to-many relationship with Product Service
- Product Service has a one-to-many relationship with Review Service
- Direct communication between Category and Review Services is blocked by network policies

## Technology Stack

### Programming Languages
- Node.js for Category Service
- PHP for Product Service
- Python for Review Service

### Databases (Polyglot Persistence)
- MongoDB for document-based storage (Category Service)
- MySQL for relational data management (Product Service)
- PostgreSQL for advanced relational features (Review Service)

### Infrastructure
- Kubernetes for orchestration
- StatefulSets for databases
- Deployments for stateless services
- Network Policies for enforcing communication rules
- Kubernetes Services for service discovery

## Project Structure

```
├── category-service/
│   ├── Dockerfile
│   ├── package.json
│   └── server.js
├── product-service/
│   ├── Dockerfile
│   ├── composer.json
│   └── index.php
├── review-service/
│   ├── Dockerfile
│   ├── app.py
│   ├── models.py
│   └── requirements.txt
└── kubernetes/
    ├── category/
    │   ├── deployment.yaml
    │   ├── service.yaml
    │   └── network-policy.yaml
    ├── product/
    │   ├── deployment.yaml
    │   ├── service.yaml
    │   └── network-policy.yaml
    ├── review/
    │   ├── deployment.yaml
    │   ├── service.yaml
    │   └── network-policy.yaml
    └── databases/
        ├── mongodb-statefulset.yaml
        ├── mysql-statefulset.yaml
        └── postgres-statefulset.yaml
```

## API Endpoints

### Category Service (Node.js)
- `GET /api/categories` - Get all categories
- `GET /api/categories/:id` - Get a specific category
- `POST /api/categories` - Create a new category
- `PUT /api/categories/:id` - Update a category
- `DELETE /api/categories/:id` - Delete a category

### Product Service (PHP)
- `GET /api/products` - Get all products
- `GET /api/products/:id` - Get a specific product
- `POST /api/products` - Create a new product
- `PUT /api/products/:id` - Update a product
- `DELETE /api/products/:id` - Delete a product
- `GET /api/products/category/:categoryId` - Get products by category

### Review Service (Python)
- `GET /api/reviews` - Get all reviews
- `GET /api/reviews/:id` - Get a specific review
- `POST /api/reviews` - Create a new review
- `PUT /api/reviews/:id` - Update a review
- `DELETE /api/reviews/:id` - Delete a review
- `GET /api/products/:productId/reviews` - Get reviews for a product

## Cloud-Native Features

1. **Microservices Architecture**
   - Each service is independently deployable
   - Services communicate via REST APIs
   - Clear separation of concerns

2. **Polyglot Programming**
   - Node.js, PHP, and Python showcase different programming models
   - Each language is chosen for its strengths

3. **Polyglot Persistence**
   - MongoDB for document storage
   - MySQL for traditional relational data
   - PostgreSQL for advanced relational features

4. **Kubernetes Deployment**
   - Stateless services use Deployments
   - Stateful databases use StatefulSets
   - Services for internal DNS resolution
   - Network Policies to enforce access rules

5. **Resilience Patterns**
   - Health checks for all services
   - Resource limits and requests
   - Replica configuration for high availability

## Deployment Instructions

### Prerequisites
- Kubernetes cluster (minikube, kind, or cloud provider)
- kubectl configured to access your cluster
- Docker for building images

### Building the Images

```bash
# Build Category Service
cd category-service
docker build -t category-service:latest .

# Build Product Service
cd ../product-service
docker build -t product-service:latest .

# Build Review Service
cd ../review-service
docker build -t review-service:latest .
```

### Deploying to Kubernetes

1. First, deploy the databases:

```bash
kubectl apply -f kubernetes/databases/mongodb-statefulset.yaml
kubectl apply -f kubernetes/databases/mysql-statefulset.yaml
kubectl apply -f kubernetes/databases/postgres-statefulset.yaml
```

2. Then, deploy the services:

```bash
# Category Service
kubectl apply -f kubernetes/category/deployment.yaml
kubectl apply -f kubernetes/category/service.yaml

# Product Service
kubectl apply -f kubernetes/product/deployment.yaml
kubectl apply -f kubernetes/product/service.yaml

# Review Service
kubectl apply -f kubernetes/review/deployment.yaml
kubectl apply -f kubernetes/review/service.yaml
```

3. Finally, apply the network policies:

```bash
kubectl apply -f kubernetes/category/network-policy.yaml
kubectl apply -f kubernetes/product/network-policy.yaml
kubectl apply -f kubernetes/review/network-policy.yaml
```

### Verifying the Deployment

Check if all pods are running:

```bash
kubectl get pods
```

Check the services:

```bash
kubectl get services
```

## Testing the Application

You can test the APIs using port-forwarding:

```bash
# Forward Category Service
kubectl port-forward svc/category-service 3000:3000

# Forward Product Service
kubectl port-forward svc/product-service 8080:80

# Forward Review Service
kubectl port-forward svc/review-service 5000:5000
```

Then, use tools like curl or Postman to interact with the APIs.

## Educational Goals

This project demonstrates:
- Cloud-native principles
- Polyglot programming
- Microservice communication patterns
- Database integration strategies
- Kubernetes deployment techniques
- Network security with Kubernetes