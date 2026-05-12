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
            'frontend.layouts.js'
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
            'frontend.pages.cart.index'
        ], function ($view) {
            $view->with([
                'allCampaigns' => Campaign::activeAndValid()->get(),
                'campaignService' => app(CampaignService::class),
            ]);
        });

        view()->composer([
            'frontend.layouts.app',
        ], function ($view) {
            $view->with('themeSetting', app(ThemeSettingService::class)->getFirst());
        });

        view()->composer([
            'frontend.layouts.modal',
            'backend.layouts.modal',
            'backend.pages.catalog.products.index',
            'backend.pages.catalog.products.create',
            'backend.pages.catalog.products.edit',
        ], function ($view) {
            $view->with([
                'brands' => app(BrandService::class)->getActiveBrands(),
                'categories' => app(CategoryService::class)->getVisibleParentCategories()
            ]);
        });

        view()->composer([
            'backend.pages.catalog.products.create',
            'backend.pages.catalog.products.edit',
        ], function ($view) {
            $view->with([
                'brands' => app(BrandService::class)->getActiveBrands(),
                'attributeGroups' => $this->getAllActiveAttributeGroups()
            ]);
        });

        view()->composer([
            'backend.pages.dashboard.reports.rep-monthly-sales.index',
            'backend.pages.dashboard.reports.brand-targets.index',
            'backend.pages.dashboard.reports.brand-collection-targets.index',
            'backend.pages.dashboard.reports.monthly-top-products.index',
            'backend.pages.payments.index'
        ], function ($view) {
            $view->with([
                'salesmans' => User::salesman()->active()->get()
            ]);
        });

        view()->composer([
            'backend.pages.dashboard.reports.brand-targets.index',
            'backend.pages.dashboard.reports.monthly-top-products.index',
            'backend.pages.dashboard.reports.monthly-brand-model-sales.index'
        ], function ($view) {
            $view->with([
                'categories' => app(CategoryService::class)->getAllActiveCategories()
            ]);
        });

        view()->composer([
            'frontend.layouts.app'
        ], function ($view) {
            $updates = $this->getAllEntityLastUpdates();

            $view->with([
                'customerLast' => $updates['customer']->first()->last_update_date ?? null,
                'productLast' => $updates['product']->first()->last_update_date ?? null,
                'imageLast' => $updates['image']->first()->last_update_date ?? null,
            ]);
        });

        view()->composer([
            'frontend.layouts.app',
            'backend.pages.dealers.salesmans.create',
            'backend.pages.dealers.salesmans.edit',
            'backend.pages.dealers.current-accounts.edit'
        ], function ($view) {
            $view->with([
                'categories' => app(CategoryService::class)->getVisibleParentCategories()
            ]);
        });

        view()->composer([
            'frontend.pages.checkout.payment-link.page',
            'backend.pages.payments.links.create',
            'backend.pages.payments.links.edit',
            'backend.pages.payments.index'
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
            'backend.pages.settings.pos-managements.index'
        ], function ($view) {
            $bankIntegrations = BankIntegration::with('installments')
                ->orderBy('name', 'ASC')
                ->get();

            $view->with([
                'bankIntegrations' => $bankIntegrations
            ]);
        });

        view()->composer([
            'frontend.layouts.modal'
        ], function ($view) {
            $cargoCompanies = $this->getAllActiveCargoCompanies();

            $view->with([
                'cargoCompanies' => $cargoCompanies
            ]);
        });

        view()->composer([
            'frontend.pages.checkout.page',
            'frontend.layouts.modal'
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
            'frontend.partials.header-top',
        ], function ($view) {
            $view->with([
                'USDExchangeRate' => app(CurrencyService::class)->getFirstByCode('USD')->selling_price,
                'EURExchangeRate' => app(CurrencyService::class)->getFirstByCode('EUR')->selling_price,
                'currentAccountService' => app(CurrentAccountService::class),
            ]);
        });

        view()->composer([
            'backend.pages.settings.users.create',
            'backend.pages.settings.users.edit'
        ], function ($view) {
            $view->with([
                'permissions' => $this->getAllActivePermissions()
            ]);
        });

        view()->composer([
            'backend.pages.dealers.current-accounts.edit',
            'backend.pages.settings.additional-settings.index'
        ], function ($view) {
            $view->with([
                'companies' => $this->getAllActiveCompanies()
            ]);
        });

        view()->composer([
            'backend.pages.orders.index',
            'backend.pages.orders.edit'
        ], function ($view) {
            $view->with([
                'orderStatuses' => $this->getAllActiveOrderStatuses()
            ]);
        });

        view()->composer([
            'frontend.pages.cart.index'
        ], function ($view) {
            $view->with([
                'backedUpCarts' => app(BackedUpCartService::class)->getAllBackedUpCarts(),
                'paymentPlans' => $this->getAllActivePaymentPlans(),
                'paymentTypes' => $this->getAllActivePaymentTypes(),
            ]);
        });

        view()->composer([
            'frontend.pages.cart.header',
            'frontend.pages.cart.summary',
            'collections.cheques.create',
            'collections.promissories.create',
            'current-accounts.index',
            'exports.excel.cart',
            'frontend.layouts.app',
            'frontend.layouts.modal',
        ], function ($view) {
            $view->with('currentAccountService', app(CurrentAccountService::class));
        });

        view()->composer([
            'frontend.pages.cart.header',
            'frontend.pages.cart.list',
            'frontend.pages.cart.index',
            'frontend.pages.cart.summary',
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


