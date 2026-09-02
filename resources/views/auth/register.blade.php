<x-guest-layout>
    <p class="auth-eyebrow">MCS Purchasing &amp; Inventory</p>
    <h1 class="auth-title">Create your account</h1>
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div>
            <label for="name" class="auth-label">Name</label>
            <div class="auth-input-wrap"><i data-lucide="user" class="auth-input-icon" aria-hidden="true"></i><input id="name" class="auth-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" /></div>
            <x-input-error :messages="$errors->get('name')" class="auth-error" />
        </div>
        <div>
            <label for="email" class="auth-label">Email address</label>
            <div class="auth-input-wrap"><i data-lucide="mail" class="auth-input-icon" aria-hidden="true"></i><input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" /></div>
            <x-input-error :messages="$errors->get('email')" class="auth-error" />
        </div>
        <div>
            <label for="password" class="auth-label">Password</label>
            <div class="auth-input-wrap"><i data-lucide="lock-keyhole" class="auth-input-icon" aria-hidden="true"></i><input id="password" class="auth-input" type="password" name="password" required autocomplete="new-password" /></div>
            <x-input-error :messages="$errors->get('password')" class="auth-error" />
        </div>
        <div>
            <label for="password_confirmation" class="auth-label">Confirm password</label>
            <div class="auth-input-wrap"><i data-lucide="shield-check" class="auth-input-icon" aria-hidden="true"></i><input id="password_confirmation" class="auth-input" type="password" name="password_confirmation" required autocomplete="new-password" /></div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
        </div>
        <button type="submit" class="auth-button">{{ __('Register') }}</button>
    </form>
    <p class="mt-7 text-center text-sm text-blue-100">Already registered? <a class="auth-link" href="{{ route('login') }}">Log in</a></p>
</x-guest-layout>
