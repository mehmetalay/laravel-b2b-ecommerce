<?php

namespace App\Http\Controllers\Admin\Contract;

use Carbon\Carbon;
use App\Models\ContractSignature;
use App\Http\Controllers\Controller;

class ContractSignatureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $firstDate = request()->get('date_from', Carbon::now()->subDays(30)->format('Y-m-d'));
        $lastDate = request()->get('date_to', Carbon::now()->format('Y-m-d'));

        $items = ContractSignature::with(['template', 'dealer', 'subdealer'])
            ->when($name = request()->get('name'), function ($query) use ($name) {
                $query->whereHas('dealer', function ($query) use ($name) {
                    $query->where('name', 'like', '%' . $name . '%');
                })
                ->orWhereHas('subdealer', function ($query) use ($name) {
                    $query->where('name', 'like', '%' . $name . '%');
                });
            })
            ->when($actor_type = request()->get('actor_type'), function ($query) use ($actor_type) {
                $query->where('actor_type', $actor_type);
            })
            ->when($status = request()->get('status'), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->where(function ($query) use ($firstDate, $lastDate) {
                $query->whereBetween('signed_at', [
                    $firstDate . ' 00:00:00',
                    $lastDate . ' 23:59:59'
                ])
                ->orWhereNull('signed_at');
            })
            ->orderBy('signed_at', 'desc')
            ->paginate(100);

        return view('backend.pages.settings.contract.signatures.index', compact('items', 'firstDate', 'lastDate'));
    }
}
