// Runs in <head> before the page paints, so there is no light/dark flash.
// (External file instead of inline script, so the Content-Security-Policy can block all inline scripts.)
(function () {
    var root = document.documentElement;
    var theme = "light";
    var preferred = root.getAttribute("data-default") || "system";
    root.classList.add("js");
    try {
        var saved = localStorage.getItem("theme");
        if (saved === "dark" || saved === "light") {
            theme = saved;
        } else if (preferred === "dark" || preferred === "light") {
            theme = preferred;
        } else if (window.matchMedia && matchMedia("(prefers-color-scheme: dark)").matches) {
            theme = "dark";
        }
    } catch (e) {}
    root.setAttribute("data-theme", theme);
})();
