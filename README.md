# Payment Service APIs - Laravel Microservice Application

A Secured robust, event-driven payment microservices system API key middleware for resource authorization and tenant isolation. Built with a modular, event-driven architecture for scalability and maintainability.

## Core Architecture Principles

This application follows three fundamental architectural patterns:

1. **Module-Based Architecture** - Self-contained, auto-discovered modules
2. **Contract-Based Dependency Injection** - Interface-driven service binding
3. **Event-Driven Design** - Decoupled, configurable event processing

---

## Module Architecture

### Auto-Discovery System

Modules are automatically discovered from the `src/Modules/` directory. Each module is self-contained with its own:
- Service providers
- Database migrations
- Routes
- Controllers, Services, Repositories
- Configuration files
- Event listeners

**Module Structure:**
```
src/Modules/{ModuleName}/
├── Contracts/              # Service interfaces
├── Database/
│   ├── Migrations/         # Module migrations
│   ├── Seeders/           # Module seeders
│   └── Models/            # Eloquent models
├── Http/
│   ├── Controllers/       # API controllers
│   ├── Requests/         # Form validation
│   └── Resources/        # API resources
├── Providers/            # Service providers
├── Repositories/         # Data access layer
├── Services/            # Business logic
├── Events/              # Domain events
├── Listeners/           # Event listeners
├── routes/
│   └── api.php         # Module routes
└── config/             # Module configuration
```

### Module Manager

The `ModuleManager` automatically discovers and registers modules:

```php
$moduleManager = app(ModuleManager::class);
$modules = $moduleManager->getModules();
$health = $moduleManager->getModuleHealth('User');
```

**Features:**
- Auto-discovery of modules in `src/Modules/`
- Service provider registration with priority support
- Health status monitoring
- Route and migration collection
- Module configuration loading

### Module Service Providers

Each module extends `BaseModuleServiceProvider` which provides:
- Automatic service registration
- Module namespace resolution
- Configuration merging
- Policy registration

**Example:**
```php
class UserServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleNamespace = 'App\\Modules\\User';
    protected array $configFiles = ['user'];

    protected function registerServices(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
```

---

## Contracts & Interface Binding

### Contract-Based Design

All services communicate through interfaces (contracts), enabling:
- Easy testing with mocks
- Implementation swapping
- Clear service boundaries
- Dependency inversion

### Service Binding Pattern

Services are bound to their interfaces in module service providers:

```php
// In UserServiceProvider
protected function registerServices(): void
{
    // Singleton binding
    $this->app->singleton(UserService::class);
    
    // Interface binding
    $this->app->bind(UserServiceInterface::class, UserService::class);
    $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
}
```

### Dependency Injection

Controllers and services receive dependencies via constructor injection:

```php
class UserController extends Controller
{
    public function __construct(
        private UserServiceInterface $userService
    ) {}
}
```

### Sub-Module Service Registry

The `SubModuleServiceRegistry` automatically discovers and registers services that implement `SubModuleServiceContract`:

```php
// Services implementing SubModuleServiceContract are auto-registered
class UserBalanceService implements SubModuleServiceContract
{
    public function getDefaultSubModuleName(): string
    {
        return 'user_balance';
    }
}

// Access via registry
$registry = app(SubModuleServiceRegistry::class);
$service = $registry->get('user_balance');
```

---

## Event-Driven Architecture

### Event System Overview

The application uses a configurable event system that supports three processing modes:
- **Sync** - Immediate processing
- **Queue** - Background job processing
- **Scheduled** - Batch processing via scheduled events table

### Event Configuration

Events are configured in `config/events.php`:

```php
'events' => [
    'investment_created' => [
        'class' => InvestmentWasCreated::class,
        'mode' => env('EVENT_INVESTMENT_MODE', 'queue'),
        'queue' => env('EVENT_INVESTMENT_QUEUE', 'default'),
        'priority' => 'high',
        'listeners' => [
            'create_transaction' => [
                'class' => CreateTransactionForEntity::class,
                'mode' => 'queue',
                'tries' => 5,
                'backoff' => [30, 60, 120],
            ],
        ],
    ],
],
```

### Notification Events Contract

All notification events implement `NotificationEventsContract`:

```php
interface NotificationEventsContract
{
    public function getEntity();
    public function getNotifiable();
    public function getEventType(): string;
    public function getChannels(): array;
    public function getTitle(): string;
    public function getMessage(): string;
    // ... more methods
}
```

### Base Notification Event

Events extend `BaseNotificationEvent`:

```php
class UserWasCreatedEvent extends BaseNotificationEvent
{
    public function __construct(public User $user) {}

    public function getEventType(): string
    {
        return 'user_was_created';
    }

    public function getEntity()
    {
        return $this->user;
    }

    public function getNotifiable()
    {
        return $this->user;
    }

    public function getChannels(): array
    {
        return ['database', 'mail'];
    }
}
```

### Configurable Listeners

Listeners implement `ConfigurableListenerInterface` to read configuration:

```php
class SendEntityNotification implements ShouldQueue, ConfigurableListenerInterface
{
    use ConfigurableListener, InteractsWithQueue;

    public function handle(NotificationEventsContract $event): void
    {
        // Listener reads its configuration from config/events.php
        // Queue, retries, backoff all configured per listener
    }
}
```

### Event Dispatcher

The `EventDispatcher` service routes events based on configuration:

```php
$eventDispatcher->dispatch(new InvestmentWasCreated($investment), 'investment_created');
```

The dispatcher checks `config/events.php` to determine:
- Processing mode (sync/queue/scheduled)
- Queue name
- Retry configuration
- Priority

---

## Notification Outbox Pattern

### Overview

The notification system uses an outbox pattern to ensure reliable notification delivery:

