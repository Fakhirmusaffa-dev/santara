# Santara

**Sistem Pengaduan Sarana & Prasarana** — aplikasi berbasis Laravel untuk melaporkan, memproses, dan memantau pengaduan sarana dan prasarana secara terpusat.

## Fitur

- Pelaporan pengaduan sarana & prasarana oleh pengguna.
- Alur penanganan dan triase pengaduan oleh petugas/admin.
- Notifikasi status pengaduan.
- Dashboard dan manajemen data (CRUD) pengaduan.
- Antarmuka responsif berbasis Blade + Tailwind CSS.

## Teknologi

- PHP 8.3, Laravel
- Blade, Tailwind CSS, Vite
- MySQL / SQLite (sesuai konfigurasi `.env`)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

## Menjalankan Development

```bash
composer run dev
```

atau jalankan terpisah:

```bash
php artisan serve
npm run dev
```

## Testing

```bash
php artisan test
```

## License

Proyek internal Santara.
