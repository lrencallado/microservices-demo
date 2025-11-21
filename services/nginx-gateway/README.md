 # Nginx Gateway Service

API Gateway and reverse proxy for the microservices system.

## Purpose

Single entry point for all client requests. Routes traffic to appropriate backend services.

## Routing Rules

| Path | Destination | Port |
|------|-------------|------|
| `/` | Frontend (Vue.js) | 8080 |
| `/api/catalog/*` | Catalog Service | 3001 |
| `/api/checkout/*` | Checkout Service | 3002 |
| `/health` | Health check | - |

## Features

- **Reverse Proxy**: Routes requests to backend services
- **CORS Handling**: Manages cross-origin requests
- **Health Check**: `/health` endpoint for monitoring
- **Request Logging**: Access and error logs
- **Timeout Configuration**: Extended timeouts for long requests

## Local Development

Run standalone:

```bash
docker build -t cinch-nginx-gateway .
docker run -p 80:80 cinch-nginx-gateway

Or use with docker-compose from project root.

Configuration

The routing logic is defined in nginx.conf:

- Upstream blocks: Define backend service addresses
- Location blocks: Define routing rules and URL rewriting
- Proxy headers: Forward client information to backend services
- CORS headers: Enable cross-origin requests from frontend

Example Requests

# Frontend
curl http://localhost/

# Catalog API
curl http://localhost/api/catalog/products

# Checkout API
curl http://localhost/api/checkout/orders

# Health check
curl http://localhost/health

URL Rewriting

The gateway rewrites URLs before forwarding:

Client: GET /api/catalog/products
↓
Nginx: GET /api/products  (prefix removed)
↓
Catalog Service receives: /api/products

This keeps the backend services simple and unaware of the gateway prefix.

## How It Works:

1. **Client makes request** → `http://localhost/api/catalog/products`
2. **Nginx receives** → Matches `/api/catalog/*` location block
3. **Rewrites URL** → Removes `/api/catalog` prefix → `/api/products`
4. **Forwards to** → `catalog:3001/api/products`
5. **Returns response** → Back to client

---