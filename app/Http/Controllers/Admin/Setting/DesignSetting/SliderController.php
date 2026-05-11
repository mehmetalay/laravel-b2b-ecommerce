<?php

namespace App\Http\Controllers\Admin\Setting\DesignSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\SliderRequest;
use App\Models\Slider;
use App\Repositories\SliderRepository;
use App\Services\SliderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SliderController extends Controller
{
    public function __construct(
        protected SliderService $service,
        protected SliderRepository $repository
    ) {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $type = request()->get('type') ?? 'slider';

        $items = Slider::where('type', $type)->orderBy('sort_order')->get();

        return view('admin.settings.design-settings.sliders.index', compact('items', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sliderSizes = collect(config('images.sizes'))
            ->only(['slider', 'payment_slider', 'category_slider', 'campaign_slider'])
            ->map(fn ($type) => [
                'desktop' => $type['desktop']['recommended_resolution'],
                'tablet' => $type['tablet']['recommended_resolution'],
                'mobile' => $type['mobile']['recommended_resolution'],
            ]);

        return view('admin.settings.design-settings.sliders.create', compact('sliderSizes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SliderRequest $request)
    {
        $slider = $this->service->store($request);

        return response()->json([
            'status' => 'success',
            'message' => 'Slider başarıyla eklendi.',
            'redirect' => route('admin.settings.design-settings.sliders.edit', $slider->id),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Slider $slider)
    {
        $type = $slider->type;

        $sliderSizes = collect(config('images.sizes'))
            ->only(['slider', 'payment_slider', 'category_slider', 'campaign_slider'])
            ->map(fn ($type) => [
                'desktop' => $type['desktop']['recommended_resolution'],
                'tablet' => $type['tablet']['recommended_resolution'],
                'mobile' => $type['mobile']['recommended_resolution'],
            ]);

        return view('admin.settings.design-settings.sliders.edit', compact('slider', 'type', 'sliderSizes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SliderRequest $request, Slider $slider)
    {
        $this->service->update($slider, $request);

        return response()->json([
            'status' => 'success',
            'message' => 'Slider güncellendi.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Slider $slider)
    {
        $this->service->delete($slider);

        return response()->json([
            'status' => 'success',
            'message' => 'Slider silindi.',
        ]);
    }

    public function sort(Request $request)
    {
        $value = $request->input('value');

        $this->service->updateSortOrders(
            $request->order,
            $value
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Sıralama güncellendi.',
        ]);
    }

    public function inlineUpdate(Request $request, Slider $slider)
    {
        $field = (string) $request->input('field', '');

        if (!in_array($field, ['status', 'sort_order'], true)) {
            return response()->json([
                'message' => 'Lütfen formdaki hataları kontrol edin.',
                'errors' => [
                    'field' => ['Geçersiz alan.'],
                ],
            ], 422);
        }

        if ($field === 'status') {
            $validator = Validator::make($request->all(), [
                'value' => 'required|in:0,1,true,false',
            ], [
                'value.required' => 'Durum zorunludur.',
                'value.in' => 'Durum geçersiz.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => [
                        'status' => $validator->errors()->get('value'),
                    ],
                ], 422);
            }

            $statusValue = (int) ((string) $request->input('value') === '1' || (string) $request->input('value') === 'true');
            $this->repository->update($slider, [
                'status' => $statusValue,
            ]);
        }

        if ($field === 'sort_order') {
            $validator = Validator::make($request->all(), [
                'value' => 'required|integer|min:1',
            ], [
                'value.required' => 'Sıra zorunludur.',
                'value.integer' => 'Sıra sayı olmalıdır.',
                'value.min' => 'Sıra en az 1 olmalıdır.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Lütfen formdaki hataları kontrol edin.',
                    'errors' => [
                        'sort_order' => $validator->errors()->get('value'),
                    ],
                ], 422);
            }

            $this->normalizeTypeSortOrder($slider, (int) $request->input('value'));
        }

        $slider->refresh();
        $message = $field === 'sort_order' ? 'Sıralama güncellendi.' : 'Güncellendi.';

        return response()->json([
            'status' => 'success',
            'success' => true,
            'message' => $message,
            'data' => $this->toTableRow($slider),
        ]);
    }

    public function bulk(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('sliders', 'id')->whereNull('deleted_at')],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->values();
        $action = (string) $validated['action'];

        if ($action === 'activate' || $action === 'deactivate') {
            $statusValue = $action === 'activate' ? 1 : 0;

            $sliders = Slider::query()
                ->whereNull('deleted_at')
                ->whereIn('id', $ids)
                ->get();

            $sliders->each(function (Slider $slider) use ($statusValue): void {
                if ((int) $slider->status !== $statusValue) {
                    $this->repository->update($slider, [
                        'status' => $statusValue,
                    ]);
                }
            });
        }

        if ($action === 'delete') {
            $sliders = Slider::query()
                ->whereNull('deleted_at')
                ->whereIn('id', $ids)
                ->get();

            $sliders->each(function (Slider $slider): void {
                $this->service->delete($slider);
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'İşlem başarıyla tamamlandı.',
        ]);
    }

    public function tableData(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $type = trim((string) $request->input('type', ''));
        $status = $request->input('status');
        $perPage = min(100, max(1, (int) $request->input('per_page', 50)));
        $page = max(1, (int) $request->input('page', 1));

        $items = Slider::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('type', 'like', "%{$search}%")
                        ->orWhere('link', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', function ($query) use ($type) {
                $query->where('type', $type);
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('status', (int) $status);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = $items->getCollection()
            ->map(fn (Slider $item) => $this->toTableRow($item))
            ->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
            ],
            'filters' => [
                'statusOptions' => [
                    ['value' => '1', 'label' => 'Aktif'],
                    ['value' => '0', 'label' => 'Pasif'],
                ],
                'typeOptions' => [
                    ['value' => 'slider', 'label' => 'Slider'],
                    ['value' => 'payment_slider', 'label' => 'Ödeme Slider'],
                    ['value' => 'category_slider', 'label' => 'Kategori Slider'],
                    ['value' => 'campaign_slider', 'label' => 'Kampanya Slider'],
                ],
            ],
        ]);
    }

    private function normalizeTypeSortOrder(Slider $slider, int $targetSortOrder): void
    {
        DB::transaction(function () use ($slider, $targetSortOrder): void {
            $sliders = Slider::query()
                ->whereNull('deleted_at')
                ->where('type', $slider->type)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            if ($sliders->isEmpty()) {
                return;
            }

            $currentIndex = $sliders->search(fn (Slider $item) => (int) $item->id === (int) $slider->id);
            if ($currentIndex === false) {
                return;
            }

            $movingSlider = $sliders->pull($currentIndex);
            if (!$movingSlider instanceof Slider) {
                return;
            }

            $total = $sliders->count() + 1;
            $clampedTarget = max(1, min($targetSortOrder, $total));
            $insertIndex = $clampedTarget - 1;

            $sliders->splice($insertIndex, 0, [$movingSlider]);

            $sliders->values()->each(function (Slider $item, int $index): void {
                $normalizedSortOrder = $index + 1;
                if ((int) $item->sort_order === $normalizedSortOrder) {
                    return;
                }

                Slider::query()
                    ->whereKey($item->id)
                    ->update(['sort_order' => $normalizedSortOrder]);
            });
        });
    }

    private function toTableRow(Slider $slider): array
    {
        return [
            'id' => $slider->id,
            'admin_image' => $slider->admin_image,
            'type' => $slider->type,
            'type_text' => $slider->type_text,
            'status' => $slider->status ? 'Aktif' : 'Pasif',
            'status_value' => (int) $slider->status,
            'sort_order' => (int) $slider->sort_order,
            'edit_url' => route('admin.settings.design-settings.sliders.edit', [$slider->id]),
            'delete_url' => route('admin.settings.design-settings.sliders.destroy', [$slider->id]),
            'inline_update_url' => url('/admin/api/sliders/' . $slider->id . '/inline'),
        ];
    }
}
