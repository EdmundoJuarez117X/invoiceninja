<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2022. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Filters;

use App\Models\BankTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * BankTransactionFilters.
 */
class BankTransactionFilters extends QueryFilters
{
    /**
     * Filter by name.
     *
     * @param string $name
     * @return Builder
     */
    public function name(string $name = ''): Builder
    {
        if(strlen($name) >=1)
            return $this->builder->where('bank_account_name', 'like', '%'.$name.'%');

        return $this->builder;
    }

    /**
     * Filter based on search text.
     *
     * @param string query filter
     * @return Builder
     * @deprecated
     */
    public function filter(string $filter = '') : Builder
    {
        if (strlen($filter) == 0) {
            return $this->builder;
        }

        return  $this->builder->where(function ($query) use ($filter) {
            $query->where('bank_transactions.description', 'like', '%'.$filter.'%');
        });

    }


/**
     * Filter based on client status.
     *
     * Statuses we need to handle
     * - all
     * - unmatched
     * - matched
     * - converted
     * - deposits
     * - withdrawals
     *
     * @return Builder
     */
    public function client_status(string $value = '') :Builder
    {
        if (strlen($value) == 0) {
            return $this->builder;
        }

        $status_parameters = explode(',', $value);

        $status_array = [];
        
        $debit_or_withdrawal_array = [];

        if (in_array('all', $status_parameters)) {
            return $this->builder;
        }

        if (in_array('unmatched', $status_parameters)) {
            $status_array[] = BankTransaction::STATUS_UNMATCHED;
            // $this->builder->orWhere('status_id', BankTransaction::STATUS_UNMATCHED);
        }

        if (in_array('matched', $status_parameters)) {
            $status_array[] = BankTransaction::STATUS_MATCHED;
            // $this->builder->where('status_id', BankTransaction::STATUS_MATCHED);
        }

        if (in_array('converted', $status_parameters)) {
            $status_array[] = BankTransaction::STATUS_CONVERTED;
            // $this->builder->where('status_id', BankTransaction::STATUS_CONVERTED);
        }

        if (in_array('deposits', $status_parameters)) {
            $debit_or_withdrawal_array[] = 'CREDIT';
            // $this->builder->where('base_type', 'CREDIT');
        }

        if (in_array('withdrawals', $status_parameters)) {
            $debit_or_withdrawal_array[] = 'DEBIT';
            // $this->builder->where('base_type', 'DEBIT');
        }

        if(count($status_array) >=1) {
            $this->builder->whereIn('status_id', $status_array);
        }

        if(count($debit_or_withdrawal_array) >=1) {
            $this->builder->orWhereIn('base_type', $debit_or_withdrawal_array);
        }

        return $this->builder;
    }

    /**
     * Filters the list based on the status
     * archived, active, deleted.
     *
     * @param string filter
     * @return Builder
     */
    public function status(string $filter = '') : Builder
    {
        if (strlen($filter) == 0) {
            return $this->builder;
        }

        $table = 'bank_transactions';
        $filters = explode(',', $filter);

        return $this->builder->where(function ($query) use ($filters, $table) {
            $query->whereNull($table.'.id');

            if (in_array(parent::STATUS_ACTIVE, $filters)) {
                $query->orWhereNull($table.'.deleted_at');
            }

            if (in_array(parent::STATUS_ARCHIVED, $filters)) {
                $query->orWhere(function ($query) use ($table) {
                    $query->whereNotNull($table.'.deleted_at');

                    if (! in_array($table, ['users'])) {
                        $query->where($table.'.is_deleted', '=', 0);
                    }
                });
            }

            if (in_array(parent::STATUS_DELETED, $filters)) {
                $query->orWhere($table.'.is_deleted', '=', 1);
            }
        });
    }

    /**
     * Sorts the list based on $sort.
     *
     * @param string sort formatted as column|asc
     * @return Builder
     */
    public function sort(string $sort) : Builder
    {
        $sort_col = explode('|', $sort);

        if(!is_array($sort_col))
            return $this->builder;
        
        if($sort_col[0] == 'deposit')
            return $this->builder->where('base_type', 'CREDIT')->orderBy('amount', $sort_col[1]);

        if($sort_col[0] == 'withdrawal')
            return $this->builder->where('base_type', 'DEBIT')->orderBy('amount', $sort_col[1]);

        if($sort_col[0] == 'status')
            $sort_col[0] = 'status_id';

        if(in_array($sort_col[0],['invoices','expense']))
            return $this->builder;

        return $this->builder->orderBy($sort_col[0], $sort_col[1]);
    }

    /**
     * Returns the base query.
     *
     * @param int company_id
     * @param User $user
     * @return Builder
     * @deprecated
     */
    public function baseQuery(int $company_id, User $user) : Builder
    {

    }

    /**
     * Filters the query by the users company ID.
     *
     * @return Illuminate\Database\Query\Builder
     */
    public function entityFilter()
    {
        //return $this->builder->whereCompanyId(auth()->user()->company()->id);
        return $this->builder->company();
    }
}
