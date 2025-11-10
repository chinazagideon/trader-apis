<?php

namespace App\Modules\Dashboard\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatisticsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total_deposits' => (float) $this->resource['total_deposits'],
            'total_withdrawals' => (float) $this->resource['total_withdrawals'],
            'deposits_count' => (int) $this->resource['deposits_count'],
            'withdrawals_count' => (int) $this->resource['withdrawals_count'],
            'active_investments' => (int) $this->resource['active_investments'],
            'active_investments_value' => (float) $this->resource['active_investments_value'],
            'net_flow' => (float) $this->resource['net_flow'],
            // 'chart_data' => $this->when(isset($this->resource['chart_data']), $this->resource['chart_data']),
        ];
    }
}
