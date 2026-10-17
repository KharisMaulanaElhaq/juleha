<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-1 px-1">
    <div class="text-base sm:text-lg text-gray-800 dark:text-gray-100 font-semibold tracking-tight">
        Selamat datang, <span class="text-[#5a3d9e] dark:text-[#b9a2f0] font-bold">{{ auth()->user()?->name }}</span>
    </div>
    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
        {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
    </div>
</div>
