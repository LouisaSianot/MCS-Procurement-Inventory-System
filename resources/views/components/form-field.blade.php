@php
    /*
    |-------------------------------------------------------------
    | Form Field — labelled input with error display + old() support
    |-------------------------------------------------------------
    | Usage:
    |   <x-form-field name="description" label="Description"
    |       type="text" :value="old('description', $order->description ?? '')"
    |       placeholder="Brief description of the order" />
    |
    | For selects/textarea pass `type="select"` or `type="textarea"`
    | and slot the options / body.
    */
    $name        = $name ?? '';
    $label       = $label ?? ucfirst($name);
    $type        = $type ?? 'text';
    $id          = $id ?? $name;
    $value       = $value ?? null;
    $placeholder = $placeholder ?? '';
    $required    = $required ?? false;
    $disabled    = $disabled ?? false;
    $help        = $help ?? null;
    $errors      = $errors ?? null;
@endphp

<div class="@if (isset($colSpan)) col-span-{{ $colSpan }} @endif">
    <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
        {{ $label }}
        @if ($required)<span class="text-rose-500">*</span>@endif
    </label>

    <div class="mt-1.5">
        @if ($type === 'select')
            <select id="{{ $id }}" name="{{ $name }}"
                    @if ($required) required @endif
                    @if ($disabled) disabled @endif
                    class="input {{ $errors && $errors->has($name) ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-500/20' : '' }}">
                {{ $slot ?? '' }}
            </select>
        @elseif ($type === 'textarea')
            <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows ?? 3 }}"
                      placeholder="{{ $placeholder }}"
                      @if ($required) required @endif
                      @if ($disabled) disabled @endif
                      class="input {{ $errors && $errors->has($name) ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-500/20' : '' }}">{{ $value }}</textarea>
        @else
            <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
                   value="{{ $value }}" placeholder="{{ $placeholder }}"
                   @if ($required) required @endif
                   @if ($disabled) disabled @endif
                   class="input {{ $errors && $errors->has($name) ? 'border-rose-400 focus:border-rose-400 focus:ring-rose-500/20' : '' }}">
        @endif
    </div>

    @if ($help)
        <p class="mt-1 text-xs text-slate-400">{{ $help }}</p>
    @endif

    @if ($errors && $errors->has($name))
        <p class="mt-1 text-xs font-medium text-rose-600">{{ $errors->first($name) }}</p>
    @endif
</div>
