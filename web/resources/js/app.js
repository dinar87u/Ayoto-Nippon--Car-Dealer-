import './bootstrap';

import Alpine from 'alpinejs';
import Lenis from 'lenis';

window.Alpine = Alpine;

// Ganti tema dengan tirai view transition + kilau bilah, seperti referensi.
window.toggleTheme = function (event) {
    var root = document.documentElement;
    var gelap = !root.classList.contains('dark');
    var tombol = event && event.currentTarget;
    var denyut = tombol ? tombol.querySelector('.theme-pulse') : null;

    if (denyut) {
        denyut.style.background = gelap ? 'rgba(129, 140, 248, .45)' : 'rgba(252, 211, 77, .55)';
        denyut.style.animation = 'pulse-burst 600ms ease-out';
        denyut.addEventListener('animationend', function () { denyut.style.animation = ''; }, { once: true });
    }

    function ganti() {
        root.classList.toggle('dark', gelap);
        try { localStorage.setItem('theme', gelap ? 'dark' : 'light'); } catch (e) {}
    }

    if (document.startViewTransition && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        var kilau = document.createElement('div');
        kilau.className = 'sword-glint slicing';
        document.body.appendChild(kilau);
        var transisi = document.startViewTransition(ganti);
        var bersihkan = function () { kilau.remove(); };
        if (transisi && transisi.finished) {
            transisi.finished.finally(bersihkan);
        } else {
            setTimeout(bersihkan, 950);
        }
        return;
    }

    ganti();
};

// Tanpa pilihan tersimpan, tema mengikuti perubahan sistem.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
    try {
        if (!localStorage.getItem('theme')) {
            document.documentElement.classList.toggle('dark', e.matches);
        }
    } catch (err) {}
});

Alpine.start();


// Animasi judul hero per karakter gaya Boxcars (blur + slide-up).
(function () {
    function initHeroTextReveal() {
        var titleEl = document.getElementById('hero-title');
        if (!titleEl || titleEl.dataset.heroSplit === '1') return;
        var text = titleEl.textContent.trim();
        if (!text) return;

        titleEl.dataset.heroSplit = '1';
        titleEl.setAttribute('aria-label', text);

        var escapeHtml = function (str) {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        };

        var words = text.split(/\s+/);
        var charIndex = 0;
        titleEl.innerHTML = words.map(function (word) {
            var chars = Array.from(word).map(function (ch) {
                var delay = 120 + charIndex * 24;
                charIndex++;
                return '<span class="hero-char" aria-hidden="true" style="--char-delay:' + delay + 'ms">' + escapeHtml(ch) + '</span>';
            }).join('');
            charIndex++;
            return '<span class="hero-word" aria-hidden="true">' + chars + '</span>';
        }).join(' ');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroTextReveal);
    } else {
        initHeroTextReveal();
    }
})();

