@php
    $cartItems = $cartService->carts();

    $appliedIds = $cartItems
        ->where('is_campaign_gift', 0)
        ->pluck('campaign_id')
        ->filter()
        ->unique();

    $eligibleCampaigns = $allCampaigns->filter(function ($campaign) use ($campaignService, $cartItems) {
        return $campaignService->isCampaignEligibleForCart($cartItems, $campaign);
    })->values();

    $appliedCampaignIds = $cartItems->where('is_campaign_gift', 0)
        ->pluck('campaign_id')
        ->filter()
        ->unique()
        ->values();
@endphp

@if ($eligibleCampaigns->isEmpty())
    <div class="text-muted text-center py-4">
        Sepetiniz için uygun kampanya bulunmuyor.
    </div>
@else

    @if ($appliedCampaignIds->count())
        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-sm btn-danger text-white" data-selector="remove-all-campaigns">
                Tüm Kampanyaları İptal Et
            </button>
        </div>
    @endif

    @foreach($eligibleCampaigns as $campaign)
        @php
            $isApplied = $appliedIds->contains($campaign->id);

            $needsGiftSelection = $isApplied && $cartService->needsGiftSelection($campaign);

            $campaignProductIds = $campaign->products->pluck('id')->toArray();
            $matchingCartItems = $cartService->carts()->whereIn('product_id', $campaignProductIds);
            $explanation = $campaignService->getCartCampaignExplanation($campaign);
            $eligible = $campaignService->isCampaignEligibleForCart($cartService->carts(), $campaign);
        @endphp

        <div class="campaign-box border p-3 rounded mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <h5 class="mb-2">{{ $campaign->name }}</h5>

                @if ($needsGiftSelection)
                    <div class="text-warning small mt-1">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Hediye seçimi tamamlanmadı
                    </div>
                @endif

                @if ($eligible)
                    @if ($needsGiftSelection)
                        <button class="btn btn-sm btn-warning text-white" data-selector="select-gifts" data-campaign-id="{{ $campaign->id }}">
                            Hediye Seç
                        </button>

                    @elseif ($isApplied)
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" disabled>Uygulandı</button>

                            <button class="btn btn-sm btn-danger text-white"
                                    data-selector="remove-single-campaign"
                                    data-campaign-id="{{ $campaign->id }}">
                                İptal Et
                            </button>
                        </div>
                    @else
                        <button class="btn btn-sm btn-warning text-white" data-selector="apply-campaign" data-campaign-id="{{ $campaign->id }}">
                            Uygula
                        </button>
                    @endif
                @else
                    <button class="btn btn-sm btn-secondary" disabled>Uygun Değil</button>
                @endif
            </div>

            <div class="mb-2">
                <strong>Bu kampanya aşağıdaki ürünlerde geçerlidir:</strong>

                <ul class="list-group mt-2">
                    @forelse ($matchingCartItems as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>{{ $item->product->name }}</strong>
                            <small class="text-muted">({{ $item->quantity }} adet)</small>
                        </li>
                    @empty
                        <li class="list-group-item">
                            <span class="text-muted">Bu kampanyaya ait ürün sepette bulunmuyor.</span>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="text-muted">
                {!! $explanation !!}
            </div>
        </div>
    @endforeach

@endif