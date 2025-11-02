<?php

namespace App\Modules\Payment\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Payment\Database\Models\PaymentGateway;

class PaymentGatewayRepository extends BaseRepository
{
    protected string $serviceName = 'PaymentGatewayRepository';

    /**
     * Constructor
     * @param PaymentGateway $model
     */
    public function __construct(PaymentGateway $model)
    {
        parent::__construct($model);
    }

    /**
     * Find payment gateway by slug
     * @param string $slug
     * @param array $columns
     * @return PaymentGateway|null
     */
    public function findBySlug(string $slug, array $columns = ['*']): ?PaymentGateway
    {
        return $this->findBy('slug', $slug, $columns);
    }

    /**
     * Find payment gateway by id
     * @param int $id
     * @param array $columns
     * @return PaymentGateway|null
     */
    public function findById(int $id, array $columns = ['*']): ?PaymentGateway
    {
        return $this->find($id, $columns);
    }
}
