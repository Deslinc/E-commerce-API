# E-Commerce Backend API

A comprehensive RESTful API for an e-commerce platform built with Laravel 11, featuring authentication, product management, shopping cart, checkout flow, and order processing.

## Features

- ✅ User Authentication (Registration, Login, Logout)
- ✅ Token-based Authentication using Laravel Sanctum
- ✅ Product Management (CRUD operations)
- ✅ Stock Management with validation
- ✅ Shopping Cart System
- ✅ Checkout Flow with stock validation
- ✅ Order Management
- ✅ Payment Simulation
- ✅ Admin/User Role Management
- ✅ Image Upload Support

## Tech Stack

- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **PHP Version**: 8.1+

## Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- PHP Extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON

## Installation

### Option 1: Automated Setup (Recommended)

```bash
# Clone the repository
git clone <your-repo-url>
cd ecommerce-backend

# Make setup script executable
chmod +x setup.sh

# Run setup script
./setup.sh
```

### Option 2: Manual Setup

```bash
# 1. Clone repository
git clone <your-repo-url>
cd ecommerce-backend

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure database in .env
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=your_password

# 6. Create database
mysql -u root -p
CREATE DATABASE ecommerce_db;
exit;

# 7. Run migrations
php artisan migrate

# 8. Seed database
php artisan db:seed

# 9. Create storage link
php artisan storage:link

# 10. Start server
php artisan serve
```

## Default Credentials

After seeding, you can use these credentials:

**Admin User:**
- Email: admin@example.com
- Password: password123

**Regular User:**
- Email: user@example.com
- Password: password123

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login user | No |
| POST | `/api/auth/logout` | Logout user | Yes |
| GET | `/api/auth/profile` | Get user profile | Yes |

### Products (Public)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/products` | List all products | No |
| GET | `/api/products/{id}` | Get single product | No |

### Products (Admin)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/admin/products` | Create product | Admin |
| PUT | `/api/admin/products/{id}` | Update product | Admin |
| DELETE | `/api/admin/products/{id}` | Delete product | Admin |
| POST | `/api/admin/products/{id}/restock` | Restock product | Admin |

### Cart

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/cart` | View cart | Yes |
| POST | `/api/cart/items` | Add item to cart | Yes |
| PUT | `/api/cart/items/{id}` | Update cart item | Yes |
| DELETE | `/api/cart/items/{id}` | Remove cart item | Yes |
| DELETE | `/api/cart` | Clear cart | Yes |

### Orders

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/orders/checkout` | Create order from cart | Yes |
| GET | `/api/orders` | Get user orders | Yes |
| GET | `/api/orders/{id}` | Get specific order | Yes |
| GET | `/api/admin/orders` | Get all orders | Admin |
| PUT | `/api/admin/orders/{id}/status` | Update order status | Admin |

### Payment

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/payment/simulate` | Simulate payment | Yes |

## API Usage Examples

### Register User

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Desmond Linc",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'
```

### Get Products

```bash
curl http://localhost:8000/api/products
```

### Add to Cart (Authenticated)

```bash
curl -X POST http://localhost:8000/api/cart/items \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "product_id": 1,
    "quantity": 2
  }'
```

### Checkout

```bash
curl -X POST http://localhost:8000/api/orders/checkout \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Simulate Payment

```bash
curl -X POST http://localhost:8000/api/payment/simulate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "order_id": 1
  }'
```

## Testing

### Using Postman

1. Import the Postman collection (see API Documentation artifact)
2. Set base URL to `http://localhost:8000/api`
3. Login to get authentication token
4. Add token to Authorization header for protected routes

### Using PHP Test Script

```bash
php test_api.php
```

## Database Schema

### Users
- id
- name
- email
- password
- is_admin
- timestamps

### Products
- id
- name
- description
- price
- stock
- image_url
- timestamps

### Carts
- id
- user_id
- timestamps

### Cart Items
- id
- cart_id
- product_id
- quantity
- timestamps

### Orders
- id
- user_id
- total_amount
- status
- timestamps

### Order Items
- id
- order_id
- product_id
- quantity
- price
- timestamps

## Project Structure

```
ecommerce-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── CartController.php
│   │   │       ├── OrderController.php
│   │   │       ├── PaymentController.php
│   │   │       └── ProductController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   └── Models/
│       ├── Cart.php
│       ├── CartItem.php
│       ├── Order.php
│       ├── OrderItem.php
│       ├── Product.php
│       └── User.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
└── README.md
```

## Deployment (AWS - Optional)

### EC2 Setup
1. Launch Ubuntu EC2 instance
2. Install PHP, Composer, MySQL
3. Clone repository
4. Run setup script
5. Configure web server (Nginx/Apache)

### RDS Setup
1. Create MySQL RDS instance
2. Update `.env` with RDS credentials
3. Run migrations

### S3 Setup
1. Create S3 bucket for images
2. Install AWS SDK: `composer require aws/aws-sdk-php`
3. Update image upload logic for S3

## Troubleshooting

See `TROUBLESHOOTING.md` for common issues and solutions.

## Security Notes

- Change default passwords in production
- Use strong APP_KEY
- Enable HTTPS in production
- Set proper CORS headers
- Validate all inputs
- Sanitize file uploads


## Author

Developed as a Laravel backend assessment project.

## Support

For issues or questions, please create an issue in the repository.