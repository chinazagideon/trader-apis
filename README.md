# Trader APIs - Microservice-Focused Architecture

This document outlines the microservice-focused architecture implemented in your Laravel application, designed for maximum scalability and maintainability.

## Architecture Overview

### **Core Principles:**
- **Module Isolation**: Each module is self-contained with its own migrations, routes, and configurations
- **Service Discovery**: Automatic module discovery and registration
- **API Gateway Pattern**: Centralized routing and health monitoring
- **Database Isolation**: Multiple strategies for database separation
- **Dynamic Exception Handling**: Consistent error handling across all modules

## Module Structure

Each module follows a standardized structure:

```
src/Modules/{ModuleName}/
├── Contracts/                 # Service interfaces
├── Database/
│   ├── Migrations/           # Module-specific migrations
│   ├── Seeders/             # Module-specific seeders
│   └── Models/              # Module-specific models
├── Http/
│   ├── Controllers/         # API controllers
│   ├── Requests/           # Form request validation
│   ├── Resources/          # API resources
│   └── Middleware/         # Module-specific middleware
├── Providers/              # Module service provider
├── Repositories/           # Data access layer
├── Services/              # Business logic layer
├── routes/
│   ├── api.php           # API routes
│   └── web.php           # Web routes
├── config/               # Module configuration
└── resources/            # Views, translations, assets
```

## Key Components

### 1. Module Manager (`App\Core\ModuleManager`)

**Purpose**: Automatic discovery and management of modules

**Features**:
- Auto-discovery of modules in `src/Modules/`
- Health status monitoring
- Service provider registration
- Route collection

**Usage**:
```php
$moduleManager = app(ModuleManager::class);
$modules = $moduleManager->getModules();
$health = $moduleManager->getModuleHealth('User');
```

### 2. API Gateway (`App\Core\Http\Controllers\ApiGatewayController`)

**Purpose**: Centralized entry point for all module APIs

**Features**:
- Module health monitoring
- Service discovery
- Load balancing (future)
- Request routing

**Endpoints**:
- `GET /api/gateway/status` - Gateway status
- `GET /api/gateway/health` - Overall health check
- `GET /api/gateway/modules` - All modules info
- `GET /api/gateway/modules/{module}` - Specific module info

### 3. Module Migration Manager (`App\Core\Database\ModuleMigrationManager`)

**Purpose**: Handle module-specific database migrations

**Features**:
- Module-specific migration execution
- Rollback capabilities
- Migration status tracking
- Database isolation strategies

**Usage**:
```bash
# Migrate specific module
php artisan module:migrate User

# Migrate all modules
php artisan module:migrate --all

# Rollback module
php artisan module:rollback User
```

## Database Isolation Strategies

### 1. **Shared Database** (Default)
- All modules use the same database
- Tables prefixed by module name
- Suitable for tightly coupled modules

### 2. **Schema Separation**
- Each module has its own database schema
- Shared database instance
- Better isolation than shared tables

### 3. **Database Separation**
- Each module has its own database
- Complete isolation
- Requires connection management

### 4. **Microservice Separation** (Future)
- Each module as separate service
- Independent databases
- Network communication

## 🛠️ Module Management Commands

### List Modules
```bash
php artisan module:list
php artisan module:list --health
```

### Create New Module
```bash
php artisan module:create Product --description="Product management module" --author="Your Company"
```

### Migrate Modules
```bash
php artisan module:migrate User
php artisan module:migrate --all
```

## Configuration

### Module Configuration
Each module can have its own configuration file:

```php
// src/Modules/User/config/user.php
return [
    'module' => [
        'name' => 'User',
        'version' => '1.0.0',
        'description' => 'User management module',
    ],
    'database' => [
        'connection' => env('USER_DB_CONNECTION', 'default'),
        'prefix' => env('USER_DB_PREFIX', ''),
    ],
    'api' => [
        'version' => 'v1',
        'prefix' => 'users',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];
```

