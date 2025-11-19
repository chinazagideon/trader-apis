<?php

namespace App\Modules\Notification\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Notification\Channels\MultiProviderMailChannel;
use App\Modules\Notification\Channels\SmsChannel;
use App\Modules\Notification\Channels\CustomChannel;
use App\Modules\Notification\Services\ProviderManager;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Services\NotificationOutboxPublisher;
use App\Modules\Notification\Services\SendGridMailerService;
use App\Modules\Notification\Repositories\NotificationRepository;
use App\Modules\Notification\Database\Models\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use App\Modules\Notification\Contracts\NotificationOutboxPublisherInterface;
use App\Modules\Notification\Console\Commands\ProcessNotificationOutbox;
use App\Modules\Notification\Contracts\ProviderResolverInterface;
use App\Modules\Notification\Services\ProviderResolver;

class NotificationServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Notification';

    /**
     * Config files
     */
    protected array $configFiles = [
        'notification',
    ];

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        // Register SendGrid Mailer Service first (dependency for ProviderManager)
        $this->app->singleton(SendGridMailerService::class, function ($app) {
            return new SendGridMailerService();
        });



        // Register Provider Resolver
        $this->app->singleton(ProviderResolverInterface::class, ProviderResolver::class);

        // Register ProviderManager with ProviderResolver injected
        $this->app->singleton(ProviderManager::class, function ($app) {
            return new ProviderManager(
                $app->make(ProviderResolverInterface::class)
            );
        });

        // Register NotificationRepository
        $this->app->singleton(NotificationRepository::class, function ($app) {
            return new NotificationRepository(new Notification());
        });

        // Register NotificationService
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(NotificationRepository::class),
                $app->make(ProviderManager::class)
            );
        });

        // Register Outbox Publisher
        $this->app->singleton(NotificationOutboxPublisherInterface::class, function ($app) {
            return new NotificationOutboxPublisher();
        });

        // Register Outbox Command
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessNotificationOutbox::class,
            ]);
        }
    }

    /**
     * Boot services
     */
    public function boot(): void
    {
        parent::boot();

        // Register config file
        $this->mergeConfigFrom(
            __DIR__ . '/../config/notification.php',
            'notification'
        );

        // Register notification views
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'notification'
        );

        // Register custom notification channels
        $this->registerNotificationChannels();

        // Register event service provider
        $this->app->register(NotificationEventServiceProvider::class);
    }

    /**
     * Register custom notification channels
     */
    protected function registerNotificationChannels(): void
    {
        // Override Laravel's default 'mail' channel
        NotificationFacade::extend('mail', function ($app) {
            return $app->make(MultiProviderMailChannel::class);
        });

        // Register Multi-Provider Mail Channel
        NotificationFacade::extend('multi_mail', function ($app) {
            return $app->make(MultiProviderMailChannel::class);
        });

        // Register SMS Channel
        NotificationFacade::extend('sms', function ($app) {
            return $app->make(SmsChannel::class);
        });

        // Register Push Channel
        NotificationFacade::extend('push', function ($app) {
            return CustomChannel::push($app->make(ProviderManager::class));
        });

        // Register Custom Slack Channel
        NotificationFacade::extend('custom_slack', function ($app) {
            return CustomChannel::slack($app->make(ProviderManager::class));
        });
    }
}
