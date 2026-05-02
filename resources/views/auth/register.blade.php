<x-guest-layout>
    <!-- Page Header -->
    <div class="p-6 pb-4">
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
    <form method="POST" action="{{ route('register') }}" class="p-6 pt-4 space-y-4">
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
        <div>
            <x-input-label for="password" :value="__('รหัสผ่าน')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('ยืนยันรหัสผ่าน')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
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
