<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Repositories\ContractRepository;
use App\Application\Contract\Validators\ContractFormValidator;
use App\Models\Contract;
use App\Models\ContractTemplate;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ContractPersistenceService
{
    public function __construct(
        private ContractFormValidator $contractFormValidator,
        private ContractPlaceholderRenderer $contractPlaceholderRenderer,
        private ContractRepository $contractRepository
    ) {}

    public function buildShowPayload(array $actorContext, ContractTemplate $template): array
    {
        $actor = $actorContext['actor'];
        $contract = $this->findContractByContext($actorContext);

        $data = [
            'customer_invoice_title' => $contract ? $contract->customer_invoice_title : $actor->name,
            'customer_invoice_address' => $contract
                ? $contract->customer_invoice_address
                : $actor->address
                    . ($actor->district ? ' ' . $actor->district : '')
                    . ($actor->province ? ' ' . $actor->province : ''),
            'phone' => $contract ? $contract->phone : $actor->phone,
            'fax' => $contract ? $contract->fax : null,
            'trade_registry_no' => $contract ? $contract->trade_registry_no : null,
            'tax_office' => $contract ? $contract->tax_office : null,
            'tax_number' => $contract ? $contract->tax_number : null,
            'company_official' => $contract ? $contract->company_official : null,
            'mobile_phone' => $contract ? $contract->mobile_phone : null,
            'email_address' => $contract ? $contract->email_address : $actor->email,
            'purchasing_officer' => $contract ? $contract->purchasing_officer : null,
            'purchase_mobile_phone' => $contract ? $contract->purchase_mobile_phone : null,
            'purchase_email_address' => $contract ? $contract->purchase_email_address : null,
            'payment_authority' => $contract ? $contract->payment_authority : null,
            'payment_authority_mobile_phone' => $contract ? $contract->payment_authority_mobile_phone : null,
            'payment_authority_email_address' => $contract ? $contract->payment_authority_email_address : null,
            'accounting_contact_name' => $contract ? $contract->accounting_contact_name : null,
            'accounting_gsm' => $contract ? $contract->accounting_gsm : null,
            'accounting_email' => $contract ? $contract->accounting_email : null,
            'monthly_payment_days' => $contract ? $contract->monthly_payment_days : null,
            'updated_at' => $contract ? $contract->updated_at : now(),
        ];

        $bankAccounts = $this->padToFive(
            $contract ? $contract->bankAccounts : collect(),
            fn ($i) => (object) ['bank_name' => null, 'branch' => null, 'account_no' => null, 'account_holder' => null, 'sort_order' => $i]
        );
        $emails = $this->padToFive(
            $contract ? $contract->emails : collect(),
            fn ($i) => (object) ['email' => null, 'sort_order' => $i]
        );
        $gsms = $this->padToFive(
            $contract ? $contract->gsms : collect(),
            fn ($i) => (object) ['gsm' => null, 'sort_order' => $i]
        );
        $shipLocations = $this->padToFive(
            $contract ? $contract->shipLocations : collect(),
            fn ($i) => (object) [
                'name' => null,
                'address' => null,
                'city' => null,
                'district' => null,
                'phone' => null,
                'fax' => null,
                'authorized_name' => null,
                'sort_order' => $i,
            ]
        );

        return [
            'data' => $data,
            'template' => $template,
            'actor_type' => $actorContext['type']->value,
            'actor_id' => $actorContext['route_actor_id'],
            'content' => $this->contractPlaceholderRenderer->render((string) $template->content, $actorContext, $contract),
            'bankAccounts' => $bankAccounts,
            'emails' => $emails,
            'gsms' => $gsms,
            'shipLocations' => $shipLocations,
        ];
    }

    public function store(array $actorContext, array $input): void
    {
        $validator = $this->contractFormValidator->validate($input);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $contract = $this->contractRepository->upsertByContext($actorContext, [
            'customer_invoice_title' => $input['customer_invoice_title'] ?? null,
            'customer_invoice_address' => $input['customer_invoice_address'] ?? null,
            'phone' => sanitize_phone_number($input['phone'] ?? null),
            'fax' => sanitize_phone_number($input['fax'] ?? null),
            'trade_registry_no' => $input['trade_registry_no'] ?? null,
            'tax_office' => $input['tax_office'] ?? null,
            'tax_number' => $input['tax_number'] ?? null,
            'company_official' => $input['company_official'] ?? null,
            'mobile_phone' => sanitize_phone_number($input['mobile_phone'] ?? null),
            'email_address' => $input['email_address'] ?? null,
            'purchasing_officer' => $input['purchasing_officer'] ?? null,
            'purchase_mobile_phone' => sanitize_phone_number($input['purchase_mobile_phone'] ?? null),
            'purchase_email_address' => $input['purchase_email_address'] ?? null,
            'payment_authority' => $input['payment_authority'] ?? null,
            'payment_authority_mobile_phone' => sanitize_phone_number($input['payment_authority_mobile_phone'] ?? null),
            'payment_authority_email_address' => $input['payment_authority_email_address'] ?? null,
            'accounting_contact_name' => $input['accounting_contact_name'] ?? null,
            'accounting_gsm' => sanitize_phone_number($input['accounting_gsm'] ?? null),
            'accounting_email' => $input['accounting_email'] ?? null,
            'monthly_payment_days' => $input['monthly_payment_days'] ?? null,
        ]);

        $now = now();

        $bankRows = [];
        foreach ($this->normalizeFive($input['bank_accounts'] ?? []) as $i => $row) {
            if ($this->isRowEmpty($row, ['bank_name', 'branch', 'account_no', 'account_holder'])) {
                continue;
            }

            $bankRows[] = [
                'contract_id' => $contract->id,
                'sort_order' => $i + 1,
                'bank_name' => $row['bank_name'] ?? null,
                'branch' => $row['branch'] ?? null,
                'account_no' => $row['account_no'] ?? null,
                'account_holder' => $row['account_holder'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $emailRows = [];
        foreach ($this->normalizeFive($input['emails'] ?? []) as $i => $row) {
            if ($this->isRowEmpty($row, ['email'])) {
                continue;
            }

            $emailRows[] = [
                'contract_id' => $contract->id,
                'sort_order' => $i + 1,
                'email' => $row['email'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $gsmRows = [];
        foreach ($this->normalizeFive($input['gsms'] ?? []) as $i => $row) {
            if ($this->isRowEmpty($row, ['gsm'])) {
                continue;
            }

            $gsmRows[] = [
                'contract_id' => $contract->id,
                'sort_order' => $i + 1,
                'gsm' => sanitize_phone_number($row['gsm'] ?? null),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $shipRows = [];
        foreach ($this->normalizeFive($input['ship_locations'] ?? []) as $i => $row) {
            if ($this->isRowEmpty($row, ['name', 'address', 'city', 'district', 'phone', 'fax', 'authorized_name'])) {
                continue;
            }

            $shipRows[] = [
                'contract_id' => $contract->id,
                'sort_order' => $i + 1,
                'name' => $row['name'] ?? null,
                'address' => $row['address'] ?? null,
                'city' => $row['city'] ?? null,
                'district' => $row['district'] ?? null,
                'phone' => sanitize_phone_number($row['phone'] ?? null),
                'fax' => sanitize_phone_number($row['fax'] ?? null),
                'authorized_name' => $row['authorized_name'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->contractRepository->replaceBankAccounts($contract, $bankRows);
        $this->contractRepository->replaceEmails($contract, $emailRows);
        $this->contractRepository->replaceGsms($contract, $gsmRows);
        $this->contractRepository->replaceShipLocations($contract, $shipRows);
    }

    public function findContractByContext(array $actorContext): ?Contract
    {
        return $this->contractRepository->findByContext($actorContext);
    }

    private function padToFive(Collection $items, callable $factory): Collection
    {
        $items = $items->values();
        for ($i = $items->count() + 1; $i <= 5; $i++) {
            $items->push($factory($i));
        }

        return $items->take(5)->values();
    }

    private function normalizeFive(array $values): array
    {
        $values = array_values($values);
        $values = array_slice($values, 0, 5);
        while (count($values) < 5) {
            $values[] = [];
        }

        return $values;
    }

    private function isRowEmpty(array $row, array $keys): bool
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return false;
            }
        }

        return true;
    }
}
