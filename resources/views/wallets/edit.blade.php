<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            แก้ไขกระเป๋าเงิน: {{ $wallet->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto" x-data="{ walletType: '{{ $wallet->type->value }}' }">
                <!-- Back Button -->
                <a href="{{ route('wallets.show', $wallet) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    กลับไปหน้ากระเป๋าเงิน
                </a>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form method="POST" action="{{ route('wallets.update', $wallet) }}" class="space-y-6">
                        @csrf
                        @method('put')

                        <div>
                            <x-input-label for="name" value="ชื่อกระเป๋าเงิน" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $wallet->name) }}" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <x-input-label for="type" value="ประเภทกระเป๋าเงิน" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600" required x-model="walletType">
                                <option value="bank">บัญชีธนาคาร</option>
                                <option value="ewallet">เว็บเวล็ต</option>
                                <option value="cash">เงินสด</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <template x-if="walletType === 'bank'">
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="bank_name" value="ชื่อธนาคาร" />
                                    <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" value="{{ old('bank_name', $wallet->bank_name) }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                                </div>

                                <div>
                                    <x-input-label for="account_number" value="เลขที่บัญชี" />
                                    <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" value="{{ old('account_number', $wallet->account_number) }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('account_number')" />
                                </div>
                            </div>
                        </template>

                        <div>
                            <x-input-label for="notes" value="หมายเหตุ" />
                            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600" rows="3">{{ old('notes', $wallet->notes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm" @checked(old('is_default', $wallet->is_default)) />
                            <label for="is_default" class="ml-2 text-sm text-gray-700 dark:text-gray-300">ตั้งเป็นกระเป๋าเงินหลัก</label>
                        </div>

                        <!-- Delete Section -->
                        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">อันตราย</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                                @if($wallet->transactions()->exists())
                                    ไม่สามารถลบกระเป๋าเงินนี้ได้เนื่องจากมีธุรกรรมอยู่
                                @else
                                    การลบกระเป๋าเงินจะไม่สามารถเรียกคืนได้
                                @endif
                            </p>
                            @if(!$wallet->transactions()->exists())
                                <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('คุณต้องการลบกระเป๋าเงินนี้ใช่ไหม?');">
                                    @csrf
                                    @method('delete')
                                    <x-danger-button>ลบกระเป๋าเงิน</x-danger-button>
                                </form>
                            @endif
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="window.history.back()" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors font-medium">
                                ยกเลิก
                            </button>
                            <x-primary-button class="flex-1">บันทึกการแก้ไข</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
