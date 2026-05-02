<x-app-layout>
    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">เพิ่มธุรกรรมใหม่</h1>
                <p class="text-text-muted text-sm mt-1">บันทึกรายการรายจ่าย รายรับ หรือการปรับยอด</p>
            </div>

            <x-transaction-modal
                :wallets="$wallets"
                :categories="$categories"
                :initial-open="true"
            />
        </div>
    </div>
</x-app-layout>
