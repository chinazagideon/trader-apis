<?php

namespace App\Modules\Notification\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Notification\Channels\MultiProviderMailChannel;
use App\Modules\Notification\Channels\SmsChannel;
use App\Modules\Notification\Channels\CustomChannel;
use App\Modules\Notification\Services\ProviderManager;
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Notification\Repositories\NotificationRepository;
use App\Modules\Notification\Database\Models\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

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
        // Register ProviderManager
        $this->app->singleton(ProviderManager::class, function ($app) {
            return new ProviderManager();
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
    }

    /**
     * Boot services
     */
    public function boot(): void
    {
        parent::boot();

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