// Scroll-reveal gaya Boxcars/useInView: animasi setiap kali masuk viewport, berlaku ke semua bagian dan halaman publik.
(function () {
    function initScrollReveal() {
        var skipContainer = function (el) {
            return !el || el.closest('.hero, header, [data-no-reveal], .sidebar-shell');
        };

        var hasRevealRelation = function (el) {
            if (el.classList.contains('reveal')) return true;
            if (el.closest('.reveal')) return true;
            return Boolean(el.querySelector('.reveal'));
        };

        var tandai = function (el) {
            if (!el || skipContainer(el) || hasRevealRelation(el)) return;
            if (getComputedStyle(el).position === 'sticky') return;
            el.classList.add('reveal');
        };

        // Terapkan otomatis ke bagian beranda, footer, dan seluruh halaman publik agar semua ikut beranimasi saat scroll.
        document.querySelectorAll('.bx .section-head, .bx .how-sticky > *, .bx .trust-content-box > *').forEach(tandai);
        document.querySelectorAll('footer > div:first-child > *').forEach(function (el, idx) {
            if (!el.hasAttribute('data-reveal-delay')) {
                el.setAttribute('data-reveal-delay', String(Math.min(idx, 6) * 80));
            }
            tandai(el);
        });

        // Halaman publik non-beranda & konten utama: animasikan blok teratas dan kartu di dalam grid.
        document.querySelectorAll('body > div.w-full > *, main:not(.bx) > section > *, main:not(.bx) > div > *').forEach(function (el) {
            if (skipContainer(el)) return;
            if (el.classList.contains('grid')) {
                if (el.classList.contains('reveal')) {
                    el.classList.remove('reveal');
                }
                if (!el.hasAttribute('data-reveal-stagger')) {
                    el.setAttribute('data-reveal-stagger', '');
                }
                Array.prototype.forEach.call(el.children, tandai);
                return;
            }
            // Jangan beri .reveal pada kolom yang membungkus elemen sticky atau grid anak agar tidak bertabrakan saat scroll.
            if (el.querySelector('.grid, [class*="sticky"]')) return;
            tandai(el);
        });

        document.querySelectorAll('body > div.w-full .grid, main:not(.bx) .grid').forEach(function (grid) {
            if (skipContainer(grid) || grid.closest('.reveal')) return;
            if (grid.children.length < 2) return;
            // Jangan animasikan grid 12-kolom utama pembungkus layout halaman (kiri-kanan sticky sidebar).
            if (grid.classList.contains('lg:grid-cols-12')) return;
            if (!grid.hasAttribute('data-reveal-stagger')) {
                grid.setAttribute('data-reveal-stagger', '');
            }
            Array.prototype.forEach.call(grid.children, tandai);
        });

        var semua = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
        if (!semua.length) return;

        document.querySelectorAll('[data-reveal-stagger]').forEach(function (induk) {
            induk.querySelectorAll('.reveal').forEach(function (el, i) {
                if (!el.hasAttribute('data-reveal-delay')) {
                    var step = el.classList.contains('brand-card') ? 55
                        : el.classList.contains('step-card') ? 60
                        : el.classList.contains('vehicle-card') ? 70
                        : 90;
                    el.dataset.revealDelayBase = String(Math.min(i, 8) * step);
                }
            });
        });

        document.querySelectorAll('[data-reveal-delay]').forEach(function (el) {
            el.dataset.revealDelayBase = el.getAttribute('data-reveal-delay');
        });

        var tampilkan = function (el) {
            var delay = el.dataset.revealDelayBase || '0';
            el.style.transitionDelay = delay + 'ms';
            el.classList.add('is-visible');
            el.addEventListener('transitionend', function hapus(e) {
                if (e.target !== el) return;
                el.style.transitionDelay = '0ms';
                el.removeEventListener('transitionend', hapus);
            });
        };

        var sembunyikan = function (el) {
            el.style.transitionDelay = '0ms';
            el.classList.remove('is-visible');
        };

        if ('IntersectionObserver' in window) {
            var tampil = new IntersectionObserver(function (masuk) {
                masuk.forEach(function (item) {
                    var isWatermark = item.target.classList.contains('site-footer__watermark');
                    var minRatio = isWatermark ? 0.25 : 0.05;

                    if (item.isIntersecting && item.intersectionRatio >= minRatio) {
                        tampilkan(item.target);
                        if (!isWatermark) {
                            tampil.unobserve(item.target);
                        }
                    } else if (isWatermark && (!item.isIntersecting || item.intersectionRatio <= 0.04)) {
                        sembunyikan(item.target);
                    }
                });
            }, { threshold: [0, 0.05, 0.25] });

            semua.forEach(function (el) { tampil.observe(el); });
        } else {
            semua.forEach(tampilkan);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScrollReveal);
    } else {
        initScrollReveal();
    }
})();

