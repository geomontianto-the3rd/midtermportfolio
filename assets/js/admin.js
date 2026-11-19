(() => {
    "use strict";

    // Confirm before any form that has data-confirm (delete buttons)
    document.querySelectorAll("form[data-confirm]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            if (!window.confirm(form.getAttribute("data-confirm"))) {
                event.preventDefault();
            }
        });
    });

    // Mobile sidebar toggle
    const btn = document.getElementById("adminMenuBtn");
    const nav = document.getElementById("adminNav");
    if (btn && nav) {
        btn.addEventListener("click", () => {
            const open = nav.classList.toggle("open");
            btn.setAttribute("aria-expanded", String(open));
        });
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && nav.classList.contains("open")) {
                nav.classList.remove("open");
                btn.setAttribute("aria-expanded", "false");
                btn.focus();
            }
        });
    }
})();
