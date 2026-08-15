@props(['status' => null, 'class' => ''])

@php
$statusText = is_string($status) ? trim($status) : (string) ($status ?? 'Unknown');
$normalized = strtolower($statusText);

$palette = match (true) {
str_contains($normalized, 'approved') || str_contains($normalized, 'completed') || str_contains($normalized, 'received') || str_contains($normalized, 'in stock') => [
'bg' => 'bg-emerald-50',
'text' => 'text-emerald-700',
'ring' => 'ring-emerald-200',
],
str_contains($normalized, 'pending') || str_contains($normalized, 'draft') || str_contains($normalized, 'ordered') || str_contains($normalized, 'not submitted') => [
'bg' => 'bg-amber-50',
'text' => 'text-amber-700',
'ring' => 'ring-amber-200',
],
str_contains($normalized, 'rejected') || str_contains($normalized, 'out of stock') || str_contains($normalized, 'error') || str_contains($normalized, 'not approved') => [
'bg' => 'bg-rose-50',
'text' => 'text-rose-700',
'ring' => 'ring-rose-200',
],
str_contains($normalized, 'low stock') || str_contains($normalized, 'partially') || str_contains($normalized, 'warning') => [
'bg' => 'bg-orange-50',
'text' => 'text-orange-700',
'ring' => 'ring-orange-200',
],
str_contains($normalized, 'info') || str_contains($normalized, 'delivery') || str_contains($normalized, 'submitted') => [
'bg' => 'bg-sky-50',
'text' => 'text-sky-700',
'ring' => 'ring-sky-200',
],
default => [
'bg' => 'bg-slate-100',
'text' => 'text-slate-700',
'ring' => 'ring-slate-200',
],
};
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ' . $palette['bg'] . ' ' . $palette['text'] . ' ' . $palette['ring'] . ' ' . $class]) }}>
    {{ $statusText ?: 'Unknown' }}
</span>