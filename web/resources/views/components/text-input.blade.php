@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-surface text-ink border-line focus:border-accent focus:ring-accent rounded-md shadow-sm']) !!}>
