<?php

namespace App\Filters;

use Illuminate\Http\Request;

class BrandFilters
{
    protected $request;
    protected $builder;
    protected $filters = ['name', 'status'];

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

        return $this->builder;
    }

    protected function name($value)
    {
        return $this->builder->where('name', 'like', "%{$value}%");
    }

    protected function status($value)
    {
        return $this->builder->where('status', $value);
    }
}
