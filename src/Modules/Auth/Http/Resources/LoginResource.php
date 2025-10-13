<?php

namespace App\Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
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
            'user' => new AuthResource($this->resource['user']),
            'token' => [
                'access_token' => $this->resource['token']['token'],
                'token_type' => $this->resource['token']['type'],
                'expires_at' => $this->resource['token']['expires_at'],
            ],
        ];
    }
}
