<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;
use App\Models\CampaignRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        ini_set('max_execution_time', '1000');
        ini_set('memory_limit', '-1');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Campaign::paginate(50);

        return view('backend.pages.campaigns.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.pages.campaigns.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampaignRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $campaign = Campaign::create([
                'name' => $validated['name'],
                'general_description' => $request->input('general_description'),
                'type' => $validated['type'],
                'sub_type' => $validated['sub_type'] ?? null,
                'use_date_filter' => $request->boolean('use_date_filter'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $validated['status'],
                'auto_apply' => $validated['auto_apply'],
            ]);

            $this->saveCampaignRules($campaign->id, $validated['rules']);
            $this->saveCampaignProducts($campaign->id, $request);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kampanya basariyla olusturuldu.',
                'redirect' => route('admin.campaigns.edit', [$campaign->id]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Bir hata olustu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        return view('backend.pages.campaigns.edit', compact('campaign'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampaignRequest $request, Campaign $campaign)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $campaign->update([
                'name' => $validated['name'],
                'general_description' => $request->input('general_description'),
                'type' => $validated['type'],
                'sub_type' => $validated['sub_type'] ?? null,
                'use_date_filter' => $request->boolean('use_date_filter'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $validated['status'],
                'auto_apply' => $validated['auto_apply'],
            ]);

            $campaign->rules()->delete();

            $this->saveCampaignRules($campaign->id, $validated['rules']);
            $this->saveCampaignProducts($campaign->id, $request);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kampanya basariyla guncellendi.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Bir hata olustu: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function loadPartial(Request $request, $partial)
    {
        $campaignId = $request->query('campaign_id');
        $campaign = $campaignId ? Campaign::with('rules')->find($campaignId) : null;

        $ruleData = null;
        if ($campaign && $campaign->rules->count() > 0) {
            $ruleData = $campaign->rules->first();
        }

        $html = view("backend.pages.campaigns.partials._{$partial}", compact('ruleData'))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Kampanya kurali basariyla yuklendi.',
            'html' => $html,
        ]);
    }

    protected function saveCampaignRules($campaignId, $rules)
    {
        foreach ($rules as $rule) {
            $extra = $rule['extra'] ?? [];

            if (isset($extra['gifts']) && !is_array($extra['gifts'])) {
                $extra['gifts'] = [$extra['gifts']];
            }

            CampaignRule::create([
                'campaign_id' => $campaignId,
                'rule_type' => $rule['rule_type'] ?? null,
                'extra' => $extra,
            ]);
        }
    }

    protected function saveCampaignProducts($campaignId, Request $request)
    {
        DB::table('campaign_products')->where('campaign_id', $campaignId)->delete();

        $products = $request->input('products', []);
        $brands = $request->input('brands', []);
        $categories = $request->input('categories', []);

        foreach ($products as $productId) {
            if (!is_numeric($productId)) {
                continue;
            }

            DB::table('campaign_products')->insert([
                'campaign_id' => $campaignId,
                'product_id' => $productId,
                'brand_id' => null,
                'category_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($brands as $brandId) {
            if (!is_numeric($brandId)) {
                continue;
            }

            DB::table('campaign_products')->insert([
                'campaign_id' => $campaignId,
                'product_id' => null,
                'brand_id' => $brandId,
                'category_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($categories as $catId) {
            if (!is_numeric($catId)) {
                continue;
            }

            DB::table('campaign_products')->insert([
                'campaign_id' => $campaignId,
                'product_id' => null,
                'brand_id' => null,
                'category_id' => $catId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
