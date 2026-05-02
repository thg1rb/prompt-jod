<x-guest-layout>
    <!-- Page Header -->
    <div class="p-6 pb-4 text-center">
        <h1 class="text-2xl font-bold text-foreground">สร้างบัญชีใหม่</h1>
        <p class="text-sm text-muted-foreground mt-1">เริ่มต้นใช้งานด้วยบัญชีฟรีของคุณวันนี้</p>
    </div>

    <!-- Google OAuth Button -->
    <div class="px-6 pb-4">
        <x-google-button type="register" />
    </div>

    <!-- Divider -->
    <div class="px-6">
        <x-divider />
    </div>

    <!-- Registration Form -->
    <form method="POST" action="{{ route('register') }}" x-data="{ showPassword: false, showPasswordConfirm: false }" class="p-6 pt-4 space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('ชื่อ')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('อีเมล')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="relative">
            <x-input-label for="password" :value="__('รหัสผ่าน')" />
            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" class="w-full px-4 py-2.5 bg-background border border-border rounded-lg text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors pr-10">
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

        <!-- Confirm Password -->
        <div class="relative">
            <x-input-label for="password_confirmation" :value="__('ยืนยันรหัสผ่าน')" />
            <input id="password_confirmation" :type="showPasswordConfirm ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="w-full px-4 py-2.5 bg-background border border-border rounded-lg text-foreground placeholder-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors pr-10">
            <button type="button" @click="showPasswordConfirm = !showPasswordConfirm" class="absolute right-3 top-9 text-muted-foreground hover:text-foreground">
                <svg x-show="!showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
                <svg x-show="showPasswordConfirm" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </button>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <!-- Submit Button -->
        <x-primary-button class="w-full">
            {{ __('สมัครสมาชิก') }}
        </x-primary-button>
    </form>

    <!-- Login Link -->
    <div class="px-6 pb-6 text-center">
        <span class="text-sm text-muted-foreground">
            {{ __('มีบัญชีอยู่แล้ว?') }}
            <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">
                {{ __('เข้าสู่ระบบ') }}
            </a>
        </span>
    </div>
</x-guest-layout>
