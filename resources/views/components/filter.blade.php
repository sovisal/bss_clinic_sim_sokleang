@props([
    'url' => ''
])

<x-card :foot="false" body-class="px-0 tw-pb-1" header-class="pb-0 pt-1">
    <x-slot name="header">
        <h5><i class="bx bx-filter-alt"></i> Filter</h5>
    </x-slot>
    <form class="w-100 tw--mt-3" action="{!! $url !!}" method="get" id="form-filter">
        <div class="row justify-content-center">
            {!! $slot !!}
        </div>
    </form>
</x-card>
