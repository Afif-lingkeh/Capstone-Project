{{-- resources/views/admin/users/partials/form.blade.php --}}
@php($profile = $user->exists ? $user->santriProfile : null)

<div x-data="{ role: @js(old('role', $currentRole)) }" class="space-y-6">
    <section class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
        <h3 class="font-semibold">Data Akun</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="name" value="Nama lengkap" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" value="Peran" />
                <x-select-input id="role" name="role" x-model="role" class="mt-1 block w-full" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $currentRole) === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="username" value="Username" />
                <x-text-input id="username" name="username" class="mt-1 block w-full" :value="old('username', $user->username)" required />
                <p class="mt-1 text-xs text-slate" x-show="role === 'santri'">Untuk santri, isi dengan NIS.</p>
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email (opsional)" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" value="Nomor HP (opsional)" />
                <x-text-input id="phone" name="phone" class="mt-1 block w-full" :value="old('phone', $user->phone)" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="gender" value="Jenis kelamin" />
                <x-select-input id="gender" name="gender" class="mt-1 block w-full" required>
                    <option value="">Pilih</option>
                    @foreach ($genders as $gender)
                        <option value="{{ $gender->value }}" @selected(old('gender', $user->gender?->value) === $gender->value)>{{ $gender->label() }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
            </div>
        </div>
    </section>

    <section x-show="role === 'santri'" x-cloak class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
        <h3 class="font-semibold">Data Santri</h3>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="nis" value="NIS" />
                <x-text-input id="nis" name="nis" class="mt-1 block w-full" :value="old('nis', $profile?->nis)" x-bind:required="role === 'santri'" />
                <x-input-error :messages="$errors->get('nis')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="level" value="Jenjang" />
                <x-select-input id="level" name="level" class="mt-1 block w-full" x-bind:required="role === 'santri'">
                    <option value="">Pilih</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->value }}" @selected(old('level', $profile?->level?->value) === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </x-select-input>
                <x-input-error :messages="$errors->get('level')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="class_name" value="Kelas" />
                <x-text-input id="class_name" name="class_name" class="mt-1 block w-full" placeholder="mis. 8B" :value="old('class_name', $profile?->class_name)" x-bind:required="role === 'santri'" />
                <x-input-error :messages="$errors->get('class_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="entry_year" value="Tahun masuk" />
                <x-text-input id="entry_year" name="entry_year" type="number" class="mt-1 block w-full" :value="old('entry_year', $profile?->entry_year)" x-bind:required="role === 'santri'" />
                <x-input-error :messages="$errors->get('entry_year')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="guardian_name" value="Nama wali (opsional)" />
                <x-text-input id="guardian_name" name="guardian_name" class="mt-1 block w-full" :value="old('guardian_name', $profile?->guardian_name)" />
                <x-input-error :messages="$errors->get('guardian_name')" class="mt-2" />
            </div>
        </div>
    </section>

    <section class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline">
        <h3 class="font-semibold">Password</h3>
        @if ($user->exists)
            <p class="mt-1 text-sm text-slate">Kosongkan jika tidak ingin mengubah password.</p>
        @endif
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="password" value="Password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $user->exists" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi password" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $user->exists" />
            </div>
        </div>
        <p class="mt-2 text-xs text-slate">Minimal 8 karakter, berisi huruf dan angka.</p>
    </section>

    <div class="flex items-center gap-3">
        <x-primary-button>Simpan</x-primary-button>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-slate hover:underline">Batal</a>
    </div>
</div>
