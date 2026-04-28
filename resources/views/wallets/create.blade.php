<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            เพิ่มกระเป๋าเงินใหม่
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto" x-data="{ walletType: '' }">
                <!-- Back Button -->
                <a href="{{ route('wallets.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    กลับไปหน้ากระเป๋าเงินทั้งหมด
                </a>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form method="POST" action="{{ route('wallets.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <x-input-label for="name" value="ชื่อกระเป๋าเงิน" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="type" value="ประเภทกระเป๋าเงิน" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600" required x-model="walletType">
                                <option value="">เลือกประเภท</option>
                                <option value="bank">บัญชีธนาคาร</option>
                                <option value="ewallet">เว็บเวล็ต</option>
                                <option value="cash">เงินสด</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>
                        <template x-if="walletType === 'bank' || walletType === 'ewallet'">
                            <div>
                                <x-input-label for="bank_name" value="ชื่อธนาคาร/บริการ" />
                                <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" value="{{ old('bank_name') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                            </div>
                            <div>
                                <x-input-label for="account_number" value="เลขที่บัญชี" />
                                <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" value="{{ old('account_number') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('account_number')" />
                            </div>
                        </template>
                        <div>
                            <x-input-label for="opening_balance" value="ยอดเงินเริ่มต้น (บาท)" />
                            <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('opening_balance') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('opening_balance')" />
                        </div>
                        <div>
                            <x-input-label for="color" value="สีประจำกระเป๋าเงิน" />
                            <div class="flex gap-2 mt-1">
                                <input type="color" id="color" name="color" value="{{ old('color', '#6366f1') }}" class="h-10 w-16 border rounded cursor-pointer" />
                                <x-text-input id="color_text" name="color_text" type="text" class="flex-1" value="{{ old('color', '#6366f1') }}" readonly />
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('color')" />
                        </div>
                        <div>
                            <x-input-label for="icon" value="ไอคอน (Emoji)" />
                            <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full" value="{{ old('icon') }}" placeholder="🏦 💳 💵" />
                            <x-input-error class="mt-2" :messages="$errors->get('icon')" />
                        </div>
                        <div>
                            <x-input-label for="notes" value="หมายเหตุ" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600" rows="3">{{ old('notes') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="is_default" name="is_default" class="rounded border-gray-300 text-indigo-600 shadow-sm" {{ old('is_default') ? 'checked' : '' }} />
                            <label for="is_default" class="ml-2 text-sm text-gray-700 dark:text-gray-300">ตั้งเป็นกระเป๋าเงินหลัก</label>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="window.history.back()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                                ยกเลิก
                            </button>
                            <x-primary-button class="flex-1">บันทึก</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('color')?.addEventListener('input', (e) => {
            document.getElementById('color_text').value = e.target.value;
        });
    </script>
</x-app-layout>
