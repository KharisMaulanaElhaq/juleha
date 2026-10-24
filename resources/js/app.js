// Theme Management (Light / Dark mode)
function initTheme() {
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');

    // Update navbar link colors if scrolled
    const navbar = document.getElementById('navbar');
    if (navbar && navbar.classList.contains('scrolled')) {
        navbar.querySelectorAll('.nav-link').forEach(l => {
            if (!l.classList.contains('font-semibold') && !l.classList.contains('text-brand')) {
                l.style.color = isDark ? '#CBD5E1' : '#334155';
            }
        });
    }

    if (window.lucide) {
        window.lucide.createIcons();
    }
}

window.toggleTheme = toggleTheme;

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Init AOS if available
    if (window.AOS) {
        window.AOS.init({ duration: 900, once: true, easing: 'ease-in-out', offset: 60 });
    }

    // Init Lucide Icons if available
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // ---- PRELOADER ----
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            setTimeout(() => {
                preloader.classList.add('hidden');
            }, 1200);
        });
        // Fallback in case load already fired
        setTimeout(() => {
            if (!preloader.classList.contains('hidden')) {
                preloader.classList.add('hidden');
            }
        }, 3000);
    }

    // ---- NAVBAR SCROLL ----
    const navbar = document.getElementById('navbar');
    if (navbar) {
        const handleScroll = () => {
            if (window.scrollY > 20) {
                navbar.classList.add('scrolled');
                navbar.querySelectorAll('.nav-link').forEach(l => {
                    if (!l.classList.contains('font-semibold') && !l.classList.contains('text-brand')) {
                        l.style.color = document.documentElement.classList.contains('dark') ? '#CBD5E1' : '#334155';
                    }
                });
            } else {
                navbar.classList.remove('scrolled');
                navbar.querySelectorAll('.nav-link').forEach(l => {
                    if (!l.classList.contains('font-semibold') && !l.classList.contains('text-brand')) {
                        l.style.color = '';
                    }
                });
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    // ---- MOBILE MENU ----
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuOpen = document.getElementById('menu-open');
    const menuClose = document.getElementById('menu-close');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');
            mobileMenu.classList.toggle('hidden');
            if (menuOpen) menuOpen.classList.toggle('hidden', !isOpen);
            if (menuClose) menuClose.classList.toggle('hidden', isOpen);
        });

        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                if (menuOpen) menuOpen.classList.remove('hidden');
                if (menuClose) menuClose.classList.add('hidden');
            });
        });
    }

    // ---- SCROLL TOP ----
    const scrollBtn = document.getElementById('scroll-top');
    if (scrollBtn) {
        window.addEventListener('scroll', () => {
            scrollBtn.classList.toggle('visible', window.scrollY > 400);
        });
        scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // ---- ANIMATED COUNTERS ----
    function animateCounter(el, target, duration = 2000) {
        let start = 0;
        const step = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const current = Math.floor(progress * target);
            el.textContent = target > 999 ? current.toLocaleString() + '+' : current + (el.dataset.suffix || '');
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target > 999 ? target.toLocaleString() + '+' : target + (el.dataset.suffix || '');
        };
        requestAnimationFrame(step);
    }

    const counterElements = document.querySelectorAll('[data-count]');
    if (counterElements.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el = entry.target;
                    const target = parseInt(el.dataset.count);
                    animateCounter(el, target);
                    counterObserver.unobserve(el);
                }
            });
        }, { threshold: 0.5 });

        counterElements.forEach(el => counterObserver.observe(el));
    }

    // ---- TESTIMONIAL SLIDER ----
    const track = document.getElementById('testimonial-track');
    const dotsContainer = document.getElementById('slider-dots');
    if (track && dotsContainer) {
        const slides = track.querySelectorAll('.slide');
        let currentSlide = 0;
        let slidesPerView = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 768 ? 2 : 1;

        function updateSlider() {
            slidesPerView = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 768 ? 2 : 1;
            const offset = currentSlide * (100 / slidesPerView);
            track.style.transform = `translateX(-${offset}%)`;
            dotsContainer.querySelectorAll('button').forEach((d, i) => {
                d.classList.toggle('bg-red-600', i === currentSlide);
                d.classList.toggle('bg-gray-300', i !== currentSlide);
                d.classList.toggle('w-6', i === currentSlide);
                d.classList.toggle('w-2.5', i !== currentSlide);
            });
        }

        dotsContainer.innerHTML = '';
        for (let i = 0; i < slides.length; i++) {
            const dot = document.createElement('button');
            dot.className = `h-2.5 rounded-full transition-all ${i === 0 ? 'bg-red-600 w-6' : 'bg-gray-300 dark:bg-slate-600 w-2.5'}`;
            dot.setAttribute('aria-label', `Slide ${i + 1}`);
            dot.addEventListener('click', () => { currentSlide = i; updateSlider(); });
            dotsContainer.appendChild(dot);
        }

        const prevBtn = document.getElementById('slide-prev');
        const nextBtn = document.getElementById('slide-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                currentSlide = Math.max(0, currentSlide - 1);
                updateSlider();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                currentSlide = Math.min(slides.length - 1, currentSlide + 1);
                updateSlider();
            });
        }

        window.addEventListener('resize', updateSlider);

        setInterval(() => {
            currentSlide = currentSlide >= slides.length - slidesPerView ? 0 : currentSlide + 1;
            updateSlider();
        }, 5000);
    }

    // ---- FORM SUBMISSIONS ----
    const apptForm = document.getElementById('appt-form');
    if (apptForm) {
        apptForm.addEventListener('submit', (e) => {
            e.preventDefault();
            showToast("Appointment request submitted! We'll confirm within 2 hours.");
            e.target.reset();
        });
    }
});

