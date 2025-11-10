<?php

namespace App\Modules\Payment\Http\Controllers;

use App\Core\Controllers\CrudController;
use App\Core\Http\ServiceResponse;
use App\Core\Exceptions\AppException;
use App\Modules\Payment\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentGatewayController extends CrudController
{
    public function __construct(
        private PaymentGatewayService $paymentGatewayService
    ) {
        parent::__construct($paymentGatewayService);
    }

    /**
     * Get payment gateway by slug
     * @param array $data
     * @return ServiceResponse
     */
    public function getPaymentGatewayBySlug(array $data): ServiceResponse
    {
        return $this->paymentGatewayService->getPaymentGatewayBySlug($data);
    }

    /**
     * before store
     * @param array $data
     * @param Request $request
     * @return array
     */
    protected function beforeStore(array $data, Request $request): array
    {
        // Only disable if the new gateway is being activated
        if (($data['is_active'] ?? false) === true) {
            $this->disableExistingPaymentGateway($data['slug']);
        }
        return $data;
    }

    /**
     * before update
     * @param array $data
     * @param Request $request
     * @param int $id
     * @return array
     */
    protected function beforeUpdate(array $data, Request $request, $id): array
    {

        $this->paymentGatewayService->validateUpdateRequest($data, $id);
        // Only disable if the current gateway is being activated
        if (($data['is_active'] ?? false) === true) {

            $this->disableExistingPaymentGateway($data['slug'], $id);
        }


        return $data;
    }

    /**
     * Disable existing payment gateways with the same slug
     * @param string $slug
     * @param int|null $excludeId ID to exclude (for update operations)
     * @return void
     */
    private function disableExistingPaymentGateway(string $slug, ?int $excludeId = null): void
    {
        $this->paymentGatewayService->disablePaymentGateway($slug, $excludeId);
    }




}
