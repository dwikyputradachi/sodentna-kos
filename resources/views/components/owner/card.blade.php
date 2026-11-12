@props(['title' => null, 'note' => null])
<section {{ $attributes->merge(['class' => 'rounded-lg border border-slate-200 bg-white']) }}>
    @if ($title)
        <div class="border-b border-slate-100 px-4 py-3">
            <h2 class="text-sm font-semibold text-navy">{{ $title }}</h2>
            @if ($note)<p class="text-xs text-slate-500">{{ $note }}</p>@endif
        </div>
    @endif
    <div class="overflow-x-auto">{{ $slot }}</div>
</section>
