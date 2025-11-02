<?php

namespace App\Modules\Investment\Services;

use App\Core\Services\BaseService;
use App\Core\Services\EventDispatcher;
use App\Core\Http\ServiceResponse;
use App\Modules\Investment\Repositories\InvestmentRepository;
use App\Modules\Investment\Events\InvestmentCreated;
use Illuminate\Support\Facades\Auth;
class InvestmentService extends BaseService
{
    protected string $serviceName = 'InvestmentService';

    public function __construct(
        private InvestmentRepository $investmentRepository,
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
            // Create the investment
            $response = parent::store($data);

            if ($response->isSuccess() && $response->getData()) {
                $investment = $response->getData();

                // Create event instance
                $event = new InvestmentCreated($investment, [
                    'created_by' => Auth::id(),
                    'source' => 'api',
                    'timestamp' => now()->toISOString(),
                    'request' => $data,
                    'category_id' => $data['category_id'] ?? null,
                ]);

                // Dispatch using configurable EventDispatcher to honor env/queue/scheduled modes
                $this->eventDispatcher->dispatch($event, 'investment_created');

                $this->logBusinessLogic(
                    'InvestmentService',
                    [
                        'operation' => 'investment_created_event_emitted',
                        'investment_id' => $investment->id,
                        'investment_uuid' => $investment->uuid ?? null,
                        'event_metadata' => [
                            'created_by' => Auth::id(),
                            'source' => 'api',
                            'request' => $data,
                        ],
                    ]
                );
            }

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


}
