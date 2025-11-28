# Cinch E-Commerce Microservices Platform

A simple e-commerce platform built with microservices architecture using a **monorepo** approach. All services (Catalog, Checkout, Email) and the frontend are organized in a single repository for simplified development, testing, and deployment workflows.

## Deployment Guides

### 🚀 GitHub Actions
- **[.github/ACTIONS_SETUP.md](.github/ACTIONS_SETUP.md)** - Automated CI/CD deployment

---

## Overview

This project demonstrates a complete microservices-based e-commerce system with:
- **Monorepo structure** - All services in one repository with shared infrastructure
- **Event-driven architecture** - Services communicate via Redis pub/sub
- **API Gateway pattern** - Nginx gateway (local) / AWS ALB (production)
- **Independent databases** - Each service has its own MySQL database
- **Containerization** - Docker for local development, ECS for production
- **Infrastructure as Code** - CloudFormation templates for AWS deployment

## Architecture

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│      Nginx API Gateway (Port 80)    │
│  / AWS Application Load Balancer    │
└─────────┬───────────────────────────┘
          │
    ┌─────┴─────┬──────────────┬────────────┐
    │           │              │            │
    ▼           ▼              ▼            ▼
┌────────┐  ┌──────────┐  ┌──────────┐  ┌─────────┐
│Frontend│  │ Catalog  │  │ Checkout │  │  Email  │
│Vue.js  │  │ Service  │  │ Service  │  │ Service │
│  SPA   │  │(Port 3001)│  │(Port 3002)│ │(Worker) │
└────────┘  └────┬─────┘  └─────┬────┘  └────┬────┘
                 │              │            │
            ┌────┴────┐    ┌────┴────┐  ┌───┴────┐
            │  MySQL  │    │  MySQL  │  │ MySQL  │
            │ Catalog │    │Checkout │  │ Email  │
            └─────────┘    └────┬────┘  └────────┘
                                │
                           ┌────┴────┐
                           │  Redis  │
                           │(Pub/Sub)│
                           └─────────┘
