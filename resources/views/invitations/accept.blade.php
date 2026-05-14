<x-guest-layout>
    <div class="p-6 pb-4">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-foreground tracking-tight mb-2">เชิญเข้าร่วมกระเป๋าเงิน</h2>
            <p class="text-text-muted text-sm">
                คุณต้องการเข้าร่วมกระเป๋าเงิน <strong class="text-foreground">{{ $wallet->name }}</strong> ของ <strong class="text-foreground">{{ $owner->name }}</strong> ใช่หรือไม่?
            </p>
        </div>

        <div class="bg-surface-subtle rounded-2xl p-4 mb-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="h-11 w-11 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold ring-1 ring-primary/20">
                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-semibold text-foreground">{{ $owner->name }}</div>
                    <div class="text-xs text-text-muted">{{ $owner->email }}</div>
                </div>
            </div>
            <div class="text-sm text-text-muted">
                <span class="font-semibold text-foreground">{{ $wallet->name }}</span>
                <span class="mx-1.5">•</span>
                <span>{{ $wallet->type->getLabel() }}</span>
            </div>
        </div>

        @if(!$isLoggedIn)
            <div class="bg-warning-light border border-warning/20 rounded-xl p-4 mb-4">
                <p class="text-sm text-warning font-medium">
                    กรุณาเข้าสู่ระบบก่อนเข้าร่วมกระเป๋าเงิน
                </p>
            </div>
            <a href="{{ route('login') }}" class="block w-full text-center px-4 py-3 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl font-semibold text-sm transition-colors shadow-sm">
                เข้าสู่ระบบ
            </a>
        @elseif($isAlreadyMember)
            <div class="bg-success-light border border-success/20 rounded-xl p-4 mb-4">
                <p class="text-sm text-success font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    คุณเป็นสมาชิกของกระเป๋าเงินนี้แล้ว
                </p>
            </div>
            <a href="{{ route('wallets.show', $wallet) }}" class="block w-full text-center px-4 py-3 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl font-semibold text-sm transition-colors shadow-sm">
                ไปยังกระเป๋าเงิน
            </a>
        @elseif(auth()->check() && auth()->user()->isFree())
            <div class="bg-primary-light border border-primary/20 rounded-xl p-4 mb-4 text-center">
                <p class="text-sm text-foreground mb-3 font-medium">กรุณาสมัครสมาชิก Premium เพื่อเข้าร่วมกระเป๋าเงินแชร์</p>
                <button onclick="window.$paywall?.open(); return false;" class="w-full px-4 py-3 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl font-semibold text-sm transition-colors shadow-sm">
                    สมัครสมาชิก Premium
                </button>
            </div>
            <a href="{{ route('dashboard') }}" class="block w-full text-center px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-sm font-medium text-foreground">
                กลับไปยังหน้าหลัก
            </a>
        @else
            <form method="POST" action="{{ route('invitations.accept.store', $token) }}">
                @csrf
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-sm font-medium text-foreground text-center">
                        ยกเลิก
                    </a>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl font-semibold text-sm transition-colors shadow-sm">
                        เข้าร่วม
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>