1. **Event Fired** → Listener publishes to `notification_outbox` table
2. **Outbox Processing** → Scheduled command processes pending notifications
3. **Notification Delivery** → Creates database notifications and queues emails

### Outbox Flow

```
Event → Listener → NotificationOutboxPublisher → notification_outbox table
                                                         ↓
                                    ProcessNotificationOutbox Command
                                                         ↓
                                    Database Notification + Queued Email
```

### Outbox Table

The `notification_outbox` table stores pending notifications:

- `event_type` - Type of event (e.g., 'user_was_created')
- `notifiable_type` / `notifiable_id` - Who receives the notification
- `entity_type` / `entity_id` - What triggered the notification
- `channels` - Delivery channels (database, mail, sms)
- `payload` - Notification data
- `status` - pending|processing|sent|failed
- `dedupe_key` - Prevents duplicate notifications

### Processing Command

The `notifications:outbox:process` command runs via Laravel scheduler:

```php
// routes/console.php
Schedule::command('notifications:outbox:process --limit=100')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
```

**Setup:**
```bash
# On server, add to crontab:
* * * * * cd /var/www/trader-apis && docker exec trader-apis-app php artisan schedule:run
```

---

## Module Management Commands

### List Modules
```bash
php artisan module:list
php artisan module:list --health
```

### Create Module
```bash
php artisan module:create Product --description="Product management"
```

### Migrations
```bash
php artisan module:migrate User
php artisan module:migrate --all
php artisan module:rollback User
php artisan module:migration:status
```

### Cache Module Providers
```bash
php artisan module:cache:providers
php artisan module:providers:list
```

---

## API Gateway

### Gateway Endpoints

- `GET /api/gateway/status` - Gateway status
- `GET /api/gateway/health` - Overall health check
- `GET /api/gateway/modules` - All modules info
- `GET /api/gateway/modules/{module}` - Specific module info

### Module Routes

Each module defines routes in `routes/api.php`:

```php
Route::prefix('api/v1/users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']);
});
```

---

## Database Architecture

### Module Migrations

Each module has its own migrations in `Database/Migrations/`. Migrations are automatically discovered and registered.

### Morph Maps

Polymorphic relationships use clean aliases via morph maps:

```php
// config/core.php
'morph_maps' => [
    'user' => \App\Modules\User\Database\Models\User::class,
    'investment' => \App\Modules\Investment\Database\Models\Investment::class,
]
```

### Database Connections

Modules can use separate database connections via configuration:

```php
// Module config
'database' => [
    'connection' => env('USER_DB_CONNECTION', 'default'),
    'prefix' => env('USER_DB_PREFIX', ''),
],
```

---

## Current Modules

- **Auth** - Authentication and authorization
- **User** - User management
- **Client** - Multi-client support
- **Investment** - Investment management
- **Transaction** - Transaction processing
- **Payment** - Payment processing
- **Funding** - Account funding
- **Withdrawal** - Withdrawal processing
- **Balance** - Balance management
- **Notification** - Notification system
- **Market** - Market data
- **Pricing** - Pricing engine
- **Currency** - Currency management
- **Category** - Transaction categories
- **Role** - Role-based access control
- **Dashboard** - Dashboard data
- **Swap** - Currency swapping

---

## Key Features

### 1. Module Auto-Discovery
Modules are automatically discovered and registered on application boot.

### 2. Contract-Based Services
All services use interfaces, enabling easy testing and implementation swapping.

### 3. Configurable Event Processing
Events can be processed sync, queued, or scheduled based on configuration.

### 4. Notification Outbox
Reliable notification delivery using the outbox pattern with scheduled processing.

### 5. Sub-Module Services
Automatic registration of services implementing `SubModuleServiceContract`.

### 6. Health Monitoring
Module health checks via API Gateway endpoints.

### 7. Module Migrations
Isolated migrations per module with rollback support.

---

## Development Workflow

### Creating a New Module

1. Generate module structure:
```bash
php artisan module:create Product
```

2. Define contracts in `Contracts/`:
```php
interface ProductServiceInterface {}
```

3. Implement services:
```php
class ProductService implements ProductServiceInterface {}
```

4. Bind in service provider:
```php
$this->app->bind(ProductServiceInterface::class, ProductService::class);
```

5. Create events:
```php
class ProductWasCreated extends BaseNotificationEvent {}
```

6. Register listeners in event service provider

7. Add routes in `routes/api.php`

8. Create migrations:
```bash
php artisan module:make:migration Product create_products_table
```

---

## Best Practices

### Module Design
- Keep modules focused on a single domain
- Use contracts for all service interfaces
- Minimize cross-module dependencies
- Each module should be independently testable

### Event Design
- Events should represent domain events (things that happened)
- Use descriptive event names (e.g., `UserWasCreated`, `InvestmentWasCreated`)
- Events should contain all data needed by listeners
- Store IDs, not full models, for serialization safety

### Service Design
- Services should implement interfaces
- Use dependency injection, not facades
- Keep services focused on business logic
- Repositories handle data access

### Testing
- Mock interfaces, not concrete classes
- Test modules in isolation
- Use contracts for test doubles
- Test event listeners separately

---

## Configuration

### Module Configuration

Each module can define `config/{module}.php`:

```php
return [
    'module' => [
        'name' => 'User',
        'version' => '1.0.0',
    ],
    'database' => [
        'connection' => env('USER_DB_CONNECTION', 'default'),
    ],
];
```

### Event Configuration

Events are configured in `config/events.php` with per-event and per-listener settings.

### Notification Configuration

Notification channels and providers configured in `config/notification.php`.

---

This architecture provides a solid foundation for building scalable, maintainable applications with clear module boundaries and event-driven communication.
