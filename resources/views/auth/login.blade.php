<x-guest-layout>
    <!-- Page Header -->
    <div class="p-6 pb-4">
        <h1 class="text-2xl font-bold text-foreground">ยินดีต้อนรับกลับ</h1>
        <p class="text-sm text-muted-foreground mt-1">เข้าสู่ระบบเพื่อดำเนินการต่อ</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="px-6" :status="session('status')" />

    <!-- Google OAuth Button -->
    <div class="px-6 pb-4">
        <x-google-button type="login" />
    </div>

    <!-- Divider -->
    <div class="px-6">
        <x-divider />
    </div>

    <!-- Email/Password Form -->
    <form method="POST" action="{{ route('login') }}" class="p-6 pt-4 space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('อีเมล')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('รหัสผ่าน')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-border text-primary focus:ring-ring">
                <span class="text-sm text-muted-foreground">{{ __('จดจำฉัน') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">
                    {{ __('ลืมรหัสผ่าน?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <x-primary-button class="w-full">
            {{ __('เข้าสู่ระบบ') }}
        </x-primary-button>
    </form>

    <!-- Register Link -->
    <div class="px-6 pb-6 text-center">
        <span class="text-sm text-muted-foreground">
            {{ __('ยังไม่มีบัญชี?') }}
            <a href="{{ route('register') }}" class="font-medium text-primary hover:underline">
                {{ __('สมัครสมาชิก') }}
            </a>
        </span>
    </div>
</x-guest-layout>
