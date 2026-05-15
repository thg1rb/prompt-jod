<x-app-layout>
    @php
        $user = auth()->user();
        $isFree = $user->isFree();
    @endphp
    <x-subscription-banner
        heading="ปลดล็อกฟีเจอร์จัดการหมวดหมู่ - เพียงแค่คุณสมัครสมาชิก"
        description="สร้าง ลบ แก้ไขหมวดหมู่ได้อย่างอิสระ!"
    />
    <div class="py-6" x-data="categories()" x-init="loadCategories(); isPremium = {{ $isFree ? 'false' : 'true' }}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <!-- Page Header -->
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-[22px] font-bold tracking-tight">หมวดหมู่</h1>
                    <p class="text-text-muted text-sm">จัดการหมวดหมู่สำหรับการจัดประเภทอัตโนมัติ</p>
                </div>
                @if($isFree)
                    <button @click="$paywall?.open()" class="inline-flex items-center px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-semibold hover:bg-primary-hover transition-colors shadow-sm active:scale-[0.98]">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        เพิ่มหมวดหมู่
                    </button>
                @else
                    <button @click="startNew()" class="inline-flex items-center px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-semibold hover:bg-primary-hover transition-colors shadow-sm active:scale-[0.98]">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        เพิ่มหมวดหมู่
                    </button>
                @endif
            </div>

            <!-- Category Grid -->
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <template x-for="category in categories" :key="category.id">
                    <div class="bg-card rounded-2xl border border-border p-4 shadow-card hover:shadow-elevated transition-shadow">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-2xl grid place-items-center text-2xl shrink-0" :style="{ background: `${getFixedCategoryColor(category.fixed_category_id)}18` }" x-text="category.icon"></div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-[14px] truncate" x-text="category.name"></div>
                                <div class="text-xs text-text-muted truncate" x-text="getFixedCategoryName(category.fixed_category_id)"></div>
                            </div>
                            @if(!$isFree)
                            <div class="flex items-center gap-1">
                                <button @click="startEdit(category)" class="w-8 h-8 rounded-xl hover:bg-surface-subtle active:bg-surface-elevated flex items-center justify-center transition-colors" aria-label="แก้ไข">
                                    <svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="remove(category.id)" class="w-8 h-8 rounded-xl hover:bg-destructive-light active:bg-surface-elevated flex items-center justify-center text-destructive transition-colors" aria-label="ลบ">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="categories.length === 0" class="text-center py-14">
                <div class="w-14 h-14 rounded-2xl bg-surface-subtle flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <p class="text-text-muted text-sm mb-1">ยังไม่มีหมวดหมู่</p>
                <button @click="startNew()" class="text-primary hover:text-primary/80 font-semibold text-sm transition-colors">
                    สร้างหมวดหมู่แรกของคุณ
                </button>
            </div>

            <!-- Category Modal -->
            <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" style="display: none;">
                <div x-show="open" x-transition:enter="transition ease-out duration-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="open = false"></div>

                <div x-show="open" x-transition:enter="transition ease-out duration-350" x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="transition ease-in duration-250" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95" class="relative bg-card rounded-t-2xl sm:rounded-2xl shadow-floating border border-border w-full max-w-md max-h-[70vh] sm:max-h-[88vh] overflow-hidden flex flex-col">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
                        <h3 class="text-[17px] font-semibold text-foreground" x-text="editing && categories.find(c => c.id === editing.id) ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่'"></h3>
                        <button @click="open = false" class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-surface-subtle active:bg-surface-elevated transition-colors" aria-label="ปิด">
                            <svg class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 min-h-0 overflow-y-auto px-6 pt-5 pb-6">
                    <template x-if="editing">
                        <div class="space-y-4">
                            <div>
                                <label for="fixed_category_id" class="block text-sm font-semibold text-foreground mb-1.5">หมวดหมู่หลัก</label>
                                <select
                                    id="fixed_category_id"
                                    x-model="editing.fixed_category_id"
                                    class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                                >
                                    <template x-for="fc in fixedCategories" :key="fc.id">
                                        <option :value="fc.id" x-text="`${fc.icon} ${fc.name}`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="grid grid-cols-[80px,1fr] gap-3">
                                <div>
                                    <label for="icon" class="block text-sm font-semibold text-foreground mb-1.5">ไอคอน</label>
                                    <input
                                        id="icon"
                                        type="text"
                                        x-model="editing.icon"
                                        maxlength="4"
                                        class="w-full text-center text-2xl h-12 px-2 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                                    />
                                </div>
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-foreground mb-1.5">ชื่อ</label>
                                    <input
                                        id="name"
                                        type="text"
                                        x-model="editing.name"
                                        class="w-full px-3 py-2.5 rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                    </div>
                    <div class="flex gap-3 p-5 border-t border-border bg-surface-subtle shrink-0">
                        <button @click="open = false" class="flex-1 px-4 py-2.5 border border-border rounded-xl hover:bg-surface-subtle active:bg-surface-elevated transition-colors text-foreground font-medium text-sm">
                            ยกเลิก
                        </button>
                        <button @click="save" class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary-hover text-primary-foreground rounded-xl transition-colors font-semibold text-sm shadow-sm">
                            บันทึก
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
