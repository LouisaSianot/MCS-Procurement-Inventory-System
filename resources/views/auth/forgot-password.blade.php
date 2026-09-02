<x-guest-layout>
    <p class="auth-eyebrow">MCS Purchasing &amp; Inventory</p>
    <h1 class="auth-title">Reset your password</h1>
    <div class="auth-copy">{{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</div>
    <x-auth-session-status class="auth-status mt-6" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <div>
            <label for="email" class="auth-label">Email address</label>
            <div class="auth-input-wrap"><i data-lucide="mail" class="auth-input-icon" aria-hidden="true"></i><input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus /></div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>
        <button type="submit" class="auth-button">{{ __('Email Password Reset Link') }}</button>
    </form>
    <p class="mt-7 text-center"><a class="auth-link" href="{{ route('login') }}">Back to Login</a></p>
</x-guest-layout>