### Environment Variables
```env
# Module-specific database connections
USER_DB_CONNECTION=mysql
USER_DB_PREFIX=user_

PRODUCT_DB_CONNECTION=mysql
PRODUCT_DB_PREFIX=product_

# API Gateway settings
API_GATEWAY_ENABLED=true
API_GATEWAY_CACHE_TTL=3600
```

## 🌐 API Routing

### Module Routes
Each module defines its own routes:

```php
// src/Modules/User/routes/api.php
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/health', [UserController::class, 'health']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        // ... more routes
    });
});
```

### API Gateway Routes
Centralized gateway routes:

```php
// Automatically registered by CoreServiceProvider
Route::prefix('api/gateway')->group(function () {
    Route::get('/status', [ApiGatewayController::class, 'status']);
    Route::get('/health', [ApiGatewayController::class, 'health']);
    // ... more gateway routes
});
```

## 🔍 Health Monitoring

### Module Health Checks
Each module can implement health checks:

```php
// In module controller
public function health(): JsonResponse
{
    return $this->successResponse([
        'status' => 'healthy',
        'module' => 'User',
        'timestamp' => now(),
        'checks' => [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ],
    ]);
}
```

### Gateway Health Monitoring
```bash
# Check overall system health
curl http://localhost/api/gateway/health

# Check specific module
curl http://localhost/api/gateway/modules/User
```

## 🚀 Deployment Strategies

### 1. **Monolithic Deployment** (Current)
- Single application instance
- All modules in one deployment
- Shared resources

### 2. **Module-Based Deployment** (Future)
- Each module as separate container
- Independent scaling
- Service mesh communication

### 3. **Hybrid Approach**
- Core modules in monolith
- Heavy modules as microservices
- Gradual migration

## 📊 Monitoring and Observability

### Logging
- Centralized logging with module context
- Structured logging format
- Module-specific log channels

### Metrics
- Module performance metrics
- API Gateway statistics
- Database connection monitoring

### Tracing
- Request tracing across modules
- Performance bottleneck identification
- Error tracking and alerting

## Security Considerations

### Module Isolation
- Each module has its own namespace
- Isolated configuration
- Independent authentication

### API Security
- Centralized authentication
- Rate limiting per module
- CORS configuration

### Database Security
- Connection isolation
- Access control per module
- Audit logging

## Testing Strategy

### Unit Testing
- Module-specific test suites
- Isolated test databases
- Mock external dependencies

### Integration Testing
- Module interaction testing
- API Gateway testing
- End-to-end scenarios

### Load Testing
- Module performance testing
- Gateway load testing
- Database performance testing

## Scalability Patterns

### Horizontal Scaling
- Module-based scaling
- Load balancer configuration
- Database sharding

### Vertical Scaling
- Resource allocation per module
- Memory optimization
- CPU optimization

### Caching Strategies
- Module-specific caching
- Distributed caching
- Cache invalidation

## Migration Path

### Phase 1: Module Structure (Current)
- ✅ Implement module structure
- ✅ Create module management tools
- ✅ Implement API Gateway

### Phase 2: Database Isolation
- 🔄 Implement database strategies
- 🔄 Add connection management
- 🔄 Create migration tools

### Phase 3: Service Separation
- ⏳ Extract modules to services
- ⏳ Implement service communication
- ⏳ Add service discovery

### Phase 4: Full Microservices
- ⏳ Complete service separation
- ⏳ Implement service mesh
- ⏳ Add monitoring and observability

## Best Practices

### Module Development
1. Keep modules focused and cohesive
2. Minimize inter-module dependencies
3. Use interfaces for module communication
4. Implement proper error handling
5. Write comprehensive tests

### Database Design
1. Use module-specific prefixes
2. Avoid cross-module foreign keys
3. Implement proper indexing
4. Use migrations for schema changes
5. Plan for data migration

### API Design
1. Follow RESTful conventions
2. Use consistent response formats
3. Implement proper versioning
4. Add comprehensive documentation
5. Use appropriate HTTP status codes

This architecture provides a solid foundation for building scalable, maintainable microservices while allowing for gradual migration from a monolithic structure.
