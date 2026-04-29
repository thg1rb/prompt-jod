<x-app-layout>
    <div class="py-6" x-data="categories()" x-init="loadCategories()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">หมวดหมู่</h1>
                <p class="text-text-muted text-sm">จัดการหมวดหมู่และคำสำคัญสำหรับการจัดประเภทอัตโนมัติ</p>
            </div>
            <button @click="startNew()" class="inline-flex items-center px-4 py-2 bg-primary text-primary-foreground rounded-lg text-sm font-medium hover:bg-primary/90 transition-colors">
                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                เพิ่มหมวดหมู่
            </button>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="category in categories" :key="category.id">
                <div class="bg-card border border-border rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-lg grid place-items-center text-2xl" :style="{ background: `${category.color}20` }" x-text="category.icon"></div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold truncate" x-text="category.name"></div>
                            <div class="flex flex-wrap gap-1 mt-2">
                                <template x-for="keyword in category.keywords.slice(0, 4)" :key="keyword">
                                    <span class="inline-flex items-center px-2 py-0.5 bg-secondary text-secondary-foreground rounded text-[10px]" x-text="keyword"></span>
                                </template>
                                <template x-if="category.keywords.length > 4">
                                    <span class="inline-flex items-center px-2 py-0.5 border border-border rounded text-[10px]" x-text="`+${category.keywords.length - 4}`"></span>
                                </template>
                                <template x-if="category.keywords.length === 0">
                                    <span class="text-xs text-text-muted">ไม่มีคำสำคัญ</span>
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <button @click="startEdit(category)" class="inline-flex items-center justify-center p-1.5 hover:bg-muted rounded-lg transition-colors" aria-label="แก้ไข">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button @click="remove(category.id)" class="inline-flex items-center justify-center p-1.5 hover:bg-muted text-destructive hover:bg-destructive/10 rounded-lg transition-colors" aria-label="ลบ">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="categories.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-muted-foreground mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <p class="text-muted-foreground">ยังไม่มีหมวดหมู่</p>
            <button @click="startNew()" class="mt-4 text-primary hover:text-primary/80 font-medium">
                สร้างหมวดหมู่แรกของคุณ
            </button>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50" @click="open = false"></div>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-card rounded-xl shadow-lg border border-border w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-foreground mb-4" x-text="editing && categories.find(c => c.id === editing.id) ? 'แก้ไขหมวดหมู่' : 'เพิ่มหมวดหมู่'"></h3>
                <template x-if="editing">
                    <div class="space-y-4">
                        <div class="grid grid-cols-[100px,1fr] gap-3">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-foreground mb-1">ไอคอน</label>
                                <input
                                    id="icon"
                                    type="text"
                                    x-model="editing.icon"
                                    maxlength="4"
                                    class="w-full text-center text-2xl h-12 px-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                                />
                            </div>
                            <div>
                                <label for="name" class="block text-sm font-medium text-foreground mb-1">ชื่อ</label>
                                <input
                                    id="name"
                                    type="text"
                                    x-model="editing.name"
                                    class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-foreground mb-1">สี</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <template x-for="color in PALETTE" :key="color">
                                    <button
                                        type="button"
                                        @click="editing.color = color"
                                        class="h-8 w-8 rounded-full border-2 transition-colors"
                                        :class="editing.color === color ? 'border-foreground' : 'border-transparent'"
                                        :style="{ background: color }"
                                        :aria-label="`เลือกสี ${color}`"
                                    ></button>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label for="kw" class="block text-sm font-medium text-foreground mb-1">คำสำคัญ (คั่นด้วยจุลภาค)</label>
                            <input
                                id="kw"
                                type="text"
                                x-model="keywordInput"
                                @input="updateKeywords"
                                placeholder="7-eleven, mk, grab"
                                class="w-full px-3 py-2 border border-border rounded-lg bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:ring-offset-background"
                            />
                        </div>
                    </div>
                </template>
                <div class="flex gap-3 pt-4">
                    <button @click="open = false" class="flex-1 px-4 py-2 border border-border rounded-lg hover:bg-muted transition-colors text-foreground">
                        ยกเลิก
                    </button>
                    <button @click="save" class="flex-1 px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground rounded-lg transition-colors">
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
