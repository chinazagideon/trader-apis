<?php

namespace App\Modules\User\Listeners;

use App\Modules\User\Contracts\UserDebitServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\User\Enums\UserPaymentTypes;

class UpdateUserBalanceListener implements ShouldQueue
{
    /**
     * Constructor.
     * @param UserDebitServiceInterface $userDebitService
     */
    public function __construct(
        private UserDebitServiceInterface $userDebitService,
    ) {}

    /**
     * Handle the event.
     * @param mixed $event
     * @return void
     */
    public function handle(mixed $event): void
    {

        $this->userDebitService->debit([
            'user_id' => $event->entity->user_id,
            'amount' => $event->entity->amount,
            'type' => UserPaymentTypes::Investment->value,
            'currency_id' => $event->entity->currency_id,
        ]);
    }
}
