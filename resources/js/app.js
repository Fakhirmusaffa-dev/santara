import "./bootstrap";
import "./echo";
import { initGreetingToolbar } from "./greeting";

// Dropdown + drawer mobile + modal konfirmasi, tanpa framework tambahan.
const start = () => {
    // Sapaan & jam di topbar (pengganti inline script yang diblokir CSP).
    initGreetingToolbar();

    // Drawer mobile
    const drawer = document.getElementById("mobile-drawer");
    const openBtn = document.getElementById("drawer-open");
    const closeBtn = document.getElementById("drawer-close");
    const backdrop = document.getElementById("drawer-backdrop");

    const setDrawer = (open) => {
        if (!drawer) return;
        drawer.classList.toggle("-translate-x-full", !open);
        backdrop?.classList.toggle("hidden", !open);
        document.body.classList.toggle("overflow-hidden", open);
    };

    openBtn?.addEventListener("click", () => setDrawer(true));
    closeBtn?.addEventListener("click", () => setDrawer(false));
    backdrop?.addEventListener("click", () => setDrawer(false));
    document.getElementById("tabbar-menu")?.addEventListener("click", () => setDrawer(true));

    // Dropdown user
    const userBtn = document.getElementById("user-menu-btn");
    const userMenu = document.getElementById("user-menu");
    userBtn?.addEventListener("click", (e) => {
        e.stopPropagation();
        userMenu?.classList.toggle("hidden");
    });
    document.addEventListener("click", (e) => {
        if (
            !userMenu?.classList.contains("hidden") &&
            !userMenu?.contains(e.target)
        ) {
            userMenu?.classList.add("hidden");
        }
    });

    // Pratinjau unggahan foto
    document
        .querySelectorAll('input[type="file"][data-preview]')
        .forEach((input) => {
            input.addEventListener("change", () => {
                const target = document.getElementById(input.dataset.preview);
                if (!target) return;
                target.innerHTML = "";
                [...(input.files ?? [])].slice(0, 4).forEach((file) => {
                    if (!file.type.startsWith("image/")) return;
                    const img = document.createElement("img");
                    img.src = URL.createObjectURL(file);
                    img.alt = file.name;
                    img.className =
                        "h-20 w-20 rounded-lg border border-slate-200 object-cover";
                    target.appendChild(img);
                });
            });
        });

    // ---------- Chip kategori (form lapor publik) ----------
    // Select asli tetap jadi sumber data & nilai yang dikirim; chip hanya
    // representasi visualnya supaya pelapor memilih lebih cepat daripada dropdown.
    const chipHost = document.getElementById("category-chips");
    const categorySelect = document.getElementById("category_id");

    if (chipHost && categorySelect) {
        const chips = [...categorySelect.options]
            .filter((opt) => opt.value !== "")
            .map((opt) => {
                const chip = document.createElement("button");
                chip.type = "button";
                chip.dataset.value = opt.value;
                chip.textContent = opt.textContent.trim();
                chip.className =
                    "chip-cat rounded-lg border border-white/70 bg-white/60 px-3 py-1.5 text-[12.5px] font-medium text-slate-700 transition";
                chip.setAttribute("aria-pressed", "false");
                return chip;
            });

        const paint = (value) => {
            chips.forEach((chip) => {
                const on = chip.dataset.value === value;
                chip.setAttribute("aria-pressed", on ? "true" : "false");
                chip.classList.toggle("is-picked", on);
            });
        };

        chips.forEach((chip) => {
            chip.addEventListener("click", () => {
                categorySelect.value = chip.dataset.value;
                categorySelect.dispatchEvent(new Event("change", { bubbles: true }));
                paint(chip.dataset.value);
            });
            chipHost.appendChild(chip);
        });

        // Kembalikan pilihan setelah validasi gagal (old input).
        paint(categorySelect.value);
    }

    // Konfirmasi aksi penting
    document.querySelectorAll("[data-confirm]").forEach((el) => {
        el.addEventListener("click", (e) => {
            if (!confirm(el.getAttribute("data-confirm"))) {
                e.preventDefault();
            }
        });
    });

    // ---------- Overlay loading berlogo ----------
    // Dipakai saat mengirim laporan, login staf, dan navigasi halaman.
    const loaderEl = document.getElementById("santara-loader");
    const loaderText = document.getElementById("santara-loader-text");
    let loaderTimer = null;

    const Loader = {
        show(message) {
            if (!loaderEl) return;
            if (message && loaderText) loaderText.textContent = message;
            loaderEl.hidden = false;
            // Beri waktu satu frame agar transisi opacity berjalan.
            requestAnimationFrame(() => loaderEl.classList.add("is-visible"));
        },
        hide() {
            if (!loaderEl) return;
            loaderEl.classList.remove("is-visible");
            clearTimeout(loaderTimer);
            loaderTimer = setTimeout(() => {
                loaderEl.hidden = true;
            }, 240);
        },
    };

    window.SantaraLoader = Loader;

    // Overlay hanya untuk aksi yang menunggu server: kirim form yang ditandai data-loading.
    // (Navigasi antarhalaman sengaja tidak memakai loader.)
    document.querySelectorAll("form[data-loading]").forEach((form) => {
        form.addEventListener("submit", () => {
            // Form yang gagal validasi HTML tidak sampai ke sini.
            if (!form.checkValidity || form.checkValidity()) {
                form.classList.add("is-loading");
                Loader.show(form.getAttribute("data-loading") || "Memproses…");
            }
        });
    });

    // Jika halaman dipulihkan dari cache browser, pastikan overlay tidak tertinggal.
    window.addEventListener("pageshow", () => {
        Loader.hide();
        document.querySelectorAll("form.is-loading").forEach((f) => f.classList.remove("is-loading"));
    });

    // Toast otomatis hilang
    setTimeout(() => {
        document
            .querySelectorAll("[data-toast]")
            .forEach((t) => t.classList.add("opacity-0"));
    }, 4200);
};

// Jalankan saat DOM siap — termasuk bila modul dimuat setelah DOMContentLoaded.
const boot = () => {
    if (window.__santaraBooted) return;
    window.__santaraBooted = true;
    start();
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot, { once: true });
} else {
    boot();
}
