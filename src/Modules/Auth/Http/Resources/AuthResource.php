<?php

namespace App\Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\User\Http\Resources\UserResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            new UserResource($this->resource),
            // 'id' => $this->id,
            // 'uuid' => $this->uuid,
            // 'name' => $this->name,
            // 'first_name' => $this->first_name,
            // 'last_name' => $this->last_name,
            // 'user_type' => $this->user_type,
            // 'referral_code' => $this->referral_code,
            // 'role' => $this->role?->name,
            // 'role_id' => $this->role_id,
            // 'avatar' => $this->avatar,
            // 'total_balance' => $this->total_balance,
            // 'available_balance' => $this->available_balance,
            // 'total_commission' => $this->total_commission,
            // 'email' => $this->email,
            // 'phone' => $this->phone,
            // 'is_active' => $this->is_active,
            // 'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            // 'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
