@props(['label', 'value', 'hint' => null])
<div class="rounded-lg border border-slate-200 bg-white p-4">
    <div class="text-xs text-slate-500">{{ $label }}</div>
    <div class="mt-1 text-xl font-semibold text-navy">{{ $value }}</div>
    @if ($hint)<div class="mt-0.5 text-xs text-slate-400">{{ $hint }}</div>@endif
</div>
