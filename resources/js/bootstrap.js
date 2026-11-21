// Bootstrap minimal tanpa dependensi eksternal.
// CSRF untuk fetch: ambil token dari meta saat dibutuhkan.
window.santaraCsrfToken = () =>
    document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content") ?? "";
