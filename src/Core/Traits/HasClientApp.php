<?php

namespace App\Core\Traits;

use Illuminate\Contracts\Container\Container;
use App\Modules\Client\Database\Models\Client;

trait HasClientApp
{
    // Container keys (single source of truth)
    protected function clientIdKey(): string { return 'current_client_id'; }
    protected function clientKey(): string   { return 'current_client'; }

    /**
     * app container instance
     *
     * @return Container
     */
    protected function app(): Container
    {
        return app();
    }

    /**
     * check if the container has a key
     *
     * @param string $key
     * @return bool
     */
    protected function containerHas(string $key): bool
    {
        return $this->app()->bound($key);
    }

    /**
     * get a value from the container
     *
     * @param string $key
     * @return mixed
     */
    protected function containerGet(string $key): mixed
    {
        return $this->app()->get($key);
    }

    /**
     * set a value in the container
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function containerSet(string $key, mixed $value): void
    {
        $this->app()->instance($key, $value);
    }

    /**
     * forget a value from the container
     *
     * @param string $key
     * @return void
     */
    protected function containerForget(string $key): void
    {
        if (method_exists($this->app(), 'forgetInstance')) {
            $this->app()->forgetInstance($key);
        } else {
            $this->containerSet($key, null);
        }
    }

    // Public API

    /**
     * get an instance from the container
     *
     * @param string $abstract
     * @return mixed
     */
    public function getAppInstance(string $abstract): mixed
    {
        return $this->app()->make($abstract);
    }

    /**
     * create an instance in the container
     *
     * @param string $abstract
     * @param mixed $instance
     * @return void
     */
    public function createAppInstance(string $abstract, mixed $instance): void
    {
        $this->containerSet($abstract, $instance);
    }


    /**
     * Set full client context with either an ID or a model. Null clears context.
     *
     * @param Client|int|null $client
     * @return void
     */
    public function setClientContext(Client|int|null $client): void
    {
        if ($client instanceof Client) {
            $this->containerSet($this->clientIdKey(), $client->id);
            $this->containerSet($this->clientKey(), $client);
            return;
        }

        if (is_int($client)) {
            $this->containerSet($this->clientIdKey(), $client);
            $this->containerForget($this->clientKey()); // lazy-load model on demand
            return;
        }

        // Clear
        $this->forgetClientContext();
    }



    /**
     * check if the client is set
     *
     * @return bool
     */
    public function hasClient(): bool
    {
        return (bool) $this->getClientId();
    }

    /**
     * get the client id
     *
     * @return ?int
     */
    public function getClientId(): ?int
    {
        if ($this->containerHas($this->clientIdKey())) {
            return $this->containerGet($this->clientIdKey());
        }

        // Fallback: infer from cached client
        return $this->getClient()?->id;
    }

    /**
     * get the client
     *
     * @return ?Client
     */
    public function getClient(): ?Client
    {
        // If a model is already cached, return it
        if ($this->containerHas($this->clientKey())) {
            $bound = $this->containerGet($this->clientKey());
            if ($bound instanceof Client) {
                return $bound;
            }
        }

        // If only the id is set, lazily resolve and cache the model
        $id = $this->containerHas($this->clientIdKey()) ? $this->containerGet($this->clientIdKey()) : null;
        if ($id) {
            $model = Client::find($id);
            if ($model) {
                $this->containerSet($this->clientKey(), $model);
                return $model;
            }
        }

        return null;
    }

    /**
     * get the client name
     *
     * @return ?string
     */
    public function getAppClientName(): ?string
    {
        return $this->getClient()?->name;
    }

    /**
     * get the client slug
     *
     * @return ?string
     */
    public function getAppClientSlug(): ?string
    {
        return $this->getClient()?->slug;
    }

    /**
     * forget the client context
     *
     * @return void
     */
    public function forgetClientContext(): void
    {
        $this->containerForget($this->clientKey());
        $this->containerForget($this->clientIdKey());
    }
}
