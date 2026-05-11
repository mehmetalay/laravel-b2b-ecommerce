<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentFilters
{
    protected $request;
    protected $builder;

    protected $filters = [
        'status',
        'date_from',
        'date_to',
        'refund_status',
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($builder)
    {
        $this->builder = $builder;

        foreach ($this->filters as $filter) {
            if (method_exists($this, $filter) && $this->request->filled($filter)) {
                $this->$filter($this->request->get($filter));
            }
        }

        if (!$this->request->filled('date_from')) {
            $this->date_from(Carbon::now()->subDays(30)->format('Y-m-d'));
        }

        if (!$this->request->filled('date_to')) {
            $this->date_to(Carbon::now()->format('Y-m-d'));
        }

        return $this->builder;
    }

    protected function status($value)
    {
        if (in_array($value, ['SUCCESS', 'FAILED'])) {
            return $this->builder->where('status', $value);
        }

        return $this->builder;
    }

    protected function date_from($value)
    {
        return $this->builder->where('created_at', '>=', $value . ' 00:00:00');
    }

    protected function date_to($value)
    {
        return $this->builder->where('created_at', '<=', $value . ' 23:59:59');
    }

    protected function refund_status($value)
    {
        if ($value === 'payment') {
            return $this->builder->whereNull('refund_status');
        }

        return $this->builder->where('refund_status', $value);
    }
}
