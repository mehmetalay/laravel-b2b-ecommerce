<?php

namespace App\Support;

use App\Services\CurrentAccountService;

class StockVisibility
{
    public static function canSee(): bool
    {
        if (additional_setting('show_stock') == 0) {
            return false;
        }

        if (auth('web')->check()) {
            $user = auth('web')->user();

            if ($user->hide_all_stock_quantities) {
                return false;
            }
        }

        if (auth('subdealer')->check()) {
            $account = app(CurrentAccountService::class)->currentAccount();

            if ($account->hide_all_stock_quantities) {
                return false;
            }
        }

        return true;
    }
}
