@php echo "<?php"
@endphp namespace App\Http\Requests\Admin\{{ $modelWithNamespaceFromDefault }};
@php
    if($translatable->count() > 0) {
        $translatableColumns = $columns->filter(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
        $standardColumn = $columns->reject(function($column) use ($translatable) {
            return in_array($column['name'], $translatable->toArray());
        });
    }
@endphp

@if($translatable->count() > 0)use Brackets\Admin\TranslatableFormRequest;
@else
use Illuminate\Foundation\Http\FormRequest;
@endif
use Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;

@if($translatable->count() > 0)class Update{{ $modelBaseName }} extends TranslatableFormRequest
@else
class Update{{ $modelBaseName }} extends FormRequest
@endif
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('admin.{{ $modelDotNotation }}.edit', $this->{{ $modelVariableName }});
    }

@if($translatable->count() > 0)/**
     * Get the validation rules that apply to the requests untranslatable fields.
     *
     * @return  array
     */
    public function untranslatableRules() {
        return [
            @foreach($standardColumn as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
            @endforeach

        ];
    }

    /**
     * Get the validation rules that apply to the requests translatable fields.
     *
     * @return  array
     */
    public function translatableRules($locale) {
        return [
            @foreach($translatableColumns as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
            @endforeach

        ];
    }
@else/**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
@php
    $columns = collect($columns)->reject(function($column) {
        return $column['name'] == 'activated';
    })->toArray();
@endphp
        $rules = [
            @foreach($columns as $column)'{{ $column['name'] }}' => [{!! implode(', ', (array) $column['serverUpdateRules']) !!}],
            @endforeach

        ];

        if(Config::get('admin-auth.activations.enabled')) {
            $rules['activated'] = ['required', 'boolean'];
        }

        return $rules;
    }
@endif
}
