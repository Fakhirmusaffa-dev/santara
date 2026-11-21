import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

/**
 * Notifikasi realtime: badge lonceng + toast kecil.
 * Kanal privat per-pengguna: user.{id}.notifications
 */
const userIdMeta = document.querySelector('meta[name="user-id"]');
if (userIdMeta) {
    const channel = window.Echo.private(
        `user.${userIdMeta.content}.notifications`,
    );

    // Saat halaman dibuka ulang, Reverb mengirim ulang badge terkini.
    channel.notification((payload) => {
        incrementBadge();
        showToast(payload);
    });

    // Tandai badge hilang saat halaman notifikasi dibuka.
    document.addEventListener("click", (e) => {
        if (e.target.closest('a[href*="notifikasi"]')) {
            window.Echo.private(`user.${userIdMeta.content}.notifications`);
        }
    });
}

function incrementBadge() {
    const badge = document.querySelector("[data-notif-badge]");
    if (!badge) {
        const bell = document.querySelector('a[aria-label="Notifikasi"]');
        if (!bell) return;
        const span = document.createElement("span");
        span.dataset.notifBadge = "";
        span.className =
            "absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white shadow-md shadow-red-600/40 ring-2 ring-white";
        bell.appendChild(span);
    }
    const el = document.querySelector("[data-notif-badge]");
    const current = parseInt(el.textContent) || 0;
    el.textContent = current + 1 > 9 ? "9+" : current + 1;
}

function showToast(payload) {
    const container = document.createElement("div");
    container.className =
        "reveal fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl border border-navy-900/10 bg-white/95 px-4 py-3.5 shadow-[0_24px_48px_-20px_rgba(16,27,45,0.35)] backdrop-blur";
    container.style.opacity = "1";

    const link = payload.url;
    const inner = document.createElement("div");
    inner.className = "flex items-start gap-3";
    inner.innerHTML = `
        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-navy-900 text-white text-sm">🔔</span>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800">${payload.title ?? "Notifikasi"}</p>
            <p class="mt-0.5 text-xs leading-relaxed text-slate-500">${payload.message ?? ""}</p>
        </div>
    `;
    container.appendChild(inner);

    if (link) {
        container.style.cursor = "pointer";
        container.addEventListener(
            "click",
            () => (window.location.href = link),
        );
    }

    document.body.appendChild(container);
    setTimeout(() => {
        container.classList.add("opacity-0");
        setTimeout(() => container.remove(), 400);
    }, 6000);
}
