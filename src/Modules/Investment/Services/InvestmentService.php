<?php

namespace App\Modules\Investment\Services;

use App\Core\Services\BaseService;
use App\Core\Services\EventDispatcher;
use App\Core\Http\ServiceResponse;
use App\Modules\Investment\Repositories\InvestmentRepository;
use App\Modules\Investment\Events\InvestmentCreated;
use App\Modules\Investment\Events\InvestmentWasCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Core\Exceptions\ServiceException;
use App\Modules\User\Contracts\UserBalanceServiceInterface;
use App\Modules\User\Enums\UserBalanceEnum;

class InvestmentService extends BaseService
{
    protected string $serviceName = 'InvestmentService';

    public function __construct(
        private InvestmentRepository $investmentRepository,
        private UserBalanceServiceInterface $userBalanceService,
        private EventDispatcher $eventDispatcher
    ) {
        parent::__construct($investmentRepository);
    }

    /**
     * Override store method to emit InvestmentCreated event
     */
    public function store(array $data): ServiceResponse
    {
        return $this->executeServiceOperation(function () use ($data) {

            $this->validateUserBalance($data);
            $response = parent::store($this->prepareData($data));

            if (! $response->isSuccess()) {
                throw new ServiceException($response->getMessage());
            }

            $investment = $response->getData();
            $this->dispatcher($investment);
            return $response;
        }, 'store');
    }

    /**
     * Get the default entry type for investment
     */
    public function getDefaultEntryType(): string
    {
        return config('investment.transaction.entry_type');
    }

    /**
     * Get the default status for investment
     */
    public function getDefaultStatus(): string
    {
        return config('investment.transaction.status');
    }

    /**
     * Dispatcher the event
     * @param Model $model
     * @return void
     */
    private function dispatcher(Model $model): void
    {
        // Create event instance
        $event = new InvestmentCreated($model, [
            'created_by' => Auth::id(),
            'source' => 'api',
            'timestamp' => now()->toISOString(),
            'request' => $model->toArray(),
            'category_id' => $model->category_id ?? null,
        ]);


        // Dispatch using configurable EventDispatcher to honor env/queue/scheduled modes
        $this->eventDispatcher->dispatch($event, 'investment_created');
        $this->eventDispatcher->dispatch(
            new InvestmentWasCreated(
                $model,
                ['user' => $model->user ?? $model->user]
            ),
            'investment_was_created'
        );
    }

    /**
     * Prepare data for investment creation
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        unset($data['category_id']);
        return $data;
    }

    /**
     * Validate user balance
     * @param array $data
     * @return void
     */
    private function validateUserBalance(array $data): void
    {
        $data['type'] = UserBalanceEnum::Investment->value;
        $this->userBalanceService->checkBalance($data);
        if (!$this->userBalanceService->checkBalance($data)) {
            throw new ServiceException('Insufficient balance');
        }
    }
}
