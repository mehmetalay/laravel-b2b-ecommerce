<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Application\Category\Services\CategoryService;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('category') ? $this->route('category')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($id) {
                    $query->when($id, function($query) use ($id) {
                            $query->where('id', '!=', $id);
                        });
                    return $query;
                })
            ],
            'slug' => 'nullable',
            'image' => 'nullable|image',
            'parent_id' => 'nullable',
            'status' => 'nullable|boolean',
            'sort_order' => 'nullable',
            'stock_display_limit' => 'nullable|integer|min:1',
        ];
    }

    protected function prepareForValidation()
    {
        $slug = str_slug($this->name);

        if ($this->filled('parent_id')) {
            $slug = app(CategoryService::class)->buildFullSlug($this->parent_id, $slug);
        }

        $this->merge([
            'name' => str_upper($this->name),
            'slug' => $slug,
            'status' => $this->has('status') ? 1 : 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adı zorunludur.',
            'name.max' => 'Kategori adı 255 karakterden uzun olamaz.',
            'name.unique' => 'Bu kategori adı zaten kayıtlıdır.',
            'image.image' => 'Yüklenen dosya bir resim olmalıdır.',
            'stock_display_limit.integer' => 'Sadece sayısal bir değer olmalıdır.',
            'stock_display_limit.min' => '1’den küçük olamaz.',
        ];
    }
}


