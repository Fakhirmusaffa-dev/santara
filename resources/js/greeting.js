// ---------- Sapaan dinamis di topbar (dipindah dari inline script agar patuh CSP) ----------
// Elemen ditandai data-greeting-toolbar di layouts/topbar.blade.php.
export function initGreetingToolbar(root = document) {
    const segments = [
        {
            until: 4,
            greet: "Selamat malam",
            emoji: "🌙",
            msg: "Terima kasih sudah tetap berjaga malam ini.",
        },
        {
            until: 11,
            greet: "Selamat pagi",
            emoji: "🌅",
            msg: "Semoga hari ini lancar dan penuh keberkahan.",
        },
        {
            until: 15,
            greet: "Selamat siang",
            emoji: "☀️",
            msg: "Semoga aktivitas hari ini berjalan dengan baik.",
        },
        {
            until: 18,
            greet: "Selamat sore",
            emoji: "🌤️",
            msg: "Semangat menyelesaikan tugas hari ini, ya!",
        },
        {
            until: 24,
            greet: "Selamat malam",
            emoji: "🌙",
            msg: "Terima kasih atas kerja kerasmu hari ini.",
        },
    ];

    if (!root.querySelector("[data-greeting-toolbar]")) {
        return;
    }

    const update = () => {
        const now = new Date(
            new Date().toLocaleString("en-US", { timeZone: "Asia/Jakarta" }),
        );
        const hour = now.getHours();
        const segment =
            segments.find((s) => hour < s.until) ??
            segments[segments.length - 1];

        const set = (selector, value) => {
            const el = root.querySelector(selector);
            if (el) el.textContent = value;
        };

        set('[data-greeting="text"]', segment.greet);
        set('[data-greeting="emoji"]', segment.emoji);
        set('[data-greeting="message"]', segment.msg);
        set(
            '[data-greeting="clock"]',
            String(hour).padStart(2, "0") +
                ":" +
                String(now.getMinutes()).padStart(2, "0"),
        );
    };

    update();
    setInterval(update, 15000);
}
