<x-guest-layout>
    <p class="auth-eyebrow">MCS Purchasing &amp; Inventory</p>
    <h1 class="auth-title">Set your new password</h1>
    <form method="POST" action="{{ route('password.store') }}" class="auth-form">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="auth-label">Email address</label>
            <div class="auth-input-wrap"><i data-lucide="mail" class="auth-input-icon" aria-hidden="true"></i><input id="email" class="auth-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" /></div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="auth-label">New password</label>
            <div class="auth-input-wrap"><i data-lucide="lock-keyhole" class="auth-input-icon" aria-hidden="true"></i><input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" /></div>
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="auth-label">Confirm password</label>
            <div class="auth-input-wrap"><i data-lucide="shield-check" class="auth-input-icon" aria-hidden="true"></i><input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" /></div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
        </div>

        <button type="submit" class="auth-button">{{ __('Reset Password') }}</button>
    </form>
    <p class="mt-7 text-center"><a class="auth-link" href="{{ route('login') }}">Back to Login</a></p>
</x-guest-layout>