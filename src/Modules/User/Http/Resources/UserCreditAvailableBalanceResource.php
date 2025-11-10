<?php

namespace App\Modules\User\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCreditAvailableBalanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'available_balance' => $this->available_balance,
        ];
    }
}
