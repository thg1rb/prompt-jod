<x-guest-layout>
    <div class="p-6 pb-3 text-center">
        <div class="w-14 h-14 rounded-2xl bg-surface-subtle flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <h1 class="text-[22px] font-bold text-foreground tracking-tight">ยืนยันรหัสผ่าน</h1>
        <p class="text-sm text-text-muted mt-1">ยืนยันรหัสผ่านก่อนดำเนินการต่อ</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="p-6 pt-2 space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="__('รหัสผ่าน')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-full">
            {{ __('ยืนยัน') }}
        </x-primary-button>
    </form>
</x-guest-layout>