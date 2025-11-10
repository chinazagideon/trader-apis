<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCreditCommissionBalanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'commission_balance' => $this->commission_balance,
        ];
    }
}
