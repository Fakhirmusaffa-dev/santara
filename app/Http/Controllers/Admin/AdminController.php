<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Location;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\ComplaintRouter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    // ---------- Users ----------
    public function users(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name', 'like', '%'.$request->get('search').'%')
                ->orWhere('email', 'like', '%'.$request->get('search').'%')))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->get('role')))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.users.index', ['users' => $users, 'filters' => $request->only(['search', 'role'])]);
    }

    public function createUser(): View
    {
        return view('admin.users.create');
    }

    /** Tampilkan detail akun (Read). */
    public function showUser(User $user): View
    {
        $user->loadCount([
            'complaints',
            'assignedComplaints',
            'handlingLogs',
        ]);

        return view('admin.users.show', ['user' => $user]);
    }

    /** Form ubah akun (bagian Update dari CRUD). */
    public function editUser(User $user): View
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /** Simpan perubahan akun (Update). */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            // Cast 'hashed' pada model sudah meng-hash sekali.
            $payload['password'] = $validated['password'];
        }

        // Checkbox is_active: dicentang => true, tidak => false.
        $payload['is_active'] = $request->boolean('is_active');

        // Proteksi: tidak boleh menonaktifkan / mengubah role diri sendiri.
        if ((int) $user->id === (int) $request->user()->id) {
            $payload['is_active'] = $user->is_active;
            $payload['role'] = $user->role;
        }

        $user->update($payload);

        ActivityLogger::log('user_updated', "Akun {$user->email} diperbarui", User::class, $user->id, [], $request);

        return redirect()->route('admin.users.show', $user)->with('success', "Akun {$user->name} diperbarui.");
    }

    /** Hapus akun (Delete). Diblokir bila masih dipakai data lain. */
    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return back()->withErrors(['user' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $usage = [
            'laporan dibuat' => $user->complaints()->count(),
            'laporan ditangani' => $user->assignedComplaints()->count(),
            'penugasan' => $user->assignments()->count(),
            'catatan penanganan' => $user->handlingLogs()->count(),
        ];
        $used = array_filter($usage);

        if ($used !== []) {
            $rincian = collect($used)->map(fn ($n, $k) => "{$n} {$k}")->join(', ');

            return back()->withErrors(['user' => "Akun tidak dapat dihapus karena masih dipakai: {$rincian}. Nonaktifkan saja."]);
        }

        $label = "{$user->name} <{$user->email}>";
        $user->delete();

        ActivityLogger::log('user_deleted', "Akun {$label} dihapus", null, null, [], $request);

        return redirect()->route('admin.users.index')->with('success', "Akun {$label} dihapus.");
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(User::ROLES)],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'position' => $validated['position'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        ActivityLogger::log('user_created', "Akun {$user->email} ({$user->role}) dibuat", User::class, $user->id, [], $request);

        return redirect()->route('admin.users.index')->with('success', "Akun {$user->name} dibuat.");
    }

    public function toggleUser(Request $request, User $user): RedirectResponse
    {
        if ((int) $user->id === (int) $request->user()->id) {
            return back()->withErrors(['user' => 'Tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        ActivityLogger::log($user->is_active ? 'user_activated' : 'user_deactivated', "Akun {$user->email}", User::class, $user->id, [], $request);

        return back()->with('success', "Status akun {$user->name} diperbarui.");
    }

    // ---------- Categories (CRUD lengkap) ----------
    public function categories(Request $request): View
    {
        $categories = Category::withCount('complaints')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->get('search').'%'))
            ->orderBy('name')->paginate(12)->withQueryString();

        // Koordinator aktif per divisi, untuk ditampilkan pada opsi "diteruskan ke".
        $koordinators = User::whereIn('role', User::KOORDINATOR_ROLES)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role'])
            ->groupBy('role');

        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
            'koordinators' => $koordinators,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_sensitive' => ['sometimes', 'boolean'],
            'koordinator_role' => ['nullable', Rule::in(User::KOORDINATOR_ROLES)],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_sensitive' => $request->boolean('is_sensitive'),
            // Divisi penerima laporan (null = tetap lewat admin).
            'koordinator_role' => ComplaintRouter::normalizeRole($validated['koordinator_role'] ?? null),
        ]);

        ActivityLogger::log('category_created', "Kategori {$validated['name']} dibuat", Category::class, $category->id, [], $request);

        return redirect()->route('admin.categories.show', $category)->with('success', 'Kategori ditambahkan.');
    }

    /** Detail kategori + daftar laporan terkait (Read). */
    public function showCategory(Category $category): View
    {
        $category->loadCount('complaints');
        $complaints = $category->complaints()->with(['reporter', 'location'])->latest()->paginate(10);

        return view('admin.categories.show', compact('category', 'complaints'));
    }

    /** Form ubah kategori (bagian Update dari CRUD). */
    public function editCategory(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    /** Simpan perubahan kategori (Update). */
    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_sensitive' => ['sometimes', 'boolean'],
            'koordinator_role' => ['nullable', Rule::in(User::KOORDINATOR_ROLES)],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
            // Kategori bertanda ini membuat laporan otomatis sensitif & mendesak.
            'is_sensitive' => $request->boolean('is_sensitive'),
            // Divisi penerima laporan (null = tetap lewat admin).
            'koordinator_role' => ComplaintRouter::normalizeRole($validated['koordinator_role'] ?? null),
        ]);

        ActivityLogger::log('category_updated', "Kategori {$category->name} diperbarui", Category::class, $category->id, [], $request);

        return redirect()->route('admin.categories.show', $category)->with('success', 'Kategori diperbarui.');
    }

    /** Hapus kategori (Delete). Diblokir bila masih dipakai laporan. */
    public function destroyCategory(Request $request, Category $category): RedirectResponse
    {
        $dipakai = $category->complaints()->count();
        if ($dipakai > 0) {
            return back()->withErrors(['category' => "Kategori tidak dapat dihapus karena dipakai {$dipakai} laporan. Nonaktifkan saja."]);
        }

        $name = $category->name;
        $category->delete();

        ActivityLogger::log('category_deleted', "Kategori {$name} dihapus", null, null, [], $request);

        return redirect()->route('admin.categories.index')->with('success', "Kategori {$name} dihapus.");
    }

    public function toggleCategory(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', 'Status kategori diperbarui.');
    }

    // ---------- Locations (CRUD lengkap) ----------
    public function locations(Request $request): View
    {
        $locations = Location::withCount('complaints')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name', 'like', '%'.$request->get('search').'%')
                ->orWhere('code', 'like', '%'.$request->get('search').'%')))
            ->when($request->filled('area') && in_array($request->get('area'), ['putra', 'putri', 'umum'], true),
                fn ($q) => $q->where('area', $request->get('area')))
            ->orderBy('area')->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.locations.index', [
            'locations' => $locations,
            'filters' => $request->only(['search', 'area']),
            'areas' => Location::AREAS,
        ]);
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:locations,name'],
            'code' => ['nullable', 'string', 'max:30', 'unique:locations,code'],
            'area' => ['required', 'string', 'in:putra,putri,umum'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $location = Location::create($validated);

        ActivityLogger::log('location_created', "Lokasi {$validated['name']} dibuat", Location::class, $location->id, [], $request);

        return redirect()->route('admin.locations.show', $location)->with('success', 'Lokasi ditambahkan.');
    }

    /** Detail lokasi + daftar laporan terkait (Read). */
    public function showLocation(Location $location): View
    {
        $location->loadCount('complaints');
        $complaints = $location->complaints()->with(['reporter', 'category'])->latest()->paginate(10);

        return view('admin.locations.show', compact('location', 'complaints'));
    }

    /** Form ubah lokasi (bagian Update dari CRUD). */
    public function editLocation(Location $location): View
    {
        return view('admin.locations.edit', ['location' => $location]);
    }

    /** Simpan perubahan lokasi (Update). */
    public function updateLocation(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('locations', 'name')->ignore($location->id)],
            'code' => ['nullable', 'string', 'max:30', Rule::unique('locations', 'code')->ignore($location->id)],
            'area' => ['required', 'string', 'in:putra,putri,umum'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $location->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'area' => $validated['area'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLogger::log('location_updated', "Lokasi {$location->name} diperbarui", Location::class, $location->id, [], $request);

        return redirect()->route('admin.locations.show', $location)->with('success', 'Lokasi diperbarui.');
    }

    /** Hapus lokasi (Delete). Diblokir bila masih dipakai laporan. */
    public function destroyLocation(Request $request, Location $location): RedirectResponse
    {
        $dipakai = $location->complaints()->count();
        if ($dipakai > 0) {
            return back()->withErrors(['location' => "Lokasi tidak dapat dihapus karena dipakai {$dipakai} laporan. Nonaktifkan saja."]);
        }

        $name = $location->name;
        $location->delete();

        ActivityLogger::log('location_deleted', "Lokasi {$name} dihapus", null, null, [], $request);

        return redirect()->route('admin.locations.index')->with('success', "Lokasi {$name} dihapus.");
    }

    public function toggleLocation(Location $location): RedirectResponse
    {
        $location->update(['is_active' => ! $location->is_active]);

        return back()->with('success', 'Status lokasi diperbarui.');
    }

    // ---------- Monitoring ----------
    public function monitoring(Request $request): View
    {
        $complaints = Complaint::with(['category', 'location', 'reporter', 'assignee'])
            ->where('sensitivity', 'biasa')
            ->whereNotIn('status', ['selesai', 'ditolak', 'ditutup'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderByRaw("CASE priority WHEN 'mendesak' THEN 0 WHEN 'tinggi' THEN 1 WHEN 'sedang' THEN 2 ELSE 3 END")
            ->latest()->paginate(12)->withQueryString();

        return view('admin.monitoring', ['complaints' => $complaints]);
    }

    public function activities(): View
    {
        $logs = ActivityLog::with('user')->latest()->paginate(15);

        return view('admin.activities', ['logs' => $logs]);
    }
}
