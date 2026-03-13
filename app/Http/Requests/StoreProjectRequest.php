<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'tags' => ['required', 'array', 'min:1', 'max:8'],
            'tags.*' => ['required', 'string', 'min:2', 'max:32'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags', []);

        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        $this->merge([
            'tags' => collect($tags)
                ->map(fn (mixed $tag): string => Str::of((string) $tag)->squish()->lower()->value())
                ->filter()
                ->unique()
                ->values()
                ->all(),
        ]);
    }

    /**
     * Get the validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Add a project title.',
            'description.required' => 'Add a short description for the project.',
            'image.required' => 'Upload a screenshot for the project.',
            'image.image' => 'The uploaded file must be an image.',
            'tags.required' => 'Add at least one tag.',
            'tags.min' => 'Add at least one tag.',
        ];
    }
}
