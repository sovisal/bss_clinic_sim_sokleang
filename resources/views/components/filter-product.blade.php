<x-filter :url="$url">
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_category_id" class="filter-input" label="{{ __('form.product.category') }}">
            <option value="">{{ __('form.all') }}</option>
            @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected($category->id == request()->ft_category_id)>{{ d_obj($category, ['name_en', 'name_kh']) }}</option>
            @endforeach
        </x-form.select>
    </div>
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_type_id" class="filter-input" label="{{ __('form.product.type') }}">
            <option value="">{{ __('form.all') }}</option>
            @foreach ($types as $type)
            <option value="{{ $type->id }}" @selected($type->id == request()->ft_type_id)>{{ d_obj($type, ['name_en', 'name_kh']) }}</option>
            @endforeach
        </x-form.select>
    </div>
    {!! $slot !!}
</x-filter>