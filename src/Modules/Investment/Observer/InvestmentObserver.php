<?php

namespace App\Modules\Investment\Observer;

use App\Modules\Investment\Database\Models\Investment;
use App\Modules\Investment\Events\InvestmentCreated;

class InvestmentObserver
{
    /**
     * Handle the investment created event
     * @param Investment $investment
     * @return void
     */
    public function created(Investment $investment): void
    {
        InvestmentCreated::dispatch($investment, ['category_id' => 1]);
    }
}
