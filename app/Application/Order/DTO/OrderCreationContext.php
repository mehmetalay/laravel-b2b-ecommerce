<?php

namespace App\Application\Order\DTO;

use Illuminate\Support\Collection;

class OrderCreationContext
{
    public Collection $appliedCampaignIds;

    public function __construct(
        public mixed $currentAccount,
        public array $userQuery,
        public Collection $carts,
        public string $deliveryType,
        public mixed $shippingAddressId = null,
        public mixed $paymentPlanId = 1,
        public mixed $paymentTypeId = 1,
        public mixed $cargoCompanyId = null,
        public ?string $warehouseName = null,
        public ?string $pickupPerson = null,
        public ?string $transitNote = null,
        public ?string $explanation = null,
        public ?string $ipAddress = null,
        public int $sendEmail = 0,
        public int $sendSms = 0,
        public ?string $shippingAddressSnapshot = null,
        public ?array $campaignSnapshot = null,
        public array $groupCurrencyStatus = [],
        ?Collection $appliedCampaignIds = null
    ) {
        $this->appliedCampaignIds = $appliedCampaignIds ?: new Collection();
    }
}
