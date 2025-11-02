<?php

namespace App\Modules\Payment\Services;

use App\Core\Services\BaseService;
use App\Modules\Payment\Repositories\PaymentGatewayRepository;
use App\Modules\Payment\Contracts\PaymentGatewayServiceContract;
use App\Core\Http\ServiceResponse;

class PaymentGatewayService extends BaseService implements PaymentGatewayServiceContract
{
    protected string $serviceName = 'PaymentGatewayService';

    public function __construct(
        private PaymentGatewayRepository $paymentGatewayRepository
    ) {
        parent::__construct($paymentGatewayRepository);
    }

    protected array $paymentGatewayColumns = [
        'id',
        'uuid',
        'name',
        'slug',
        'description',
        'mode',
        'type',
        'is_traditional',
        'instructions',
        'supported_currencies',
        'credentials',
        'is_active'
    ];
    /**
     * Process traditional payment
     * @param array $data
     * @return ServiceResponse
     */
    public function processTraditionalPayment(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $paymentGateway = $this->paymentGatewayRepository->findBySlug($data['slug'], $this->paymentGatewayColumns);
            if (!$paymentGateway) {
                return ServiceResponse::error('Payment gateway not found');
            }

            return ServiceResponse::success($paymentGateway, 'Payment gateway retrieved successfully');
        }, 'processTraditionalPayment');
    }

    /**
     * Find payment gateway by slug
     * @param string $slug
     * @return ServiceResponse
     */
    public function getPaymentGatewayBySlug(string $slug): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($slug) {
            $paymentGateway = $this->paymentGatewayRepository->findBySlug($slug, $this->paymentGatewayColumns);
            if (!$paymentGateway) {
                return ServiceResponse::error('Payment gateway not found');
            }
            return ServiceResponse::success($paymentGateway, 'Payment gateway retrieved successfully');
        }, 'getPaymentGatewayBySlug');
    }
}
