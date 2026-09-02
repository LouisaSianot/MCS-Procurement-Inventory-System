<x-guest-layout>
    <p class="auth-eyebrow">MCS Purchasing &amp; Inventory</p>
    <h1 class="auth-title">Confirm your password</h1>
    <div class="auth-copy">{{ __('This is a secure area of the application. Please confirm your password before continuing.') }}</div>

    <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
        @csrf

        <!-- Password -->
        <div>
            <label for="password" class="auth-label">Password</label>
            <div class="auth-input-wrap"><i data-lucide="lock-keyhole" class="auth-input-icon" aria-hidden="true"></i><input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" /></div>
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <button type="submit" class="auth-button">{{ __('Confirm') }}</button>
    </form>
</x-guest-layout>