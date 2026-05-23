@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'block w-full rounded-lg border-slate-300 shadow-sm placeholder:text-slate-400 focus:border-amber-500 focus:ring-amber-500 disabled:bg-slate-50 disabled:text-slate-500',
]) }}>
