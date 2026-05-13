@props(['show' => true])

@if ($show)
    <div x-data="{ bannerClosed: false }" 
         x-show="!bannerClosed" 
         x-transition 
         x-cloak
         class="bg-gradient-to-r from-primary to-primary/80 text-white px-4 py-3">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="font-medium">เริ่มใช้งาน PromptJod Premium เพื่อเข้าถึงฟีเจอร์พิเศษทั้งหมด</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('subscription.index') }}" 
                   class="px-4 py-2 bg-white text-primary rounded-lg text-sm font-medium hover:bg-white/90 transition-colors">
                    เรียนดูรายละเอียด
                </a>
                <button @click="bannerClosed = true" 
                        class="text-white/80 hover:text-white p-1">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
