<?php

namespace App\Application\DealerApplication\DTO;

class DealerApplicationData
{
    public function __construct(
        public string $companyName,
        public ?string $taxOffice,
        public ?string $taxNumber,
        public string $city,
        public string $district,
        public string $address,
        public string $authorizedNameSurname,
        public ?string $identityNumber,
        public ?string $phoneNumber,
        public string $mobilePhoneNumber,
        public ?string $faxNumber,
        public string $emailAddress,
        public ?string $webAddress,
        public string $ipAddress
    ) {}

    public static function fromRequestPayload(array $payload, string $ipAddress): self
    {
        return new self(
            companyName: mb_strtoupper((string) ($payload['company_name'] ?? ''), 'UTF-8'),
            taxOffice: self::nullableTrim($payload['tax_office'] ?? null),
            taxNumber: self::nullableUpper($payload['tax_number'] ?? null),
            city: mb_strtoupper((string) ($payload['city'] ?? ''), 'UTF-8'),
            district: mb_strtoupper((string) ($payload['district'] ?? ''), 'UTF-8'),
            address: mb_strtoupper((string) ($payload['address'] ?? ''), 'UTF-8'),
            authorizedNameSurname: mb_strtoupper((string) ($payload['authorized_name_surname'] ?? ''), 'UTF-8'),
            identityNumber: self::nullableTrim($payload['identity_number'] ?? null),
            phoneNumber: self::normalizePhone($payload['phone_number'] ?? null),
            mobilePhoneNumber: self::normalizePhone($payload['mobile_phone_number'] ?? '') ?? '',
            faxNumber: self::normalizePhone($payload['fax_number'] ?? null),
            emailAddress: trim((string) ($payload['email_address'] ?? '')),
            webAddress: self::nullableTrim($payload['web_address'] ?? null),
            ipAddress: $ipAddress
        );
    }

    public function toDatabasePayload(): array
    {
        return [
            'company_name' => $this->companyName,
            'tax_office' => $this->taxOffice,
            'tax_number' => $this->taxNumber,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address,
            'authorized_name_surname' => $this->authorizedNameSurname,
            'identity_number' => $this->identityNumber,
            'phone_number' => $this->phoneNumber,
            'mobile_phone_number' => $this->mobilePhoneNumber,
            'fax_number' => $this->faxNumber,
            'email_address' => $this->emailAddress,
            'web_address' => $this->webAddress,
            'ip_address' => $this->ipAddress,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private static function normalizePhone(mixed $value): ?string
    {
        $phone = trim((string) $value);
        if ($phone === '') {
            return null;
        }

        return str_replace(['(', ')', ' ', '-'], '', $phone);
    }

    private static function nullableTrim(mixed $value): ?string
    {
        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    private static function nullableUpper(mixed $value): ?string
    {
        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        return mb_strtoupper($string, 'UTF-8');
    }
}

