<?php

namespace App\Modules\Withdrawal\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Modules\Withdrawal\Database\Models\Withdrawal;

class WithdrawalWasCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Constructor
     * @param Withdrawal $withdrawal
     * @param string $moduleName
     */
    public function __construct(public Withdrawal $withdrawal, public ?string $moduleName) {}
}
