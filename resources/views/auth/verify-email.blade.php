<x-guest-layout>
    <div class="p-6 pb-3 text-center">
        <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <h1 class="text-[22px] font-bold text-foreground tracking-tight">ยืนยันอีเมล</h1>
        <p class="text-sm text-text-muted mt-1">คลิกลิงก์ในอีเมลเพื่อยืนยันที่อยู่ของคุณ</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mx-6 mb-4 p-3 bg-success-light border border-success/20 rounded-xl">
            <p class="text-sm text-success font-medium">ลิงก์ยืนยันถูกส่งไปยังอีเมลของคุณแล้ว</p>
        </div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="px-6 pt-2">
        @csrf
        <x-primary-button class="w-full">
            {{ __('ส่งลิงก์ยืนยันอีเมลอีกครั้ง') }}
        </x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="px-6 pt-2">
        @csrf
        <button type="submit" class="w-full py-2.5 text-sm text-text-muted hover:text-primary font-medium transition-colors">
            {{ __('ออกจากระบบ') }}
        </button>
    </form>

    <div class="px-6 pb-6 text-center mt-2">
        <a href="{{ route('dashboard') }}" class="text-sm text-primary hover:text-primary/80 font-medium transition-colors">
            {{ __('กลับไปหน้าหลัก') }}
        </a>
    </div>
</x-guest-layout>