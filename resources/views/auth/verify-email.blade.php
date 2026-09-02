<x-guest-layout>
    <p class="auth-eyebrow">MCS Purchasing &amp; Inventory</p>
    <h1 class="auth-title">Verify your email</h1>
    <div class="auth-copy">{{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}</div>

    @if (session('status') == 'verification-link-sent')
    <div class="auth-status mt-6">
        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
    </div>
    @endif

    <div class="auth-form space-y-3 pt-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full">
            @csrf
            <button type="submit" class="auth-button">{{ __('Resend Verification Email') }}</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="auth-link block w-full text-center">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>