// Animasi angka statistik: count-up animation (hitung naik halus dari 0 ke nilai asli setiap kali masuk viewport).
(function () {
    function initCounters() {
        var counters = Array.prototype.slice.call(document.querySelectorAll('[data-counter]'));
        if (!counters.length) return;

        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        var formatValue = function (val, decimals, suffix) {
            var formatted = decimals === 0
                ? String(Math.round(val))
                : val.toFixed(decimals);
            return formatted + suffix;
        };

        var resetCounter = function (el) {
            if (el._counterTimer) {
                clearTimeout(el._counterTimer);
                el._counterTimer = null;
            }
            if (el._counterRaf) {
                cancelAnimationFrame(el._counterRaf);
                el._counterRaf = null;
            }
            el.dataset.counterDone = '0';
            el.classList.remove('is-counting', 'is-counted');
            var decimals = parseInt(el.dataset.decimals || '0', 10);
            var suffix = el.dataset.suffix || '';
            el.textContent = formatValue(0, decimals, suffix);
        };

        var animate = function (el, delayMs) {
            if (el.dataset.counterDone === '1') return;
            el.dataset.counterDone = '1';

            var target = parseFloat(el.dataset.counter);
            if (Number.isNaN(target)) return;

            var decimals = parseInt(el.dataset.decimals || '0', 10);
            var suffix = el.dataset.suffix || '';
            var finalText = formatValue(target, decimals, suffix);

            // Skala durasi & easing agar angka kecil (mis. 2) maupun besar (1257) sama-sama terlihat jelas.
            var absTarget = Math.abs(target);
            var duration = absTarget <= 10 ? 750 : absTarget <= 100 ? 1150 : absTarget <= 500 ? 1400 : 1650;
            var easePow = absTarget <= 10 ? 1.5 : 3;

            var run = function () {
                var start = performance.now();
                el.classList.add('is-counting');

                var step = function (now) {
                    if (el.dataset.counterDone !== '1') return;
                    var progress = Math.min((now - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, easePow);
                    var value = target * eased;

                    if (progress >= 1) {
                        el.textContent = finalText;
                        el.classList.remove('is-counting');
                        el.classList.add('is-counted');
                        el._counterRaf = null;
                        return;
                    }

                    el.textContent = formatValue(value, decimals, suffix);
                    el._counterRaf = requestAnimationFrame(step);
                };

                el._counterRaf = requestAnimationFrame(step);
            };

            if (delayMs > 0) {
                el._counterTimer = setTimeout(run, delayMs);
            } else {
                run();
            }
        };

        // Siapkan nilai awal 0 sebelum masuk viewport sambil menjaga nilai asli untuk screen reader.
        counters.forEach(function (el) {
            var target = parseFloat(el.dataset.counter);
            if (Number.isNaN(target)) return;

            var decimals = parseInt(el.dataset.decimals || '0', 10);
            var suffix = el.dataset.suffix || '';
            var finalText = formatValue(target, decimals, suffix);

            if (!el.hasAttribute('aria-label')) {
                el.setAttribute('aria-label', finalText);
            }

            el.textContent = formatValue(0, decimals, suffix);
        });

        var triggerGroup = function (container) {
            var items = container.querySelectorAll('[data-counter]');
            items.forEach(function (el, idx) {
                var delay = reducedMotion ? idx * 80 : idx * 100 + 180;
                animate(el, delay);
            });
        };

        var resetGroup = function (container) {
            container.querySelectorAll('[data-counter]').forEach(resetCounter);
        };

        if (!('IntersectionObserver' in window)) {
            counters.forEach(function (el, idx) { animate(el, idx * 80); });
            return;
        }

        var groups = Array.prototype.slice.call(document.querySelectorAll('.stats-row'));
        var groupedCounters = new Set();

        groups.forEach(function (group) {
            group.querySelectorAll('[data-counter]').forEach(function (el) {
                groupedCounters.add(el);
            });

            var groupObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        triggerGroup(group);
                    } else {
                        var rect = entry.boundingClientRect;
                        if (rect.bottom <= 0 || rect.top >= window.innerHeight) {
                            resetGroup(group);
                        }
                    }
                });
            }, { threshold: 0.15 });

            groupObserver.observe(group);
        });

        var standalone = counters.filter(function (el) { return !groupedCounters.has(el); });
        if (standalone.length) {
            var itemObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        animate(entry.target, reducedMotion ? 0 : 150);
                    } else {
                        var rect = entry.boundingClientRect;
                        if (rect.bottom <= 0 || rect.top >= window.innerHeight) {
                            resetCounter(entry.target);
                        }
                    }
                });
            }, { threshold: 0.15 });

            standalone.forEach(function (el) { itemObserver.observe(el); });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCounters);
    } else {
        initCounters();
    }
})();

