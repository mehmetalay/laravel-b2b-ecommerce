<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\{CartService, FileZipService, CurrentAccountService};
use App\Application\Order\Actions\{ApproveDealerOrderAction, PreviewOrderAction};
use App\Application\Order\CreateOrderAction;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $currentAccountService;

    public function __construct(CurrentAccountService $currentAccountService)
    {
        parent::__construct();
        $this->middleware('auth:web,subdealer');
        $this->currentAccountService = $currentAccountService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (auth('subdealer')->check() && !auth('subdealer')->user()->can_place_order) {
            return redirect()->back()->with('warning', 'Siparişler sayfasına erişim yetkiniz bulunmamaktadır.');
        }

        $startDate = request()->get('startDate', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = request()->get('endDate', Carbon::now()->format('Y-m-d'));

        $userQuery = $this->currentAccountService->userQuery();

        $baseQuery = Order::when(auth('web')->check() && auth('web')->user()->role === 'salesman', function ($query) use ($userQuery, $startDate, $endDate) {
                $query->where('plasiyer_id', $userQuery['plasiyer_id'])
                    ->where('status', 'approved')
                    ->when(request()->get('customerName'), function ($query) {
                        $query->whereHas('user', function ($q) {
                            $q->where('name', 'like', '%' . request()->get('customerName') . '%');
                        });
                    })
                    ->when($startDate, function ($query) use ($startDate) {
                        $query->whereDate('created_at', '>=', $startDate . ' 00:00:00');
                    })
                    ->when($endDate, function ($query) use ($endDate) {
                        $query->whereDate('created_at', '<=', $endDate . ' 23:59:59');
                    })
                    ->when(request()->get('status'), function ($query) {
                        $query->where('order_status_id', request()->get('status'));
                    })
                    ->orWhere(function ($query) use ($userQuery) {
                        $query->whereRelation('user', 'plasiyer1', $userQuery['plasiyer_id'])
                            ->orWhereRelation('user', 'plasiyer2', $userQuery['plasiyer_id'])
                            ->orWhereRelation('user', 'plasiyer3', $userQuery['plasiyer_id'])
                            ->orWhereRelation('user', 'plasiyer4', $userQuery['plasiyer_id'])
                            ->orWhereRelation('user', 'plasiyer5', $userQuery['plasiyer_id']);
                    });
            })
            ->when(auth('web')->check() && auth('web')->user()->role === 'dealer', function ($query) use ($userQuery) {
                $query->where('user_id', $userQuery['user_id'])
                    ->when($subDealerId = ($userQuery['sub_dealer_id'] ?? null), function ($query) use ($subDealerId) {
                        $query->where('sub_dealer_id', $subDealerId);
                    });
            })
            ->when(auth('subdealer')->check(), function ($query) use ($userQuery) {
                $query->where('sub_dealer_id', $userQuery['sub_dealer_id']);
            });

            $orders = (clone $baseQuery)
                ->orderBy('id', 'DESC')
                ->paginate(50);

            $currencyTotals = (clone $baseQuery)
                ->select(
                    'currency',
                    DB::raw('SUM(total_price) as total')
                )
                ->whereIn('currency', ['TL', 'USD'])
                ->groupBy('currency')
                ->pluck('total', 'currency');

        return view('orders.index', compact('orders', 'currencyTotals', 'startDate', 'endDate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartService $cartService, CreateOrderAction $createOrderAction)
    {
        return $createOrderAction->handle($cartService);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return view('orders.show', ['order' => $order]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function downloadAllImages(Order $order)
    {
        $orderProducts = $order->orderProducts()
            ->with('product')
            ->get()
            ->sortBy('product.code');

        $zipFilePath = app(FileZipService::class)->createImageZip($orderProducts, 'siparis');

        return response()->download($zipFilePath)->deleteFileAfterSend();
    }

    public function dealerApprove(Order $order, ApproveDealerOrderAction $approveDealerOrderAction)
    {
        $approved = $approveDealerOrderAction->handle(
            $order,
            (int) auth('web')->user()->current_account_id
        );

        if (!$approved) {
            return response()->json([
                'status' => 'error',
                'message' => 'Zaten işlenmiş',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'order_status' => 'Onaylandı',
            'message' => 'Sipariş başarıyla onaylandı.',
        ]);
    }

    public function preview(CartService $cartService, PreviewOrderAction $previewOrderAction)
    {
        return $previewOrderAction->handle($cartService, $this->currentAccountService);
    }
}

