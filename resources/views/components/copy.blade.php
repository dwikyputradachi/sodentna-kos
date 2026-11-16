@props(['text'])
{{-- Copy-to-clipboard button; falls back to execCommand because plain http (e.g. .test) has no navigator.clipboard. --}}
<button type="button" x-data="{ ok: false }"
    @click.prevent="
        const t = @js($text);
        const done = () => { ok = true; setTimeout(() => ok = false, 1500) };
        if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(t).then(done) }
        else { const a = document.createElement('textarea'); a.value = t; a.style.position = 'fixed'; a.style.opacity = '0'; document.body.appendChild(a); a.select(); try { document.execCommand('copy'); done() } catch (e) {} document.body.removeChild(a) }"
    {{ $attributes->merge(['class' => 'rounded border border-slate-300 px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-50']) }}>
    <span x-show="!ok">Salin</span><span x-show="ok" x-cloak class="text-emerald-600">Tersalin</span>
</button>
