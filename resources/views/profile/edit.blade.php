<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Page Header -->
            <div class="space-y-1">
                <h1 class="text-[22px] font-bold tracking-tight">โปรไฟล์</h1>
                <p class="text-sm text-text-muted">จัดการข้อมูลส่วนตัวและการตั้งค่าบัญชี</p>
            </div>

            <!-- Profile Card -->
            <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-primary/60 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-foreground">{{ auth()->user()->name }}</h2>
                            <p class="text-sm text-text-muted">{{ auth()->user()->email }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-sm font-semibold text-foreground mb-1.5">ชื่อ</label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', auth()->user()->name) }}"
                                   required
                                   minlength="2"
                                   maxlength="255"
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('name') ? 'border-destructive' : 'border-border' }} bg-background text-foreground placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors text-sm"
                                   placeholder="ระบุชื่อของคุณ">
                            @error('name')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-foreground mb-1.5">อีเมล</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', auth()->user()->email) }}"
                                   required
                                   maxlength="255"
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-destructive' : 'border-border' }} bg-background text-foreground placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors text-sm"
                                   placeholder="your@email.com">
                            @error('email')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                                class="w-full bg-primary text-primary-foreground py-2.5 rounded-xl font-semibold text-sm hover:bg-primary-hover active:scale-[0.98] transition-all shadow-sm">
                            บันทึกข้อมูล
                        </button>
                    </form>
                </div>
            </div>

            <!-- Password Card -->
            <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-surface-subtle flex items-center justify-center">
                            <svg class="w-4.5 h-4.5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h2 class="text-[16px] font-semibold text-foreground">เปลี่ยนรหัสผ่าน</h2>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-sm font-semibold text-foreground mb-1.5">รหัสผ่านปัจจุบัน</label>
                            <input type="password"
                                   name="current_password"
                                   autocomplete="current-password"
                                   required
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('current_password') ? 'border-destructive' : 'border-border' }} bg-background text-foreground placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors text-sm"
                                   placeholder="••••••••">
                            @error('current_password')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-foreground mb-1.5">รหัสผ่านใหม่</label>
                            <input type="password"
                                   name="password"
                                   autocomplete="new-password"
                                   required
                                   minlength="8"
                                   class="w-full px-4 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-destructive' : 'border-border' }} bg-background text-foreground placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors text-sm"
                                   placeholder="••••••••">
                            @error('password')
                            <p class="text-sm text-destructive mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-foreground mb-1.5">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password"
                                   name="password_confirmation"
                                   autocomplete="new-password"
                                   required
                                   minlength="8"
                                   class="w-full px-4 py-2.5 rounded-xl border border-border bg-background text-foreground placeholder-text-muted focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors text-sm"
                                   placeholder="••••••••">
                        </div>

                        <button type="submit"
                                class="w-full bg-primary text-primary-foreground py-2.5 rounded-xl font-semibold text-sm hover:bg-primary-hover active:scale-[0.98] transition-all shadow-sm">
                            เปลี่ยนรหัสผ่าน
                        </button>
                    </form>
                </div>
            </div>

            <!-- Subscription Card -->
            <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden">
                <div class="p-5 sm:p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-9 h-9 rounded-xl bg-surface-subtle flex items-center justify-center">
                            <svg class="w-4.5 h-4.5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <h2 class="text-[16px] font-semibold text-foreground">การสมัครสมาชิก</h2>
                    </div>

                    @if($subscription && $subscription->status !== \App\Enums\SubscriptionStatus::Canceled)
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold
                                        {{ $subscription->status === \App\Enums\SubscriptionStatus::Active ? 'bg-success-light text-success' : 'bg-warning-light text-warning' }}">
                                        {{ $subscription->status_label }}
                                    </span>
                                    <span class="text-sm text-text-muted font-medium">PromptJod Premium</span>
                                </div>
                                <p class="text-2xl font-bold tracking-tight">
                                    ฿{{ number_format($subscription->amount, 2) }}
                                    <span class="text-sm font-normal text-text-muted">
                                        /{{ $subscription->plan === 'yearly' ? 'ปี' : 'เดือน' }}
                                    </span>
                                </p>
                                @if($subscription->current_period_end)
                                    <p class="text-sm text-text-muted mt-1">
                                        ใช้งานถึง: {{ $subscription->current_period_end->locale('th')->isoFormat('D MMMM YYYY') }}
                                    </p>
                                @endif
                            </div>
                            <button onclick="if(confirm('คุณต้องการยกเลิกการสมัครสมาชิกหรือไม่?')) cancelSubscription()"
                                    class="px-4 py-2.5 bg-destructive text-destructive-foreground rounded-xl text-sm font-semibold hover:bg-destructive/90 transition-colors shadow-sm active:scale-[0.98]">
                                ยกเลิก
                            </button>
                        </div>
                    @elseif($subscription && $subscription->status === \App\Enums\SubscriptionStatus::Canceled)
                        <div class="text-center py-3" x-data>
                            <p class="text-text-muted mb-2">การสมัครสมาชิกถูกยกเลิกแล้ว</p>
                            <button @click="$paywall?.open()"
                                    class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl font-semibold text-sm hover:bg-primary-hover transition-colors shadow-sm active:scale-[0.98]">
                                สมัครใหม่
                            </button>
                        </div>
                    @else
                        <div class="text-center py-3" x-data>
                            <p class="text-text-muted mb-2">ยังไม่ได้สมัครสมาชิก</p>
                            <button @click="$paywall?.open()"
                                    class="px-6 py-2.5 bg-primary text-primary-foreground rounded-xl font-semibold text-sm hover:bg-primary-hover transition-colors shadow-sm active:scale-[0.98]">
                                เลือกแพ็กเกจ
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <script>
                async function cancelSubscription() {
                    try {
                        const response = await fetch('/subscription', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'ไม่สามารถยกเลิกได้');
                        }
                    } catch (e) {
                        alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
                    }
                }
            </script>

            <!-- Actions -->
            <div class="bg-card rounded-2xl shadow-card border border-border overflow-hidden divide-y divide-border">
                <!-- Terms of Service -->
                <a href="/terms"
                   class="flex items-center justify-between px-5 py-3.5 text-left hover:bg-surface-subtle/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-surface-subtle flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-foreground">เงื่อนไขการใช้งาน</span>
                    </div>
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Privacy Policy -->
                <a href="/privacy"
                   class="flex items-center justify-between px-5 py-3.5 text-left hover:bg-surface-subtle/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-surface-subtle flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-foreground">นโยบายความเป็นส่วนตัว</span>
                    </div>
                    <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full px-5 py-3.5 flex items-center justify-between text-left hover:bg-surface-subtle/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-destructive-light flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </div>
                            <span class="text-sm font-semibold text-destructive">ออกจากระบบ</span>
                        </div>
                        <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-card rounded-2xl shadow-card border border-destructive/30 overflow-hidden">
                <div class="p-5 sm:p-6">
                    <h3 class="text-[15px] font-semibold text-destructive mb-1.5">ลบบัญชี</h3>
                    <p class="text-sm text-text-muted mb-4">การลบบัญชีจะไม่สามารถกู้คืนได้ ข้อมูลทั้งหมดจะถูกลบออกจากระบบ</p>
                    <button x-data="{ open: false }"
                            @click="open = true"
                            class="inline-flex items-center gap-2 bg-destructive text-destructive-foreground text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-destructive/90 transition-colors shadow-sm active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        ลบบัญชีถาวร
                    </button>
                </div>
            </div>

            @if (session('status'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click.away="setTimeout(() => show = false, 2000)"
                     class="fixed bottom-6 right-6 bg-success text-success-foreground px-6 py-3 rounded-2xl shadow-floating flex items-center gap-2.5 text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>บันทึกข้อมูลเรียบร้อยแล้ว</span>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>