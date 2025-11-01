<?php

namespace App\Modules\Funding\Events;


use App\Modules\Funding\Database\Models\Funding;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FundingWasCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Constructor
     * @param Funding $funding
     * @param string $moduleName
     */
    public function __construct(
        public Funding $funding,
        public string $moduleName,
    ) {
    }
}
