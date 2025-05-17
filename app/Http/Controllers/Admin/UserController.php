<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage users');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::with('roles')->latest();

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('roles_badge', function ($user) {
                    $badges = '';
                    if ($user->roles->isNotEmpty()) {
                        foreach ($user->roles as $role) {
                            $badges .= '<span class="badge badge-sm bg-gradient-info me-1">' . htmlspecialchars($role->name) . '</span>';
                        }
                    } else {
                        $badges = '<span class="badge badge-sm bg-gradient-secondary">Belum ada role</span>';
                    }
                    return $badges;
                })
                ->addColumn('created_at_formatted', function ($user) {
                    return $user->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($user) {
                    $editUrl = route('admin.users.edit', $user->id);
                    $showUrl = route('admin.users.show', $user->id);
                    $deleteForm = '';

                    if (auth()->id() != $user->id) {
                        $deleteForm = '
                        <form action="' . route('admin.users.destroy', $user->id) . '" method="POST" class="d-inline delete-form">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                            <button type="submit" class="btn btn-link text-danger font-weight-bold text-xs p-0 m-0 align-baseline" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus User">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>';
                    }

                    return '
                        <a href="' . $editUrl . '" class="text-secondary font-weight-bold text-xs mx-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit User">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="' . $showUrl . '" class="text-info font-weight-bold text-xs mx-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat User">
                            <i class="fas fa-eye"></i> Lihat
                        </a>
                        ' . $deleteForm;
                })
                ->rawColumns(['roles_badge', 'action'])
                ->filter(function ($query) use ($request) {
                    if ($request->filled('search.value')) {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('nama', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%")
                                ->orWhere('nip', 'like', "%{$searchValue}%");
                        });
                    }
                })
                ->make(true);
        }
        return view('admin.users.index');
    }

    public function create()
    {
        $roles = Role::pluck('name', 'id'); // $roles dibutuhkan oleh form
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:users,nip'],
            'nomor_hp' => ['nullable', 'string', 'max:20', 'unique:users,nomor_hp'], // <-- VALIDASI BARU (sesuaikan max dan unique jika perlu)
            'gol' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.create')
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->only(['nama', 'email', 'nip', 'nomor_hp', 'gol', 'jabatan']);
        $userData['password'] = Hash::make($request->password);

        $user = User::create($userData);

        if ($request->filled('roles')) {
            $user->syncRoles(Role::whereIn('id', $request->roles)->get());
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }
    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'id'); // $roles dibutuhkan
        $userRoles = $user->roles->pluck('id')->toArray(); // $userRoles dibutuhkan
        return view('admin.users.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'nomor_hp' => ['nullable', 'string', 'max:20', Rule::unique('users', 'nomor_hp')->ignore($user->id)], // <-- VALIDASI BARU (sesuaikan max dan unique jika perlu)
            'gol' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.users.edit', $user->id)
                ->withErrors($validator)
                ->withInput();
        }

        $userData = $request->only(['nama', 'email', 'nip', 'nomor_hp', 'gol', 'jabatan']);
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        if ($request->filled('roles')) {
            $user->syncRoles(Role::whereIn('id', $request->roles)->get());
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() == $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        // Tambahkan logika lain jika perlu, misal tidak bisa hapus superadmin utama
        // if ($user->hasRole('superadmin') && $user->id === 1) {
        //    return redirect()->route('admin.users.index')->with('error', 'Super Admin utama tidak dapat dihapus.');
        // }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
