<?php

namespace App\Rules;

use App\Models\SubDealer;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmailAcrossTables implements Rule
{
    protected $dealerId;
    protected $ignoreId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($dealerId, $ignoreId = null)
    {
        $this->dealerId = $dealerId;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $query = SubDealer::query()
            ->where('dealer_id', $this->dealerId)
            ->where('username', $value)
            ->whereNull('deleted_at');

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Bu kullanıcı adı zaten kayıtlıdır.';
    }
}