// ---- FAQ ACCORDION ----
function toggleFAQ(el) {
    const answer = el.nextElementSibling;
    if (!answer) return;
    const isOpen = answer.classList.contains('open');

    document.querySelectorAll('.faq-answer.open').forEach(a => a.classList.remove('open'));
    document.querySelectorAll('.faq-question.active').forEach(q => q.classList.remove('active'));

    if (!isOpen) {
        answer.classList.add('open');
        el.classList.add('active');
    }
    if (window.lucide) {
        window.lucide.createIcons();
    }
}
window.toggleFAQ = toggleFAQ;

// ---- TOAST & NEWSLETTER ----
function handleNewsletter(e) {
    e.preventDefault();
    showToast('Successfully subscribed to health updates!');
    if (e.target && typeof e.target.reset === 'function') {
        e.target.reset();
    }
}
window.handleNewsletter = handleNewsletter;

function showToast(msg) {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-msg');
    if (!toast) return;

    if (toastMsg) toastMsg.textContent = msg;
    toast.classList.remove('translate-x-full', 'opacity-0');
    toast.classList.add('translate-x-0', 'opacity-100');

    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        toast.classList.remove('translate-x-0', 'opacity-100');
    }, 3500);

    if (window.lucide) {
        window.lucide.createIcons();
    }
}
window.showToast = showToast;

function handleSidebarNewsletter(e) {
    e.preventDefault();
    showToast('Successfully subscribed to health updates!');
    if (e.target && typeof e.target.reset === 'function') {
        e.target.reset();
    }
}
window.handleSidebarNewsletter = handleSidebarNewsletter;

function handleComment(e) {
    e.preventDefault();
    showToast('Pesan berhasil dikirim! Pengurus akan segera merespons.');
    if (e.target && typeof e.target.reset === 'function') {
        e.target.reset();
    }
}
window.handleComment = handleComment;

function handleRegistration(e) {
    e.preventDefault();
    showToast('Formulir berhasil dikirim! Narahubung DPD JULEHA akan menghubungi Anda.');
    if (e.target && typeof e.target.reset === 'function') {
        e.target.reset();
    }
}
window.handleRegistration = handleRegistration;
window.handleAppointment = handleRegistration;



