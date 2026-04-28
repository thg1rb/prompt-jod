<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
            บัญชีเงินของฉัน
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{ walletType: '' }">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">บัญชีเงิน</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">จัดการบัญชีเงินและยอดคงเหลือ</p>
                </div>
                <button
                    onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-wallet' }))"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    เพิ่มบัญชีใหม่
                </button>
            </div>

            <!-- Total Balance Card -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-indigo-100 text-sm font-medium">ยอดคงเหลือรวมทั้งหมด</p>
                <p class="text-3xl font-bold mt-2">
                    {{ number_format($totalBalance, 2) }} บาท
                </p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-green-600 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-red-600 dark:text-red-400">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Wallets Grid -->
            @if($wallets->isEmpty())
                <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">ยังไม่มีบัญชี</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">เริ่มต้นด้วยการเพิ่มบัญชีเงินแรกของคุณ</p>
                    <button
                        onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-wallet' }))"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        เพิ่มบัญชีใหม่
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($wallets as $wallet)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                            <!-- Wallet Header -->
                            <div class="p-6" style="background-color: {{ $wallet->color ?? '#6366f1' }}">
                                <div class="flex items-start justify-between">
                                    <div>
                                        @if($wallet->is_default)
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-white/20 text-white mb-2">
                                                บัญชีหลัก
                                            </span>
                                        @endif
                                        <h3 class="text-lg font-semibold text-white">{{ $wallet->name }}</h3>
                                        <p class="text-white/80 text-sm mt-1">{{ $wallet->type->getLabel() }}</p>
                                    </div>
                                    @if($wallet->icon)
                                        <div class="text-white/80 text-2xl">
                                            {{ $wallet->icon }}
                                        </div>
                                    @endif
                                </div>
                                @if($wallet->bank_name)
                                    <p class="text-white/70 text-sm mt-2">{{ $wallet->bank_name }}
                                        @if($wallet->account_number)
                                            • {{ $wallet->account_number }}
                                        @endif
                                    </p>
                                @endif
                            </div>

                            <!-- Wallet Body -->
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">ยอดคงเหลือ</span>
                                    <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ number_format($wallet->balance, 2) }} บาท
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ $wallet->transactions_count }} รายการ</span>
                                    <span>{{ $wallet->balance_adjustments_count }} การปรับ</span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 flex gap-2">
                                    <a href="{{ route('wallets.show', $wallet) }}" class="flex-1 text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                                        ดูรายละเอียด
                                    </a>
                                    <a href="{{ route('wallets.edit', $wallet) }}" class="flex-1 text-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                                        แก้ไข
                                    </a>
                                    @if(!$wallet->is_default)
                                        <form method="POST" action="{{ route('wallets.set-default', $wallet) }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                                                ตั้งหลัก
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Create Wallet Modal -->
    <x-modal name="create-wallet" maxWidth="2xl">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">เพิ่มบัญชีใหม่</h3>
            <form method="POST" action="{{ route('wallets.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="name" value="ชื่อบัญชี" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="type" value="ประเภทบัญชี" />
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
                        <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
                    </div>
                    <div>
                        <x-input-label for="account_number" value="เลขที่บัญชี" />
                        <x-text-input id="account_number" name="account_number" type="text" class="mt-1 block w-full" />
                        <x-input-error class="mt-2" :messages="$errors->get('account_number')" />
                    </div>
                </template>
                <div>
                    <x-input-label for="opening_balance" value="ยอดเงินเริ่มต้น (บาท)" />
                    <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    <x-input-error class="mt-2" :messages="$errors->get('opening_balance')" />
                </div>
                <div>
                    <x-input-label for="color" value="สีประจำบัญชี" />
                    <div class="flex gap-2 mt-1">
                        <input type="color" id="color" name="color" value="#6366f1" class="h-10 w-16 border rounded cursor-pointer" />
                        <x-text-input id="color_text" name="color_text" type="text" class="flex-1" value="#6366f1" readonly />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('color')" />
                </div>
                <div>
                    <x-input-label for="icon" value="ไอคอน (Emoji)" />
                    <x-text-input id="icon" name="icon" type="text" class="mt-1 block w-full" placeholder="🏦 💳 💵" />
                    <x-input-error class="mt-2" :messages="$errors->get('icon')" />
                </div>
                <div>
                    <x-input-label for="notes" value="หมายเหตุ" />
                    <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600" rows="3"></textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="is_default" name="is_default" class="rounded border-gray-300 text-indigo-600 shadow-sm" />
                    <label for="is_default" class="ml-2 text-sm text-gray-700 dark:text-gray-300">ตั้งเป็นบัญชีหลัก</label>
                </div>
                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'create-wallet' }))"
                        class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        ยกเลิก
                    </button>
                    <x-primary-button class="flex-1">บันทึก</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <script>
        document.getElementById('color')?.addEventListener('input', (e) => {
            document.getElementById('color_text').value = e.target.value;
        });
    </script>
</x-app-layout>
