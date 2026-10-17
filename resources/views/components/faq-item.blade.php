@props([
    'question' => '',
    'answer' => '',
    'isOpen' => false,
])

<div class="faq-item">
    <div class="faq-question py-5 flex items-center justify-between text-slate-800 dark:text-white cursor-pointer group {{ $isOpen ? 'active' : '' }}" onclick="toggleFAQ(this)">
        <span class="text-base sm:text-lg font-semibold group-hover:text-brand dark:group-hover:text-brand-dark transition-colors pr-4">{{ $question }}</span>
        <i data-lucide="plus" class="faq-icon w-5 h-5 text-brand-accent dark:text-brand-dark-accent flex-shrink-0 transition-transform"></i>
    </div>
    <div class="faq-answer text-slate-600 dark:text-slate-400 text-sm sm:text-base leading-relaxed {{ $isOpen ? 'open' : '' }}">
        @if($slot->isNotEmpty())
            {{ $slot }}
        @else
            <p>{{ $answer }}</p>
        @endif
    </div>
</div>
