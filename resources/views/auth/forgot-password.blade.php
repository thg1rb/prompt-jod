<x-guest-layout>
    <!-- Page Header -->
    <div class="p-6 pb-4 text-center">
        <h1 class="text-2xl font-bold text-foreground">ลืมรหัสผ่าน?</h1>
        <p class="text-sm text-muted-foreground mt-1">ไม่เป็นไร เราจะส่งลิงก์รีเซ็ตรหัสผ่านให้คุณ</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="px-6" :status="session('status')" />

    <!-- Email Form -->
    <form method="POST" action="{{ route('password.email') }}" class="p-6 pt-4 space-y-4">
        @csrf

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('อีเมล')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Submit Button -->
        <x-primary-button class="w-full">
            {{ __('ส่งลิงก์รีเซ็ตรหัสผ่าน') }}
        </x-primary-button>
    </form>

    <!-- Back to Login -->
    <div class="px-6 pb-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-muted-foreground hover:text-foreground">
            ← {{ __('กลับไปหน้าเข้าสู่ระบบ') }}
        </a>
    </div>
</x-guest-layout>
