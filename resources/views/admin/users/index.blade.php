{{-- resources/views/admin/users/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">Manajemen Pengguna</x-slot>

    <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="grid flex-1 gap-3 sm:grid-cols-5">
            <x-text-input name="q" :value="request('q')" placeholder="Cari nama, username, email" class="sm:col-span-2" />
            <x-select-input name="role">
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </x-select-input>
            <x-select-input name="status">
                <option value="">Semua status</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
            </x-select-input>
            <x-secondary-button type="submit" class="justify-center">Filter</x-secondary-button>
        </form>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.users.import') }}" class="btn-secondary"><x-icon name="upload" class="size-[18px]" />Impor santri (CSV)</a>
            <a href="{{ route('admin.users.create') }}" class="btn-primary"><x-icon name="plus" class="size-[18px]" />Tambah pengguna</a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-3xl bg-white ring-1 ring-hairline">
        <table class="min-w-full divide-y divide-hairline text-sm">
            <thead class="bg-cloud text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Peran</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Login terakhir</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hairline">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink">{{ $user->name }}</p>
                            <p class="text-xs text-slate">{{ $user->username }}{{ $user->email ? ' · '.$user->email : '' }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $user->primaryRole()?->label() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-success-wash text-blue' => $user->is_active,
                                'bg-pebble text-ink' => ! $user->is_active,
                            ])>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate">{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-blue hover:underline">Ubah</a>
                            @can('toggleStatus', $user)
                                <form method="POST" action="{{ route('admin.users.status', $user) }}" class="ml-3 inline"
                                      onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" @class([
                                        'font-medium hover:underline',
                                        'text-danger-ink' => $user->is_active,
                                        'text-blue' => ! $user->is_active,
                                    ])>{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate">Tidak ada pengguna yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links('pagination.peduli') }}</div>
</x-app-layout>
