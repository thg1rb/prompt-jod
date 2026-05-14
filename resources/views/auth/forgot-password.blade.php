<x-guest-layout>
    <div class="p-6 pb-3 text-center">
        <h1 class="text-[22px] font-bold text-foreground tracking-tight">ลืมรหัสผ่าน?</h1>
        <p class="text-sm text-text-muted mt-1">ไม่เป็นไร เราจะส่งลิงก์รีเซ็ตรหัสผ่านให้คุณ</p>
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
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-primary font-medium transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            กลับไปหน้าเข้าสู่ระบบ
        </a>
    </div>
</x-guest-layout>