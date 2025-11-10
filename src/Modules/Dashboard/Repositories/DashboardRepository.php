<?php

namespace App\Modules\Dashboard\Repositories;

use App\Core\Repositories\BaseRepository;
use App\Modules\Funding\Database\Models\Funding;
use App\Modules\Withdrawal\Database\Models\Withdrawal;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardRepository extends BaseRepository
{

    public function __construct(Funding $funding, Withdrawal $withdrawal, Investment $investment)
    {
        parent::__construct($funding, $withdrawal, $investment);
    }

    /**
     * Get total deposits (funding) with filters
     */
    public function getTotalDeposits(array $filters = []): float
    {
        $query = $this->buildFundingQuery($filters);
        return (float) $query->sum('fiat_amount');
    }

    /**
     * Get total withdrawals with filters
     */
    public function getTotalWithdrawals(array $filters = []): float
    {
        $query = $this->buildWithdrawalQuery($filters);
        return (float) $query->sum('fiat_amount');
    }

    /**
     * Get active investments count with filters
     */
    public function getActiveInvestmentsCount(array $filters = []): int
    {
        $query = $this->buildInvestmentQuery($filters);
        return $query->where('status', 'active')->count();
    }

    /**
     * Get total active investments value
     */
    public function getActiveInvestmentsValue(array $filters = []): float
    {
        $query = $this->buildInvestmentQuery($filters);
        return (float) $query->where('status', 'active')->sum('amount');
    }

    /**
     * Get deposits count
     */
    public function getDepositsCount(array $filters = []): int
    {
        return $this->buildFundingQuery($filters)->count();
    }

    /**
     * Get withdrawals count
     */
    public function getWithdrawalsCount(array $filters = []): int
    {
        return $this->buildWithdrawalQuery($filters)->count();
    }

    /**
     * Get statistics grouped by date (for charts)
     */
    public function getStatisticsByDate(array $filters = [], string $groupBy = 'day'): array
    {
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };

        $deposits = $this->buildFundingQuery($filters)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('SUM(fiat_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $withdrawals = $this->buildWithdrawalQuery($filters)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as date"),
                DB::raw('SUM(fiat_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
        ];
    }

    /**
     * Build funding query with filters
     */
    protected function buildFundingQuery(array $filters = []): Builder
    {
        $query = Funding::query();

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply user filter (if needed for ownership-based filtering)
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Apply currency filter
        if (isset($filters['currency_id'])) {
            $query->where('currency_id', $filters['currency_id']);
        }

        return $query;
    }

    /**
     * Build withdrawal query with filters
     */
    protected function buildWithdrawalQuery(array $filters = []): Builder
    {
        $query = Withdrawal::query();

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply user filter
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Apply currency filter
        if (isset($filters['currency_id'])) {
            $query->where('currency_id', $filters['currency_id']);
        }

        return $query;
    }

    /**
     * Build investment query with filters
     */
    protected function buildInvestmentQuery(array $filters = []): Builder
    {
        $query = Investment::query();

        // Apply date filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply user filter
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Apply type filter
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }

    /**
     * Get comprehensive dashboard statistics
     */
    public function getDashboardStatistics(array $filters = []): array
    {
        return [
            'total_deposits' => $this->getTotalDeposits($filters),
            'total_withdrawals' => $this->getTotalWithdrawals($filters),
            'deposits_count' => $this->getDepositsCount($filters),
            'withdrawals_count' => $this->getWithdrawalsCount($filters),
            'active_investments' => $this->getActiveInvestmentsCount($filters),
            'active_investments_value' => $this->getActiveInvestmentsValue($filters),
            'net_flow' => $this->getTotalDeposits($filters) - $this->getTotalWithdrawals($filters),
        ];
    }
}
