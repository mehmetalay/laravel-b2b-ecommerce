<?php

namespace App\Application\Cart\DTO;

class CartTotalsResult
{
    public function __construct(private array $totals)
    {
    }

    public function toArray(): array
    {
        return $this->totals;
    }
}

