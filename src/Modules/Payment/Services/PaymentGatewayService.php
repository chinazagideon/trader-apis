<?php

namespace App\Modules\Payment\Services;

use App\Core\Services\BaseService;
use App\Modules\Payment\Repositories\PaymentGatewayRepository;
use App\Modules\Payment\Contracts\PaymentGatewayServiceContract;
use App\Core\Http\ServiceResponse;
use App\Modules\Currency\Contracts\CurrencyServiceContract;
use App\Core\Exceptions\AppException;

class PaymentGatewayService extends BaseService implements PaymentGatewayServiceContract
{
    protected string $serviceName = 'PaymentGatewayService';

    public function __construct(
        private PaymentGatewayRepository $paymentGatewayRepository,
        private CurrencyServiceContract $currencyService
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
     * @param array $data
     * @return ServiceResponse
     */
    public function getPaymentGatewayBySlug(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {
            $paymentGateway = $this->paymentGatewayRepository->findBySlug($data['slug'], $this->paymentGatewayColumns, $data['filters']);
            if (!$paymentGateway) {
                return ServiceResponse::error('Payment gateway not found');
            }
            return ServiceResponse::success($paymentGateway, 'Payment gateway retrieved successfully');
        }, 'getPaymentGatewayBySlug');
    }

    /**
     * Prepare payment gateway data
     * @param array $data
     * @return array
     */
    private function preparePaymentGatewayData(array $data): array
    {

        return [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'mode' => $data['mode'] ?? 'test',
            'type' => $this->getCurrencyType($data['slug']),
            'is_traditional' => $data['is_traditional'] ?? false,
            'instructions' => $data['instructions'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            "instructions" => json_encode($this->prepareInstructions($data)),
        ];
    }

    /**
     * validate payment gateway slug against currency code
     * @param string $slug
     * @return string
     */
    private function getCurrencyType(string $slug): string
    {
        $currency = $this->currencyService->getCurrencyByCode($slug, ['type']);

        if (!$currency || $currency->getData()->type === null) {
            throw new AppException('Invalid slug or currency type not found');
        }

        return $currency->getData()->type;
    }

    /**
     * Store payment gateway
     * @param array $data
     * @return ServiceResponse
     */
    public function store(array $data): ServiceResponse
    {
        $response = parent::store($data);
        return $response;
    }

    /**
     * Prepare instructions
     * @param array $data
     * @return array
     */
    private function prepareInstructions(array $data): array
    {
        $instructions = config('Payment.payment_gateway.instructions');
        return [
            'title' => $instructions['title'],
            'address' => $data['payment_address'] ?? null,
            'network' => $data['wallet_network'] ?? null,
        ];
    }

    /**
     * Disable payment gateway
     * @param string $slug
     * @param int|null $excludeId ID to exclude from disabling
     * @return int Number of records disabled
     */
    public function disablePaymentGateway(string $slug, ?int $excludeId = null): int
    {
        return $this->paymentGatewayRepository->disablePaymentGateway($slug, $excludeId);
    }

    /**
     * Validate update request
     * @param array $data
     * @param int $id
     * @return void
     */
    public function validateUpdateRequest(array $data, int $id): void
    {
        // Only validate if trying to deactivate
        if (($data['is_active'] ?? false) === false) {
            // Check if there are other active gateways with the same slug
            $activeGateway = $this->getPaymentGatewayBySlug([
                'slug' => $data['slug'] ?? $this->getCurrentGatewaySlug($id),
                'filters' => ['is_active' => true]
            ]);

            $response = $activeGateway->getData();

            if ($response && is_object($response) && $response->id !== $id) {
                return;
            }
            throw new AppException('Atleast one payment gateway must be active');
        }
    }

    /**
     * Destroy payment gateway
     * @param int $id
     * @return ServiceResponse
     */
    public function destroy(int $id): ServiceResponse
    {
        $destroy = parent::destroy($id);
        return $destroy;
    }


    /**
     * Get current gateway slug by ID
     * @param int $id
     * @return string
     */
    private function getCurrentGatewaySlug(int $id): string
    {
        $gateway = $this->show($id);
        if (!$gateway->isSuccess() || !$gateway->getData()) {
            throw new \App\Core\Exceptions\AppException('Payment gateway not found');
        }
        return $gateway->getData()->slug;
    }

    /**
     * Get payment gateways
     * @param array $filters
     * @param int $perPage
     * @return ServiceResponse
     */
    public function index(array $filters = [], int $perPage = 15): ServiceResponse
    {

        return $this->executeServiceOperation(function () use ($filters, $perPage) {
            $paymentGateways = $this->paymentGatewayRepository->getPaymentGateways($filters, $perPage);
            return ServiceResponse::success($paymentGateways, 'Payment gateways retrieved successfully');
        }, 'index');
    }

}
