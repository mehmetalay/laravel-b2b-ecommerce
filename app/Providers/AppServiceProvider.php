<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Campaign;
use App\Models\Permission;
use App\Models\OrderStatus;
use App\Models\PaymentPlan;
use App\Models\PaymentType;
use App\Models\CargoCompany;
use App\Models\SurveyAnswer;
use App\Services\CartService;
use App\Models\AttributeGroup;
use App\Application\Brand\Services\BrandService;
use App\Models\BankIntegration;
use App\Models\EntityLastUpdate;
use App\Observers\BrandObserver;
use App\Support\StockVisibility;
use App\Services\CampaignService;
use App\Application\Category\Services\CategoryService;
use App\Services\CurrencyService;
use App\Services\BackedUpCartService;
use App\Services\ThemeSettingService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use App\Services\CurrentAccountService;
use Illuminate\Support\ServiceProvider;
use App\Observers\AttributeGroupObserver;
use App\Application\Survey\Services\SurveyWorkflowService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require_once app_path('Helpers/Helpers.php');

        AttributeGroup::observe(AttributeGroupObserver::class);
        Brand::observe(BrandObserver::class);

        Blade::if('canSeeStock', function () {
            return StockVisibility::canSee();
        });

        view()->composer([
            'layouts.js'
        ], function ($view) {
            $survey = null;
            $hasParticipated = false;

            if (auth('web')->check() && auth('web')->user()->role === 'dealer') {
                $survey = app(SurveyWorkflowService::class)->getActiveSurvey();

                if ($survey) {
                    $hasParticipated = SurveyAnswer::where('survey_id', $survey->id)
                        ->where('dealer_id', auth('web')->user()->current_account_id)
                        ->exists();
                }
            }

            $view->with([
                'survey' => $survey,
                'hasParticipated' => $hasParticipated,
            ]);
        });

        view()->composer([
            'carts.index'
        ], function ($view) {
            $view->with([
                'allCampaigns' => Campaign::activeAndValid()->get(),
                'campaignService' => app(CampaignService::class),
            ]);
        });

        view()->composer([
            'layouts.app',
        ], function ($view) {
            $view->with('themeSetting', app(ThemeSettingService::class)->getFirst());
        });

        view()->composer([
            'layouts.modal',
            'admin.layouts.modal',
            'admin.catalog.products.index',
            'admin.catalog.products.create',
            'admin.catalog.products.edit',
        ], function ($view) {
            $view->with([
                'brands' => app(BrandService::class)->getActiveBrands(),
                'categories' => app(CategoryService::class)->getVisibleParentCategories()
            ]);
        });

        view()->composer([
            'admin.catalog.products.create',
            'admin.catalog.products.edit',
        ], function ($view) {
            $view->with([
                'brands' => app(BrandService::class)->getActiveBrands(),
                'attributeGroups' => $this->getAllActiveAttributeGroups()
            ]);
        });

        view()->composer([
            'admin.reports.rep-monthly-sales.index',
            'admin.reports.brand-targets.index',
            'admin.reports.brand-collection-targets.index',
            'admin.reports.monthly-top-products.index',
            'admin.payments.index'
        ], function ($view) {
            $view->with([
                'salesmans' => User::salesman()->active()->get()
            ]);
        });

        view()->composer([
            'admin.reports.brand-targets.index',
            'admin.reports.monthly-top-products.index',
            'admin.reports.monthly-brand-model-sales.index'
        ], function ($view) {
            $view->with([
                'categories' => app(CategoryService::class)->getAllActiveCategories()
            ]);
        });

        view()->composer([
            'layouts.app'
        ], function ($view) {
            $updates = $this->getAllEntityLastUpdates();

            $view->with([
                'customerLast' => $updates['customer']->first()->last_update_date ?? null,
                'productLast' => $updates['product']->first()->last_update_date ?? null,
                'imageLast' => $updates['image']->first()->last_update_date ?? null,
            ]);
        });

        view()->composer([
            'layouts.app',
            'admin.salesmans.create',
            'admin.salesmans.edit',
            'admin.current-accounts.edit'
        ], function ($view) {
            $view->with([
                'categories' => app(CategoryService::class)->getVisibleParentCategories()
            ]);
        });

        view()->composer([
            'payments.payment-link.page',
            'admin.payment-links.create',
            'admin.payment-links.edit',
            'admin.payments.index'
        ], function ($view) {
            $bankIntegrations = BankIntegration::with('installments')
                ->active()
                ->orderBy('name', 'ASC')
                ->get();

            $view->with([
                'bankIntegrations' => $bankIntegrations
            ]);
        });

        view()->composer([
            'admin.settings.pos-managements.index'
        ], function ($view) {
            $bankIntegrations = BankIntegration::with('installments')
                ->orderBy('name', 'ASC')
                ->get();

            $view->with([
                'bankIntegrations' => $bankIntegrations
            ]);
        });

        view()->composer([
            'layouts.modal'
        ], function ($view) {
            $cargoCompanies = $this->getAllActiveCargoCompanies();

            $view->with([
                'cargoCompanies' => $cargoCompanies
            ]);
        });

        view()->composer([
            'payments.page',
            'layouts.modal'
        ], function ($view) {
            $account = app(CurrentAccountService::class)->currentAccount();

            if ($account && $account->company_id) {
                $company_id = $account->company_id;
            } else {
                $company_id = additional_setting('default_company_id');
            }

            $bankIntegrations = BankIntegration::with('installments')
                ->where('company_id', $company_id)
                ->active()
                ->orderBy('name', 'ASC')
                ->get();

            $view->with([
                'bankIntegrations' => $bankIntegrations,
                'account_name' => $account?->name,
                'account_code' => $account?->code,
                'account_balance' => $account?->balance,
            ]);
        });

        view()->composer([
            'layouts.partials.header-top',
        ], function ($view) {
            $view->with([
                'USDExchangeRate' => app(CurrencyService::class)->getFirstByCode('USD')->selling_price,
                'EURExchangeRate' => app(CurrencyService::class)->getFirstByCode('EUR')->selling_price,
                'currentAccountService' => app(CurrentAccountService::class),
            ]);
        });

        view()->composer([
            'admin.settings.users.create',
            'admin.settings.users.edit'
        ], function ($view) {
            $view->with([
                'permissions' => $this->getAllActivePermissions()
            ]);
        });

        view()->composer([
            'admin.current-accounts.edit',
            'admin.settings.additional-settings.index'
        ], function ($view) {
            $view->with([
                'companies' => $this->getAllActiveCompanies()
            ]);
        });

        view()->composer([
            'admin.orders.index',
            'admin.orders.edit'
        ], function ($view) {
            $view->with([
                'orderStatuses' => $this->getAllActiveOrderStatuses()
            ]);
        });

        view()->composer([
            'carts.index'
        ], function ($view) {
            $view->with([
                'backedUpCarts' => app(BackedUpCartService::class)->getAllBackedUpCarts(),
                'paymentPlans' => $this->getAllActivePaymentPlans(),
                'paymentTypes' => $this->getAllActivePaymentTypes(),
            ]);
        });

        view()->composer([
            'carts.header',
            'carts.summary',
            'collections.cheques.create',
            'collections.promissories.create',
            'current-accounts.index',
            'exports.excel.cart',
            'layouts.app',
            'layouts.modal',
        ], function ($view) {
            $view->with('currentAccountService', app(CurrentAccountService::class));
        });

        view()->composer([
            'carts.header',
            'carts.list',
            'carts.index',
            'carts.summary',
            'exports.excel.cart',
        ], function ($view) {
            $view->with('cartService', app(CartService::class));
        });

        Blade::if('allowedPayment', function ($method, $product = null) {
            if (auth('web')->check() && auth('web')->user()->role === 'salesman') {
                return true;
            }

            // Cari bazlı kontrol
            $currentAccount = app(CurrentAccountService::class)->currentAccount();
            if (!$currentAccount) return false;

            $methods = explode(',', $currentAccount->allowed_payment_methods);
            if (!in_array($method, $methods)) {
                return false;
            }

            // Marka bazlı kontrol
            if ($product && $product->brand) {
                if (!$product->brand->isPaymentMethodAllowed($method)) {
                    return false;
                }
            }

            return true;
        });
    }

    private function getAllActivePaymentPlans()
    {
        return Cache::rememberForever('all_active_payment_plans', function () {
            return PaymentPlan::latest()->get();
        });
    }

    private function getAllActivePaymentTypes()
    {
        return Cache::rememberForever('all_active_payment_types', function () {
            return PaymentType::latest()->get();
        });
    }

    private function getAllActiveCompanies()
    {
        return Cache::rememberForever('all_active_companies', function () {
            return Company::active()->get();
        });
    }

    private function getAllActiveOrderStatuses()
    {
        return Cache::rememberForever('all_active_order_statuses', function () {
            return OrderStatus::orderBy('id', 'asc')->get();
        });
    }

    private function getAllActivePermissions()
    {
        return Cache::rememberForever('all_active_permissions', function () {
            return Permission::orderBy('name', 'asc')->get();
        });
    }

    private function getAllActiveAttributeGroups()
    {
        return Cache::rememberForever('all_active_attribute_groups', function () {
            return AttributeGroup::active()->orderBy('name')->get();
        });
    }

    private function getAllEntityLastUpdates()
    {
        return Cache::rememberForever('all_entity_last_updates', function () {
            return EntityLastUpdate::whereIn('entity_type', ['customer', 'product', 'image'])
                ->get()
                ->groupBy('entity_type');
        });
    }

    private function getAllActiveCargoCompanies()
    {
        return Cache::rememberForever('all_active_cargo_companies', function () {
            return CargoCompany::active()->orderBy('name')->get();
        });
    }
}


