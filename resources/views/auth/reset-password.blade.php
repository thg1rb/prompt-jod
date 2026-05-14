<x-guest-layout>
    <div class="p-6 pb-3 text-center">
        <h1 class="text-[22px] font-bold text-foreground tracking-tight">ตั้งรหัสผ่านใหม่</h1>
        <p class="text-sm text-text-muted mt-1">ระบุรหัสผ่านใหม่ของคุณ</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="p-6 pt-4 space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email -->
        <div>
            <x-input-label for="email" :value="__('อีเมล')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('รหัสผ่านใหม่')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('ยืนยันรหัสผ่านใหม่')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">
            {{ __('ตั้งรหัสผ่านใหม่') }}
        </x-primary-button>
    </form>
</x-guest-layout>