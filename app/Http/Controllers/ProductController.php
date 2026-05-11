<?php

namespace App\Http\Controllers;

use App\Models\{Product, Attribute, AttributeValue};
use App\Application\Brand\Services\BrandService;
use App\Application\Category\Services\CategoryService;
use App\Services\{CurrencyService, CurrentAccountService};
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $currentAccountService;
    protected $categoryService;

    public function __construct(CurrentAccountService $currentAccountService, CategoryService $categoryService)
    {
        $this->middleware('auth:web,subdealer', ['except' => ['priceListPdf']]);
        $this->currentAccountService = $currentAccountService;
        $this->categoryService = $categoryService;
    }

    public function query($value, $type = null)
    {
        $currentAccount = $this->currentAccountService->currentAccount();
        $productRecordPerPage = additional_setting('product_record_per_page');

        $products = Product::with(['attributeValues', 'category'])
            ->select('products.*')
            ->where('products.status', 1)
            ->whereHas('brand', function ($query) {
                $query->where('status', 1);
            });

        if ($type === 'brand') {
            $products->whereHas('brand', function ($query) use ($value) {
                $query->where('id', $value->id);
            });
        }

        $productNameColonName = 'products.name';
        $stockColonName = 'products.stock';

        $hiddenProductPrefixes = auth('web')->check() && auth('web')->user()->role === 'salesman'
            ? ($currentAccount ? $currentAccount->hidden_product_prefixes : null)
            : (auth('web')->check() && auth('web')->user()->role === 'dealer' ? auth('web')->user()->hidden_product_prefixes : null);

        if ($hiddenProductPrefixes) {
            $prefixes = array_map('trim', explode(',', $hiddenProductPrefixes));

            $products->where(function($query) use ($prefixes, $productNameColonName) {
                foreach ($prefixes as $prefix) {
                    $query->where($productNameColonName, 'not like', $prefix . '%');
                }
            });
        }

        $minStock = request()->get('minStok');
        $maxStock = request()->get('maxStok');

        if (additional_setting('display_of_out_of_stock_products') == 0) {
            $minStockQuantity = auth('web')->check() ? (auth('web')->user()->min_stock_quantity ?? additional_setting('min_stock_quantity')) : additional_setting('min_stock_quantity');

            if ($minStockQuantity < 1) {
                $minStockQuantity = 1;
            }

            if (!is_null($minStock) && $minStock > $minStockQuantity) {
                $products->where($stockColonName, '>=', $minStock);
            } else {
                $products->where($stockColonName, '>=', $minStockQuantity);
            }

            if (!is_null($maxStock) && $maxStock < additional_setting('max_stock_quantity')) {
                $products->where($stockColonName, '<=', $maxStock);
            } elseif (additional_setting('max_stock_quantity')) {
                $products->where($stockColonName, '<=', additional_setting('max_stock_quantity'));
            }
        }

        // Kategori listele
        if ($type === 'list' && !is_null($value)) {
            $products->whereIn('products.category_id', $value);
        }

        if ($type === 'block' && !is_null($value)) {
            $products->whereHas('homepageBlocks', function ($query) use ($value) {
                $query->where('homepage_blocks.id', $value->id);
            });
        }

        // kelimeye göre listele
        if ($type === 'search' && !is_null($value)) {
            $products->where(function($query) use($value) {
                $like = "%{$value}%";
                $query->orWhere(function($q) use ($like) {
                    $q->where('products.name', 'like', $like)
                        ->orWhere('products.code', 'like', $like)
                        ->orWhere('products.code_2', 'like', $like)
                        ->orWhere('products.code_group', 'like', $like)
                        ->orWhereRelation('brand', 'name', 'like', $like);
                });
            });
        }

        $currencyService = app(CurrencyService::class);

        $usdRate = $currencyService->getFirstByCode('USD')->selling_price;
        $eurRate = $currencyService->getFirstByCode('EUR')->selling_price;

        $priceSql = "
            CASE products.price_1_currency
                WHEN 'TL' THEN products.price_1
                WHEN 'USD' THEN products.price_1 * {$usdRate}
                WHEN 'EUR' THEN products.price_1 * {$eurRate}
            END
        ";

        // Sırala
        $sortOptions = [
            'yeniden-eskiye' => ['products.erp_created_at', 'DESC'],
            'eskiden-yeniye' => ['products.erp_created_at', 'ASC'],
            'dusuk-fiyat' => [DB::raw($priceSql), 'ASC'],
            'yuksek-fiyat' => [DB::raw($priceSql), 'DESC'],
            'a-z-sirala' => ['products.code', 'ASC'],
            'z-a-sirala' => ['products.code', 'DESC'],
        ];

        if ($sortBy = request()->get('sirala')) {
            if (isset($sortOptions[$sortBy])) {
                $products->orderBy($sortOptions[$sortBy][0], $sortOptions[$sortBy][1]);
            }
        } else {
            $products->orderBy('products.name', 'ASC');
        }

        $groupByProductCode = auth('web')->check() &&
            (
                (auth('web')->user()->role === 'dealer' && auth('web')->user()->group_by_product_code) ||
                (auth('web')->user()->role === 'salesman' && $currentAccount && $currentAccount->group_by_product_code)
            );

        // Gruplandırma
        if ($groupByProductCode) {
            $products->groupBy('products.code_group');
        }

        if ($features = request()->get('ozellikler')) {
            $products = $products->whereHas('attributeValues', function ($query) use ($features) {
                foreach (explode(';', $features) as $feature) {
                    $featureArray = explode(':', $feature);
                    $attributeSlug = $featureArray[0];
                    $attributeValues = explode(',', $featureArray[1]);

                    $query->whereHas('attribute', function ($query) use ($attributeSlug) {
                        $query->where('slug', $attributeSlug);
                    })
                    ->whereIn('slug', $attributeValues);
                }
            });
        }

        if ($brands = request()->get('markalar')) {
            $brandValues = explode(',', $brands);
            $products = $products->whereHas('brand', function ($query) use ($brandValues) {
                $query->whereIn('name', $brandValues);
            });
        }

        $categories = request()->get('kategoriler');

        if ($type === 'all' && $categories) {
            $categoryValues = explode(',', $categories);
            $allCategoryIds = [];

            foreach ($categoryValues as $catSlug) {
                $category = $this->categoryService->getFirstBySlug($catSlug);

                if ($category) {
                    $descendants = $this->categoryService->getCategoryAndDescendantIds($category);
                    $allCategoryIds = array_merge($allCategoryIds, $descendants);
                }
            }

            $allCategoryIds = array_unique($allCategoryIds);
            $products = $products->whereIn('products.category_id', $allCategoryIds);
        }

        return $products->whereNotIn('products.category_id', hide_category_ids())->paginate($productRecordPerPage);
    }

    public function all()
    {
        $type = 'all';

        $products = $this->query(null, $type);

        $usedAttributes = [];
        $viewType = $this->setViewType();

        return view('products.list', compact('type', 'products', 'usedAttributes', 'viewType'));
    }

    public function list($slug)
    {
        $type = 'list';

        $category = $this->categoryService->getFirstBySlug($slug) ?? abort(404);

        if (!$category) {
            return redirect()->route('index');
        }

        $categoryIds = $this->categoryService->getCategoryAndDescendantIds($category);

        $products = $this->query($categoryIds, $type);

        $usedAttributes = Attribute::whereHas('attributeValues', function ($query) use ($category) {
            $query->whereHas('products', function ($query) use ($category) {
                $query->whereIn('products.id', $category->products->pluck('id'));
            });
        })
        ->with('attributeValues')
        ->get();

        $viewType = $this->setViewType();

        return view('products.list', compact('type', 'category', 'products', 'usedAttributes', 'viewType'));
    }

    public function search()
    {
        $type = 'search';

        $q = request()->get('q');

        if (empty($q)) {
            return redirect()->back();
        }

        $products = $this->query($q, $type);

        $usedAttributes = Attribute::whereHas('attributeValues', function ($query) {
                $query->whereHas('products', function ($query) {
                    $query->whereNotIn('category_id', hide_category_ids());
                });
            })
            ->with('attributeValues')
            ->get();

        $viewType = $this->setViewType();

        return view('products.list', compact('type', 'q', 'products', 'usedAttributes', 'viewType'));
    }

    public function brand($slug)
    {
        $type = 'brand';

        $brand = app(BrandService::class)->getFirstBySlug($slug) ?? abort(404);

        if (!$brand) {
            return redirect()->route('index');
        }

        $products = $this->query($brand, $type);

        $usedAttributes = Attribute::whereHas('attributeValues', function ($query) use ($brand) {
            $query->whereHas('products', function ($query) use ($brand) {
                $query->whereHas('brand', function ($query) use ($brand) {
                    $query->where('id', $brand->id);
                });
            });
        })
        ->with('attributeValues')
        ->get();

        $viewType = $this->setViewType();

        return view('products.list', compact('type', 'brand', 'products', 'usedAttributes', 'viewType'));
    }

    public function block($slug)
    {
        $type = 'block';

        $block = \App\Models\HomepageBlock::where('slug', $slug)->first() ?? abort(404);

        if (!$block) {
            return redirect()->route('index');
        }

        $products = $this->query($block, $type);

        $usedAttributes = Attribute::whereHas('attributeValues', function ($query) use ($block) {
            $query->whereHas('products', function ($query) use ($block) {
                $query->whereHas('homepageBlocks', function ($query) use ($block) {
                    $query->where('homepage_blocks.id', $block->id);
                });
            });
        })
        ->with('attributeValues')
        ->get();

        $viewType = $this->setViewType();

        return view('products.list', compact('type', 'block', 'products', 'usedAttributes', 'viewType'));
    }

    public function detail($slug)
    {
        $currentAccount = $this->currentAccountService->currentAccount();

        // merkez depo
        $product = Product::with(['attributeValues', 'category'])
            ->whereNotIn('category_id', hide_category_ids())
            ->whereSlug($slug)
            ->where('status', 1)
            ->whereHas('brand', function ($query) {
                $query->where('status', 1);
            })
            ->first() ?? abort(404);

        $relatedProducts = Product::with(['attributeValues', 'category'])
            ->where('products.id', '!=', $product->id)
            ->where('products.category_id', $product->category_id)
            ->where('products.status', 1)
            ->whereHas('brand', function ($query) {
                $query->where('status', 1);
            });

        $stockColonName = 'products.stock';

        if (additional_setting('display_of_out_of_stock_products') == 0) {
            $minStockQuantity = auth('web')->check() ? (auth('web')->user()->min_stock_quantity ?? additional_setting('min_stock_quantity')) : additional_setting('min_stock_quantity');

            if ($minStockQuantity < 1) {
                $minStockQuantity = 1;
            }

            $relatedProducts = $relatedProducts->where($stockColonName, '>=', $minStockQuantity);

            if ($maxStockQuantity = additional_setting('max_stock_quantity')) {
                $relatedProducts = $relatedProducts->where($stockColonName, '<=', $maxStockQuantity);
            }
        }

        $relatedProducts = $relatedProducts->inRandomOrder()->limit(20)->get();

        $campaigns = $product->activeCampaigns();

        return view('products.detail', compact('product', 'relatedProducts', 'campaigns'));
    }

    public function filter()
    {
        $pageType = request('page_type');
        $slug = request('slug');
        $route = null;

        if ($pageType === 'list') {
            $route = route('product.list', [$slug]);
        } else if ($pageType === 'all') {
            $route = route('product.all');
        } else if ($pageType === 'search' || $pageType === 'price_list_filter') {
            $route = $slug;
        } else if ($pageType === 'block') {
            $route = $slug;
        }

        $sorting = null;
        if (request('sorting')) {
            $sorting = '&sirala=' . request('sorting');
        }

        $minStock = null;
        if (request('minStock')) {
            $minStock = '&minStok=' . request('minStock');
        }

        $maxStock = null;
        if (request('maxStock')) {
            $maxStock = '&maxStok=' . request('maxStock');
        }

        $features = null;
		if ($requestFeatures = request('features')) {
            $queryParams = [];

            $attributeValues = AttributeValue::whereIn('slug', $requestFeatures)->get()->groupBy('attribute_id');

            foreach ($attributeValues as $attributeId => $values) {
                $attribute = Attribute::find($attributeId);
                if ($attribute) {
                    $queryParams[$attribute->slug] = $values->pluck('slug')->toArray();
                }
            }

            $features = '&ozellikler=' . collect($queryParams)->map(function ($values, $name) {
                return $name . ':' . implode(',', $values);
            })->implode(';');

            $s = '?';
        }

        $brands = null;
        if ($requestBrands = request('brands')) {
            $brands = '&markalar=' . implode(',', $requestBrands);
        }

        $categories = null;
        if ($requestCategories = request('categories')) {
            $categories = '&kategoriler=' . implode(',', $requestCategories);
        }

        $result = $sorting . $minStock . $maxStock . $features . $brands . $categories;

        $result = ltrim($result, '&');

        $separator = str_contains($route, '?') ? '&' : '?';
        return redirect()->to($route . ($result !== '' ? $separator . $result : ''));
    }

    protected function setViewType()
    {
        $viewType = request()->get('view');

        $allowed = ['grid', 'list'];

        if ($viewType && in_array($viewType, $allowed)) {
            session(['product_view_type' => $viewType]);
            return $viewType;
        } else {
            return session(
                'product_view_type',
                additional_setting('default_product_view_type') ?? 'grid'
            );
        }
    }
}


