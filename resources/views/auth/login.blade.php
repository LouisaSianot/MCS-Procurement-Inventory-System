<x-guest-layout>
    <p class="auth-eyebrow">MCS Purchasing &amp; Inventory</p>
    <h1 class="auth-title">Welcome back</h1>
    <x-auth-session-status class="auth-status mt-6" :status="session('status')" />
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <div>
            <label for="email" class="auth-label">Email address</label>
            <div class="auth-input-wrap"><i data-lucide="mail" class="auth-input-icon" aria-hidden="true"></i><input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" /></div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>
        <div>
            <label for="password" class="auth-label">Password</label>
            <div class="auth-input-wrap"><i data-lucide="lock-keyhole" class="auth-input-icon" aria-hidden="true"></i><input id="password" class="auth-input" type="password" name="password" required autocomplete="current-password" /></div>
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>
        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm text-blue-100">
                <input id="remember_me" type="checkbox" class="rounded border-white/50 bg-brand-800/30 text-white shadow-none focus:ring-2 focus:ring-white/70 focus:ring-offset-brand-700" name="remember">
                <span>{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-link whitespace-nowrap" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>
        <button type="submit" class="auth-button">{{ __('Log in') }}</button>
    </form>
    @if (Route::has('register'))
        <p class="mt-7 text-center text-sm text-blue-100">New to MCS? <a class="auth-link" href="{{ route('register') }}">Create an account</a></p>
    @endif
</x-guest-layout>
