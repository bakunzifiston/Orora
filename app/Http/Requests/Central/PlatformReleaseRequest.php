<?php

namespace App\Http\Requests\Central;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category' => ['required', Rule::in(array_keys(config('releases.categories')))],
            'released_on' => ['required', 'date'],
            'module' => ['nullable', Rule::in(collect(config('workspace_access.modules', []))->pluck('key')->all())],
            // Internal dashboard paths only (e.g. /milk/distribution) — no external or protocol-relative URLs.
            'link_path' => ['nullable', 'string', 'max:255', 'regex:#^/(?!/)[A-Za-z0-9\-._~/?=&%\#]*$#'],
            'audience' => ['required', Rule::in(array_keys(config('releases.audiences')))],
            'tenant_ids' => ['nullable', 'array', 'required_if:audience,selected'],
            'tenant_ids.*' => ['string', Rule::exists('central.tenants', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'link_path.regex' => __('Use a dashboard path that starts with /, for example /milk/distribution.'),
            'tenant_ids.required_if' => __('Choose at least one tenant, or target all tenants.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'module' => $this->filled('module') ? $this->input('module') : null,
            'link_path' => $this->filled('link_path') ? trim((string) $this->input('link_path')) : null,
            'tenant_ids' => $this->input('audience') === 'selected' ? array_values(array_filter((array) $this->input('tenant_ids', []))) : [],
        ]);
    }
}
