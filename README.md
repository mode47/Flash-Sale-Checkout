

```bash

echo "# 🚀 Flash Sale API - Laravel

A high-performance flash sale API built with Laravel that handles high concurrency without overselling. This project demonstrates advanced backend development skills including race condition prevention, database optimization, and clean architecture.

## 🎯 Features

- ✅ **Real-time Stock Management** - Accurate available stock calculation
- ✅ **Temporary Holds System** - 2-minute reservations with auto-expiry
- ✅ **Race Condition Prevention** - Database locks & transactions
- ✅ **Idempotent Payment Webhook** - Safe duplicate payment handling
- ✅ **Caching Strategy** - Fast reads with real-time invalidation
- ✅ **Repository & Service Pattern** - Clean architecture separation
- ✅ **Comprehensive Testing** - Concurrency and edge case coverage

## 🛠 Tech Stack

- **Laravel 12** - PHP Framework
- **MySQL** - Database with transactions & locks
- **Redis** - Caching (Optional)
- **PHPUnit** - Testing framework
- **Repository Pattern** - Data abstraction layer
- **Service Layer** - Business logic separation

## 🏗 Architecture

\`\`\`
app/
├── Http/Controllers/API/V1/     # API Controllers
├── Models/                      # Eloquent Models
├── Repositories/               # Repository Pattern
├── Services/                   # Business Logic
└── Exceptions/                 # Custom Exceptions
\`\`\`

### **Key Design Decisions:**

1. **Database Locks** - Used \`lockForUpdate()\` to prevent race conditions
2. **Repository Pattern** - Abstracted data layer for testability
3. **Service Layer** - Centralized business logic
4. **Global Exception Handling** - Consistent API responses
5. **Strategic Caching** - Balance between performance and accuracy

## ⚡ Quick Start

\`\`\`bash
# Clone repository
git clone https://github.com/mode47/flash-sale-interview.git
cd flash-sale-interview

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start server
php artisan serve
\`\`\`

## 🎯 API Endpoints

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| \`GET\` | \`/api/products/{id}\` | Get product with real-time stock | Public |
| \`POST\` | \`/api/holds\` | Create temporary hold (2min expiry) | Public |
| \`POST\` | \`/api/orders\` | Create order from valid hold | Public |
| \`POST\` | \`/api/payments/webhook\` | Idempotent payment processing | Webhook |

### **Example Requests:**

\`\`\`bash
# Get product
curl http://localhost:8000/api/products/1

# Create hold
curl -X POST http://localhost:8000/api/holds \\
  -H \"Content-Type: application/json\" \\
  -d '{\"product_id\": 1, \"quantity\": 2}'

# Create order
curl -X POST http://localhost:8000/api/orders \\
  -H \"Content-Type: application/json\" \\
  -d '{\"hold_id\": 1}'
\`\`\`

## 🔒 Concurrency Handling

### **Race Condition Prevention:**
- **Database-level locking** (\`lockForUpdate()\`) for stock operations
- **Transaction management** ensuring data consistency
- **Atomic operations** preventing overselling

### **Example Implementation:**
\`\`\`php
public function createHold(int \$productId, int \$qty)
{
    return DB::transaction(function() use (\$productId, \$qty) {
        \$product = \$this->holds->lockProduct(\$productId); // Lock row
        \$existingHolds = \$this->holds->sumActiveHolds(\$productId);
        \$availableStock = \$product->stock - \$existingHolds;
        
        if (\$availableStock < \$qty) {
            throw new RuntimeException(\"Insufficient stock\");
        }
        
        return \$this->holds->createHold(\$productId, \$qty);
    });
}
\`\`\`

## 🧪 Testing

\`\`\`bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test tests/Feature/HoldEndpointTest.php
php artisan test tests/Feature/ConcurrencyTest.php

# Test with coverage
php artisan test --coverage-html coverage
\`\`\`

### **Test Coverage:**
- ✅ Hold creation with sufficient stock
- ✅ Race condition prevention
- ✅ Insufficient stock scenarios
- ✅ Hold expiration logic
- ✅ Payment webhook idempotency

## 📊 Performance Considerations

- **Caching Strategy**: Short TTL (2min) for stock with real-time invalidation
- **Database Indexing**: Optimized for high-concurrency reads
- **Query Optimization**: Eager loading and selective field retrieval
- **Connection Pooling**: Prepared for horizontal scaling

## 🚀 Deployment

\`\`\`bash
# Production setup
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
\`\`\`

## 👨‍💻 Developer

**Mohamed  Abd Algwad **  
- GitHub: [@mode47](https://github.com/mode47)

## 📄 License

This project is licensed under the MIT License.
