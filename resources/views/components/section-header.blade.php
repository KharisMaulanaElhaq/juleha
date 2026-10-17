@props([
    'tag' => null,
    'tagIcon' => null,
    'title' => '',
    'highlight' => null,
    'subtitle' => null,
    'align' => 'center', // 'center' or 'left'
    'tagColor' => 'bg-brand/10 text-brand dark:bg-brand/20 dark:text-brand-dark border border-brand/20 dark:border-brand-dark/20',
    'highlightColor' => 'text-brand dark:text-brand-dark',
])

<div {{ $attributes->merge(['class' => ($align === 'center' ? 'text-center max-w-3xl mx-auto' : 'max-w-2xl') . ' mb-8 sm:mb-14']) }} data-aos="fade-up">
    @if($tag)
        <span class="inline-flex items-center gap-1.5 px-3 py-1 sm:px-3.5 sm:py-1.5 rounded-full text-[11px] sm:text-xs font-semibold uppercase tracking-wider mb-3 sm:mb-4 {{ $tagColor }}">
            @if($tagIcon)
                <i data-lucide="{{ $tagIcon }}" class="w-3.5 h-3.5"></i>
            @endif
            {!! $tag !!}
        </span>
    @endif

    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl lg:text-4xl text-slate-900 dark:text-white leading-tight tracking-tight mb-3 sm:mb-4">
        {!! $title !!}
        @if($highlight)
            <span class="{{ $highlightColor }}">{!! $highlight !!}</span>
        @endif
    </h2>

    @if($subtitle)
        <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base leading-relaxed line-clamp-3 sm:line-clamp-none">
            {!! $subtitle !!}
        </p>
    @endif
</div>
