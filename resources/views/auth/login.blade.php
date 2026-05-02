<x-guest-layout>
    <!-- Page Header -->
    <div class="p-6 pb-4 text-center">
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
    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }" class="p-6 pt-4 space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('อีเมล')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="relative">
            <x-input-label for="password" :value="__('รหัสผ่าน')" />
            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" class="w-full px-4 py-2.5 bg-background border border-border rounded-lg text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors pr-10">
            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-9 text-muted-foreground hover:text-foreground">
                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                <svg x-show="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
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
