<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ROLE_WAJIB_CABANG = ['pimpinan', 'admin-cabang', 'sales', 'service-advisor', 'mekanik'];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:40'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $users = User::with(['roles', 'branch'])
            ->when($validated['q'] ?? null, function ($query, $s) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('telp', 'like', "%{$s}%"));
            })
            ->when($validated['role'] ?? null, function ($query, $role) {
                if ($role === 'staf') {
                    $query->whereHas('roles', fn ($q) => $q->where('name', '!=', 'customer'));
                } elseif ($role === 'pelanggan') {
                    $query->where(fn ($q) => $q->whereDoesntHave('roles')
                        ->orWhereHas('roles', fn ($r) => $r->where('name', 'customer')));
                } else {
                    $query->whereHas('roles', fn ($q) => $q->where('name', $role));
                }
            })
            ->when($validated['branch_id'] ?? null, fn ($query, $bid) => $query->where('branch_id', $bid))
            ->orderByRaw('branch_id is null desc')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('aktif', true)->orderBy('nama')->get();
        $ringkasan = [
            'total' => User::count(),
            'staf' => User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'customer'))->count(),
            'pelanggan' => User::whereDoesntHave('roles')->orWhereHas('roles', fn ($q) => $q->where('name', 'customer'))->count(),
        ];

        return view('admin.users.index', compact('users', 'roles', 'branches', 'ringkasan'));
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $wajibCabang = in_array($request->input('role'), self::ROLE_WAJIB_CABANG, true);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'exists:roles,name'],
            'branch_id' => [$wajibCabang ? 'required' : 'nullable', 'exists:branches,id'],
            'telp' => ['nullable', 'string', 'max:20'],
        ], [
            'branch_id.required' => 'Role operasional cabang wajib memilih cabang penempatan.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'branch_id' => $data['role'] === 'customer' ? null : ($data['branch_id'] ?? null),
            'telp' => $data['telp'] ?? null,
        ]);
        $user->assignRole($data['role']);

        return redirect()->route('admin.users.index')->with('sukses', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'branches' => Branch::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $wajibCabang = in_array($request->input('role'), self::ROLE_WAJIB_CABANG, true);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'exists:roles,name'],
            'branch_id' => [$wajibCabang ? 'required' : 'nullable', 'exists:branches,id'],
            'telp' => ['nullable', 'string', 'max:20'],
        ], [
            'branch_id.required' => 'Role operasional cabang wajib memilih cabang penempatan.',
        ]);

        if ($user->id === $request->user()->id && $user->hasRole('superadmin') && $data['role'] !== 'superadmin') {
            return back()->withErrors(['role' => 'Anda tidak dapat menurunkan role Superadmin akun Anda sendiri.'])->withInput();
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'branch_id' => $data['role'] === 'customer' ? null : ($data['branch_id'] ?? null),
            'telp' => $data['telp'] ?? null,
        ] + ($data['password'] ? ['password' => Hash::make($data['password'])] : []));
        $user->syncRoles([$data['role']]);

        return redirect()->route('admin.users.index')->with('sukses', 'Data pengguna berhasil diperbarui.');
    }
}
