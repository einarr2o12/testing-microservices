# Microservices Architecture with Vue.js Frontend

## Overview

This repository contains a microservices-based application with a Vue.js frontend and multiple backend services deployed in a Kubernetes environment. The architecture focuses on a modular design where services can be developed, deployed, and scaled independently.

![Architecture Diagram](/image-01.png)

## Excelidraw Link

[Architecture Diagram](https://link.excalidraw.com/readonly/g5iAivNBVTKzOqsIadPd

## Architecture Components

### Frontend

- **Vue.js Application**: Single-page application that interacts with backend services through API gateways
- **Load Balancer/API Gateway**: Routes requests to appropriate services

### Backend Services

#### Category Service

The Category Service handles all category-related operations:

- **API Endpoints**: RESTful endpoints for CRUD operations on categories
- **Data Structure**: Categories with attributes like name, description, and relationships
- **Database**: Persistent volume for storing category data
- **Kubernetes Deployment**: Containerized service with defined replicas for high availability

#### Product Service

The Product Service manages product-related operations:

- **API Endpoints**: RESTful endpoints for CRUD operations on products
- **Data Structure**: Products with attributes including name, price, inventory status, and category relationships
- **Database**: Persistent volume for storing product data
- **Kubernetes Deployment**: Containerized service with defined replicas for high availability

### Infrastructure

- **Ingress Controller**: Manages external access to services
- **Service Discovery**: Kubernetes services for internal communication
- **Persistent Storage**: Volume claims for database persistence

## Deployment

The application is deployed on Kubernetes with the following configuration:

1. Each service is deployed as a separate pod with its own configuration
2. Services communicate via internal Kubernetes networking
3. Ingress routes external traffic to appropriate services
4. Persistent volumes ensure data durability

## Service Communication

- **Internal Communication**: Services communicate directly through Kubernetes service discovery
- **External Communication**: External requests are routed through the Ingress controller to appropriate services

## Development

### Prerequisites

- Docker
- Kubernetes cluster (local or cloud-based)
- Node.js and npm
- Vue.js CLI

### Setup Instructions

1. Clone the repository
   ```bash
   git clone https://github.com/einarr2o12/testing-microservices
   cd your-repo
   ```

2. Build and deploy services
   ```bash
   # Build and push Docker images for each services
   docker buildx build --platform linux/amd64 -t your-docker-hub-url .
   
   # Deploy to Kubernetes for each services.
   kubectl apply -f .
   ```

3. Access the application
   ```
   # Get the Ingress IP
   kubectl get ingress -n namespace
   ```

4. Access the Vue.js frontend
   ```
   # Get all in namespace
   kubectl get all -n namespace -o wide
   ```

5. coming soon...

## Load Testing
Coming soon...

## Configuration
Configuration for each services is managed through environment variables and Kubernetes ConfigMaps.

## Service Details

### Category Service

The Category Service provides a RESTful API for managing product categories with the following endpoints:

- `GET /categories` - List all categories
- `GET /categories/:id` - Get a specific category
- `POST /categories` - Create a new category
- `PUT /categories/:id` - Update a category
- `DELETE /categories/:id` - Delete a category

Configuration is managed through environment variables and Kubernetes ConfigMaps.

### Product Service

The Product Service provides a RESTful API for managing products with the following endpoints:

- `GET /products` - List all products
- `GET /products/:id` - Get a specific product
- `POST /products` - Create a new product
- `PUT /products/:id` - Update a product
- `DELETE /products/:id` - Delete a product
- `GET /products/category/:categoryId` - Get products by category

Products are linked to categories through category IDs, creating a relationship between the two services.

## API Documentation

Coming soon...

## Scaling and High Availability

Services are configured for horizontal scaling with:

- Multiple replicas for each service
- Load balancing through Kubernetes services
- Readiness and liveness probes for health monitoring

## Monitoring

The architecture includes monitoring through:

Coming soon...

## Troubleshooting

Common issues and solutions:

1. **Service connectivity issues**: Check service DNS names and port configurations
2. **Database connection failures**: Verify persistent volume claims are correctly bound
3. **Frontend not loading**: Check ingress configuration and frontend service status

## Future Improvements

Planned enhancements for the architecture:

1. Add logging and monitoring for better observability
2. Add kong gateway for API management
3. Add request and response logging for each services
4. Implement CI/CD pipeline for automated deployment
5. Add custom kong plugins for API gateway
6. coming soon...

## Contributing

Coming soon...

## License

coming soon...
