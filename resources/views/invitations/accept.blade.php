<x-guest-layout>
    <div class="py-8 px-6">
        <div class="text-center mb-6">
            <div class="h-16 w-16 mx-auto bg-primary/10 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-foreground mb-2">เชิญเข้าร่วมกระเป๋าเงิน</h2>
            <p class="text-text-muted">
                คุณต้องการเข้าร่วมกระเป๋าเงิน <strong class="text-foreground">{{ $wallet->name }}</strong> ของ <strong class="text-foreground">{{ $owner->name }}</strong> ใช่หรือไม่?
            </p>
        </div>

        <div class="bg-muted rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3 mb-3">
                <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-semibold">
                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-medium text-foreground">{{ $owner->name }}</div>
                    <div class="text-xs text-text-muted">{{ $owner->email }}</div>
                </div>
            </div>
            <div class="text-sm text-text-muted">
                <span class="font-medium text-foreground">{{ $wallet->name }}</span>
                <span class="mx-1">•</span>
                <span>{{ $wallet->type->getLabel() }}</span>
            </div>
        </div>

        @if(!$isLoggedIn)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                    กรุณาเข้าสู่ระบบก่อนเข้าร่วมกระเป๋าเงิน
                </p>
            </div>
            <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors font-medium">
                เข้าสู่ระบบ
            </a>
        @elseif($isAlreadyMember)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:bg-green-800 rounded-lg p-4 mb-4">
                <p class="text-sm text-green-700 dark:text-green-400">
                    คุณเป็นสมาชิกของกระเป๋าเงินนี้แล้ว
                </p>
            </div>
            <a href="{{ route('wallets.show', $wallet) }}" class="block w-full text-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors font-medium">
                ไปยังกระเป๋าเงิน
            </a>
        @elseif(auth()->check() && auth()->user()->isFree())
            <div class="bg-primary/5 border border-primary/20 rounded-lg p-4 mb-4 text-center">
                <p class="text-sm text-foreground mb-3">กรุณาสมัครสมาชิก Premium เพื่อเข้าร่วมกระเป๋าเงินแชร์</p>
                <button onclick="window.$paywall?.open(); return false;" class="w-full px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors font-medium">
                    สมัครสมาชิก Premium
                </button>
            </div>
            <a href="{{ route('dashboard') }}" class="block w-full text-center px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground text-center">
                กลับไปยังหน้าหลัก
            </a>
        @else
            <form method="POST" action="{{ route('invitations.accept.store', $token) }}">
                @csrf
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground text-center">
                        ยกเลิก
                    </a>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors font-medium">
                        เข้าร่วม
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-guest-layout>