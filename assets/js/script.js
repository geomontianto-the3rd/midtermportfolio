(() => {
    "use strict";

    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    const root = document.documentElement;
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    // localStorage can throw (private mode, blocked cookies), so wrap it
    const store = {
        get(key) {
            try { return localStorage.getItem(key); } catch (e) { return null; }
        },
        set(key, value) {
            try { localStorage.setItem(key, value); } catch (e) { /* ignore */ }
        }
    };


    // ===============================
    // DARK / LIGHT MODE
    // ===============================
    const themeBtn = $("#themeBtn");
    const themeMeta = $('meta[name="theme-color"]');
    const systemDark = window.matchMedia("(prefers-color-scheme: dark)");

    function applyTheme(theme) {
        root.setAttribute("data-theme", theme);

        if (themeBtn) {
            const isDark = theme === "dark";
            themeBtn.setAttribute("aria-pressed", String(isDark));
            themeBtn.setAttribute("aria-label", isDark ? "Switch to light mode" : "Switch to dark mode");
        }

        if (themeMeta) {
            themeMeta.setAttribute("content", theme === "dark" ? "#08110f" : "#e9f0ed");
        }
    }

    applyTheme(root.getAttribute("data-theme") === "dark" ? "dark" : "light");

    if (themeBtn) {
        themeBtn.addEventListener("click", () => {
            const next = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
            applyTheme(next);
            store.set("theme", next);
        });
    }

    // Follow the system setting until the visitor picks a theme themselves
    const defaultTheme = root.getAttribute("data-default") || "system";
    const onSystemChange = (event) => {
        const saved = store.get("theme");
        if (defaultTheme === "system" && saved !== "dark" && saved !== "light") {
            applyTheme(event.matches ? "dark" : "light");
        }
    };
    if (systemDark.addEventListener) {
        systemDark.addEventListener("change", onSystemChange);
    } else if (systemDark.addListener) {
        systemDark.addListener(onSystemChange);
    }


    // ===============================
    // MOBILE NAVIGATION
    // ===============================
    const menuBtn = $("#menuBtn");
    const navMenu = $("#navMenu");

    function setMenu(open) {
        if (!menuBtn || !navMenu) return;
        navMenu.classList.toggle("open", open);
        menuBtn.setAttribute("aria-expanded", String(open));
        menuBtn.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    }

    if (menuBtn && navMenu) {
        menuBtn.addEventListener("click", () => {
            setMenu(!navMenu.classList.contains("open"));
        });

        // Close after choosing a link
        $$("a", navMenu).forEach((link) => {
            link.addEventListener("click", () => setMenu(false));
        });

        // Close when clicking outside
        document.addEventListener("click", (event) => {
            if (
                navMenu.classList.contains("open") &&
                !navMenu.contains(event.target) &&
                !menuBtn.contains(event.target)
            ) {
                setMenu(false);
            }
        });

        // Close with Escape and return focus to the button
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && navMenu.classList.contains("open")) {
                setMenu(false);
                menuBtn.focus();
            }
        });

        // Reset when the layout switches back to desktop
        window.matchMedia("(min-width: 861px)").addEventListener?.("change", (event) => {
            if (event.matches) setMenu(false);
        });
    }


    // ===============================
    // TYPING EFFECT
    // ===============================
    const typing = $("#typing");

    if (typing && !reduceMotion.matches) {
        const words = (typing.getAttribute("data-words") || "")
            .split("|").map((w) => w.trim()).filter(Boolean);
        if (!words.length) words.push(typing.textContent.trim() || "Web Developer");
        let wordIndex = 0;
        let charIndex = 0;
        let deleting = false;

        const tick = () => {
            const word = words[wordIndex];

            if (!deleting) {
                charIndex++;
                typing.textContent = word.slice(0, charIndex);

                if (charIndex === word.length) {
                    deleting = true;
                    setTimeout(tick, 1600);
                    return;
                }
                setTimeout(tick, 90);
            } else {
                charIndex--;
                typing.textContent = word.slice(0, charIndex);

                if (charIndex === 0) {
                    deleting = false;
                    wordIndex = (wordIndex + 1) % words.length;
                    setTimeout(tick, 350);
                    return;
                }
                setTimeout(tick, 45);
            }
        };

        typing.textContent = "";
        tick();
    }


    // ===============================
    // SKILL BARS (fill when visible)
    // ===============================
    const skills = $$(".skill");

    if ("IntersectionObserver" in window && !reduceMotion.matches) {
        const skillObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("in-view");
                    skillObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        skills.forEach((skill) => skillObserver.observe(skill));
    } else {
        skills.forEach((skill) => skill.classList.add("in-view"));
    }


    // ===============================
    // ACTIVE NAVIGATION + SCROLL TO TOP
    // ===============================
    const navLinks = $$("#navMenu a");
    const sections = navLinks
        .map((link) => document.getElementById(link.hash.slice(1)))
        .filter(Boolean);
    const scrollTopBtn = $("#scrollTop");

    function updateOnScroll() {
        const marker = window.scrollY + window.innerHeight * 0.33;
        let current = sections[0];

        sections.forEach((section) => {
            const top = section.getBoundingClientRect().top + window.scrollY;
            if (top <= marker) current = section;
        });

        // At the very bottom, the last section should win
        if (window.innerHeight + window.scrollY >= root.scrollHeight - 4) {
            current = sections[sections.length - 1];
        }

        navLinks.forEach((link) => {
            const isActive = current && link.hash === "#" + current.id;
            link.classList.toggle("active", isActive);
            if (isActive) {
                link.setAttribute("aria-current", "true");
            } else {
                link.removeAttribute("aria-current");
            }
        });

        if (scrollTopBtn) {
            scrollTopBtn.classList.toggle("show", window.scrollY > 400);
        }
    }

    let ticking = false;
    const requestUpdate = () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            updateOnScroll();
            ticking = false;
        });
    };

    window.addEventListener("scroll", requestUpdate, { passive: true });
    window.addEventListener("resize", requestUpdate);
    updateOnScroll();

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: reduceMotion.matches ? "auto" : "smooth" });
        });
    }


    // ===============================
    // CONTACT FORM
    // ===============================
    const form = $("#contactForm");

    if (form) {
        const status = $("#formMessage");
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        const rules = {
            name: (value) => (value ? "" : "Enter your name."),
            email: (value) => {
                if (!value) return "Enter your email address.";
                return emailPattern.test(value) ? "" : "Enter a valid email address, like you@example.com.";
            },
            subject: (value) => (value ? "" : "Enter a subject."),
            message: (value) => {
                if (!value) return "Write a message.";
                return value.length >= 10 ? "" : "Write at least 10 characters.";
            }
        };

        const fieldEl = (id) => $("#" + id, form);

        function setError(id, message) {
            const input = fieldEl(id);
            const error = $("#" + id + "-error");
            if (!input || !error) return;
            error.textContent = message;
            if (message) {
                input.setAttribute("aria-invalid", "true");
            } else {
                input.removeAttribute("aria-invalid");
            }
        }

        function validateField(id) {
            const message = rules[id](fieldEl(id).value.trim());
            setError(id, message);
            return message === "";
        }

        function showStatus(text, isError) {
            status.textContent = text;
            status.classList.toggle("is-error", Boolean(isError));
        }

        Object.keys(rules).forEach((id) => {
            const input = fieldEl(id);
            if (!input) return;

            // Check when leaving a field; clear the error as soon as it's fixed
            input.addEventListener("blur", () => {
                if (input.value.trim() !== "" || input.hasAttribute("aria-invalid")) {
                    validateField(id);
                }
            });
            input.addEventListener("input", () => {
                if (input.hasAttribute("aria-invalid")) validateField(id);
            });
        });

        form.addEventListener("submit", (event) => {
            const results = Object.keys(rules).map((id) => ({ id, valid: validateField(id) }));
            const firstInvalid = results.find((result) => !result.valid);

            if (firstInvalid) {
                // Browser-side check failed: stop here and help the visitor
                event.preventDefault();
                showStatus("Fix the highlighted fields and try again.", true);
                fieldEl(firstInvalid.id).focus();
                return;
            }

            // Valid: let the browser POST the form to contact.php (the server validates again)
            const button = $("button[type=submit]", form);
            if (button) {
                button.disabled = true;
                button.textContent = "Sending...";
            }
        });
    }


    // ===============================
    // FOOTER YEAR
    // ===============================
    const year = $("#year");
    if (year) year.textContent = String(new Date().getFullYear());
})();