```

### Key Features

- **Microservices Architecture**: Independent, scalable services
- **Saga Pattern**: Distributed transaction management with compensating actions
- **Event-Driven Communication**: Redis pub/sub for asynchronous messaging
- **API Gateway**: Centralized routing
- **Container Orchestration**: Docker Compose for easy deployment
- **Security Hardening**: Rate limiting, CSP, security headers, connection limits
- **Health Checks**: Automated health monitoring for all services
- **Database Isolation**: Separate MySQL instances per service

## Services

### 1. Catalog Service (Port 3001)
- **Technology**: PHP 8.3, Laravel 12
- **Database**: MySQL 8.0

**API Endpoints**:
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get single product

### 2. Checkout Service (Port 3002)
- **Technology**: PHP 8.3, Laravel 12
- **Database**: MySQL 8.0
- **Message Broker**: Redis (pub/sub)=

**API Endpoints**:
- `POST /api/orders` - Create new order (initiates Saga)
- `GET /api/orders/{id}` - Get order details

**Saga Flow**:
1. Validate order data
2. Reserve inventory (call Catalog service)
3. Create order record
4. Publish order confirmation event
5. Trigger email notification
6. Handle failures with compensating actions

### 3. Email Service (Background Worker)
- **Technology**: PHP 8.3, Laravel 12, Supervisor
- **Database**: MySQL 8.0
- **Message Broker**: Redis (queue)

### 4. Frontend (Vue.js SPA)
- **Technology**: Vue.js 3, TypeScript, Vite
- **UI Framework**: Tailwind CSS, shadcn-vue
- **State Management**: Pinia
- **Responsibilities**:
  - Product catalog browsing
  - Product detail views
  - Shopping cart management
  - Checkout flow
  - Order confirmation

**Pages**:
- `/` - Product listing
- `/products/:id` - Product detail
- `/cart` - Shopping cart

### 5. Nginx Gateway (Port 80)
- **Technology**: Nginx
- **Responsibilities**:
  - Reverse proxy for all services
  - Route `/api/catalog/*` to Catalog service
  - Route `/api/checkout/*` to Checkout service
  - Route `/` to Frontend

## Prerequisites

- **Docker**: 20.10+
- **Docker Compose**: 2.0+
- **Git**: 2.0+

## Getting Started

### 1. Clone the Repository

```bash
git clone git@github.com:lrencallado/microservices-demo.git
cd microservices-demo
```

### 2. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

The default `.env` file contains development-friendly credentials:

```env
# MySQL
MYSQL_ROOT_PASSWORD=secret
DB_PASSWORD=secret

# Redis (no password for development)
# REDIS_PASSWORD=

# Email
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password

# Application
APP_ENV=local
APP_DEBUG=true

```

### 3. Build and Start Services

```bash
# Build all containers
docker-compose build

# Start all services in detached mode
docker-compose up -d

# View logs (optional)
docker-compose logs -f
```

This will start:
- Nginx Gateway: http://localhost
- Frontend: http://localhost (proxied through Nginx)
- Catalog API: http://localhost/api/catalog
- Checkout API: http://localhost/api/checkout
- 3 MySQL databases (ports 3307, 3308, 3309 for local access)
- Redis (port 6379)
- Email worker (background process)

### 4. Run Migrations and Seed Data

```bash
# Catalog service
docker exec cinch-catalog php artisan migrate --seed

# Checkout service
docker exec cinch-checkout php artisan migrate

# Email service
docker exec cinch-email php artisan migrate
```

### 5. Start Email Listener

```bash
docker exec cinch-email php artisan orders:listen
```

This starts the background worker that listens for order events and sends confirmation emails.

### 6. Access the Application

Open your browser and navigate to:
- **Frontend**: http://localhost
- **API Gateway Health Check**: http://localhost/health

## Testing the Complete Flow

### 1. Browse Products
- Open http://localhost
- View the product catalog (seeded with sample products)

### 2. Add to Cart
- Click on a product to view details
- Add items to cart
- View cart in the top-right corner

### 3. Checkout
- Navigate to `/cart`
- Click "Proceed to Checkout"
- Enter your email address
- Submit order

## API Testing

### Using cURL

```bash
# Get all products
curl http://localhost/api/catalog/products

# Get single product
curl http://localhost/api/catalog/products/1

# Create order
curl -X POST http://localhost/api/checkout/orders \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 2, "quantity": 1}
    ]
  }'

# Get order details
curl http://localhost/api/checkout/orders/1
```

## Project Structure

```
cinch-microservices/
├── infrastructure/
│   ├── scripts/
│   │   └── cloudformation.yml    # AWS CloudFormation template
│   └── parameters/
│       └── cloudformation-parameters.json  # CloudFormation configuration
├── frontend/
│   ├── src/
│   │   ├── components/           # Vue components
│   │   ├── lib/
│   │   │   └── api.ts            # API client
│   │   ├── pages/                # Route pages
│   │   ├── stores/               # Pinia stores
│   │   └── router/               # Vue Router config
│   ├── Dockerfile                # Frontend container
│   └── nginx.conf                # Frontend nginx config
├── services/
│   ├── catalog/
│   │   ├── app/
│   │   │   ├── Http/Controllers/
│   │   │   └── Models/
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   └── seeders/
│   │   ├── routes/
│   │   │   └── api.php
│   │   └── Dockerfile
│   ├── checkout/
│   │   ├── app/
│   │   │   ├── Http/Controllers/
│   │   │   ├── Models/
│   │   │   └── Services/
│   │   │       └── SagaOrchestrator.php
│   │   ├── database/
│   │   └── Dockerfile
│   ├── email/
│   │   ├── app/
│   │   │   ├── Console/Commands/
│   │   │   │   └── ListenToOrders.php
│   │   │   ├── Mail/
│   │   │   └── Models/
│   │   ├── database/
│   │   └── Dockerfile
│   └── nginx-gateway/
│       ├── nginx.conf            # Gateway configuration
│       └── Dockerfile
├── .github/
│   ├── workflows/
│   │   ├── ci.yml                # CI workflow
│   │   ├── deploy.yml            # Deployment workflow
│   │   └── infrastructure.yml    # Infrastructure deployment
│   └── ACTIONS_SETUP.md          # GitHub Actions setup guide
├── .env.example                  # Environment template
├── .gitignore
├── docker-compose.yml            # Development compose
├── DEPLOYMENT.md                 # Linux/macOS deployment guide
└── README.md                     # This file
```

## Technologies Used

### Backend
- **PHP**: 8.3
- **Laravel**: 12.x
- **MySQL**: 8.0
- **Redis**: 7 (Alpine)
- **Composer**: 2.x

### Frontend
- **Vue.js**: 3.x
- **TypeScript**: 5.x
- **Vite**: 5.x
- **Vue Router**: 4.x
- **Pinia**: 2.x (State Management)
- **Tailwind CSS**: 3.x
- **shadcn-vue**: UI Components

### Infrastructure
- **Docker**: 20.10+
- **Docker Compose**: 2.0+
- **Nginx**: Alpine
- **Supervisor**: (Email service process manager)

