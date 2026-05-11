<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use App\Application\Brand\Services\BrandService;
use App\Services\SliderService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\HomepageBlockService;

class HomepageController extends Controller
{
    public function __construct(
        protected HomepageBlockService $blockService,
        protected SliderService $sliderService,
        protected BrandService $brandService
    ) {
        $this->middleware('auth:web,subdealer');
    }

    public function index(Request $request)
    {
        $sections = $request->input('sections', []);

        $locale = app()->getLocale();
        $device = device_type();

        $response = ['status' => 'success'];

        if (in_array('sliders', $sections)) {
            $response['sliders'] = $this->sliders($locale, $device);
        }

        if (in_array('payments', $sections)) {
            $response['payments'] = $this->payments($locale, $device);
        }

        if (in_array('categories', $sections)) {
            $response['categories'] = $this->categories($locale, $device);
        }

        if (in_array('campaigns', $sections)) {
            $response['campaigns'] = $this->campaigns($locale, $device);
        }

        if (in_array('brands', $sections)) {
            $response['brands'] = $this->brands();
        }

        if (in_array('blocks', $sections)) {
            $response['blocks'] = $this->blocks($locale);
        }

        return response()->json($response);
    }

    protected function sliders($locale, $device)
    {
        $type = 'slider';

        return Cache::remember(
                "sliders:html:{$locale}:{$device}:{$type}",
                now()->addMinutes(30),
                function () use ($device, $type) {

                    $sliders = $this->sliderService->getActiveByType($type) ?? collect();

                    if ($sliders->isEmpty()) {
                        return '';
                    }

                    return view('homepage.partials.sliders', compact('sliders', 'device'))->render();
                }
            );
    }

    protected function payments($locale, $device)
    {
        $type = 'payment_slider';

        return Cache::remember(
                "sliders:html:{$locale}:{$device}:{$type}",
                now()->addMinutes(30),
                function () use ($device, $type) {

                    $sliders = $this->sliderService->getActiveByType($type) ?? collect();

                    if ($sliders->isEmpty()) {
                        return '';
                    }

                    return view('homepage.partials.payments', compact('sliders', 'device'))->render();
                }
            );
    }

    protected function categories($locale, $device)
    {
        $type = 'category_slider';

        return Cache::remember(
                "sliders:html:{$locale}:{$device}:{$type}",
                now()->addMinutes(30),
                function () use ($device, $type) {

                    $sliders = $this->sliderService->getActiveByType($type) ?? collect();

                    if ($sliders->isEmpty()) {
                        return '';
                    }

                    return view('homepage.partials.categories', compact('sliders', 'device'))->render();
                }
            );
    }

    protected function campaigns($locale, $device)
    {
        $type = 'campaign_slider';

        return Cache::remember(
                "sliders:html:{$locale}:{$device}:{$type}",
                now()->addMinutes(30),
                function () use ($device, $type) {

                    $sliders = $this->sliderService->getActiveByType($type) ?? collect();

                    if ($sliders->isEmpty()) {
                        return '';
                    }

                    return view('homepage.partials.campaigns', compact('sliders', 'device'))->render();
                }
            );
    }

    protected function brands()
    {
        $brands = $this->brandService->getActiveBrandsWithImage();

        if ($brands->isEmpty()) {
            return '';
        }

        return view('homepage.partials.brands', compact('brands'))->render();
    }

    protected function blocks($locale)
    {
        return Cache::remember("homepageblock:html:{$locale}", now()->addMinutes(30), function () {
                $blocks = $this->blockService->getActiveHomepageBlocks();

                if ($blocks->isEmpty()) {
                    return '';
                }
                
                return view('homepage.partials.blocks', compact('blocks'))->render();
            });
    }
}