// Efek parallax halus saat hover pada media kartu (beranda klon Boxcars).
(function () {
    function initParallax() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (window.matchMedia('(hover: none), (pointer: coarse)').matches) return;

        var kartu = document.querySelectorAll('.bx .vehicle-card, .bx .step-card, .bx .promo-card, .bx .brand-card');
        if (!kartu.length) return;

        kartu.forEach(function (el) {
            var img = el.querySelector('.vehicle-card__media img, .step-card__media img, .promo-card > img, img');
            if (!img) return;

            el.addEventListener('mousemove', function (e) {
                var r = el.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                // Responsif mengikuti kursor agar terasa kedalaman (parallax).
                img.style.transitionDuration = '0.12s';
                img.style.setProperty('--px', (-x * 14).toFixed(2) + 'px');
                img.style.setProperty('--py', (-y * 14).toFixed(2) + 'px');
            });

            el.addEventListener('mouseleave', function () {
                img.style.transitionDuration = '';
                img.style.setProperty('--px', '0px');
                img.style.setProperty('--py', '0px');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initParallax);
    } else {
        initParallax();
    }
})();

// Lenis Inertia Scrolling (lerp halus) & Framer hero parallax (speed 80).
(function () {
    function initBoxcarsScroll() {
        document.querySelectorAll('.sidebar-track, [x-show]').forEach(function (el) {
            if (!el.hasAttribute('data-lenis-prevent')) {
                el.setAttribute('data-lenis-prevent', '');
            }
        });

        var lenis = new Lenis({
            lerp: 0.068,
            smoothWheel: true,
            wheelMultiplier: 0.95,
            touchMultiplier: 1.15,
            anchors: true,
        });
        window.lenis = lenis;

        var hero = document.querySelector('.bx .hero');
        var heroMedia = document.querySelector('.bx .hero__media');
        var stepCards = Array.prototype.slice.call(document.querySelectorAll('.bx .steps-stack .step-card'));
        var clamp = function (v, min, max) { return Math.min(max, Math.max(min, v)); };

        var updateParallax = function (scrollY) {
            if (!hero || !heroMedia) return;
            var h = hero.offsetHeight || window.innerHeight || 900;
            var clamped = Math.min(Math.max(scrollY, 0), h * 1.25);
            // Formula Framer __framer__speed: 80 => y = scrollY * (1 - 0.8) = scrollY * 0.2
            heroMedia.style.transform = 'translate3d(0, ' + (clamped * 0.2).toFixed(2) + 'px, 0)';
        };

        var updateStepStack = function () {
            if (stepCards.length < 2) return;
            if (window.matchMedia('(max-width: 1199px)').matches) {
                stepCards.forEach(function (card) {
                    card.style.setProperty('--stack-scale', '1');
                    card.style.filter = '';
                });
                return;
            }

            for (var i = 0; i < stepCards.length; i++) {
                var card = stepCards[i];
                var next = stepCards[i + 1];
                if (!next) {
                    card.style.setProperty('--stack-scale', '1');
                    card.style.filter = '';
                    continue;
                }

                var cardTop = parseFloat(getComputedStyle(card).top) || 110;
                var nextTop = next.getBoundingClientRect().top;
                // Progress 0 -> 1 saat kartu berikutnya naik menimpa kartu ini.
                var distance = 180;
                var p = clamp((cardTop + 40 - nextTop) / distance, 0, 1);
                var scale = 1 - p * 0.06;
                var dim = 1 - p * 0.08;
                card.style.setProperty('--stack-scale', scale.toFixed(4));
                card.style.filter = 'brightness(' + dim.toFixed(3) + ') saturate(' + (0.92 + 0.08 * (1 - p)).toFixed(3) + ')';
            }
        };

        lenis.on('scroll', function (e) {
            updateParallax(e.scroll);
            updateStepStack();
        });
        window.addEventListener('resize', updateStepStack);

        var raf = function (time) {
            lenis.raf(time);
            requestAnimationFrame(raf);
        };
        requestAnimationFrame(raf);
        updateParallax(window.scrollY || 0);
        updateStepStack();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBoxcarsScroll);
    } else {
        initBoxcarsScroll();
    }
})();
