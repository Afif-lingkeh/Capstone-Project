{{-- resources/views/profile/edit.blade.php --}}
{{-- Desain "03 Santri · Profil Saya": foto, data santri (diatur admin), info profil, dan ubah password. --}}
@php($sp = $user->santriProfile)
<x-app-layout>
    <x-slot name="header">Profil Saya</x-slot>

    <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
        <div class="flex flex-col gap-6 xl:w-[380px]">
            {{-- Foto profil --}}
            <section aria-labelledby="foto" class="flex flex-col items-center gap-4 rounded-3xl bg-white p-6 text-center ring-1 ring-inset ring-hairline"
                     x-data="avatarPicker()" @keydown.escape.window="closeCamera()">
                <h2 id="foto" class="sr-only">Foto profil</h2>
                <div class="relative">
                    <template x-if="preview"><img :src="preview" alt="Pratinjau foto baru" class="size-28 rounded-full object-cover outline outline-1 -outline-offset-1 outline-black/10"></template>
                    <div x-show="! preview"><x-avatar :user="$user" size="size-28" text="text-3xl" /></div>
                </div>
                <div class="flex flex-col gap-0.5">
                    <p class="text-lg font-extrabold">{{ $user->name }}</p>
                    <p class="text-sm text-slate">{{ $user->primaryRole()?->label() }}{{ $sp ? ' · '.$sp->level->label().' Kelas '.$sp->class_name : '' }}</p>
                </div>

                <form method="POST" action="{{ route('avatar.update') }}" enctype="multipart/form-data" class="flex w-full flex-col gap-3">
                    @csrf
                    <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-1">
                        <label for="avatar" class="btn-secondary w-full cursor-pointer px-4 has-[:focus-visible]:outline has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-blue">
                            <x-icon name="upload" class="size-[18px]" />{{ $user->avatar_path ? 'Pilih dari perangkat' : 'Unggah foto' }}
                            <input id="avatar" x-ref="input" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" required @change="picked($event.target.files[0])">
                        </label>
                        {{-- Tampil hanya jika browser mendukung kamera (butuh HTTPS atau localhost). --}}
                        <button type="button" x-show="cameraSupported" x-cloak @click="openCamera()" class="btn-secondary w-full px-4">
                            <x-icon name="camera" class="size-[18px]" />Gunakan kamera
                        </button>
                    </div>
                    <p x-show="name" x-cloak class="break-all text-xs font-semibold text-slate" x-text="name"></p>
                    <x-primary-button x-show="preview" x-cloak class="w-full">Simpan foto</x-primary-button>

                    {{-- Jendela kamera: <dialog> bawaan browser menangani fokus & tombol Esc. --}}
                    <dialog x-ref="dialog" @close="stopStream()" aria-labelledby="judul-kamera"
                            class="w-[min(92vw,440px)] overflow-hidden rounded-3xl bg-white p-0 text-ink shadow-float backdrop:bg-ink/60">
                        <div class="flex items-center justify-between gap-3 border-b border-hairline px-5 py-4">
                            <h3 id="judul-kamera" class="text-base font-extrabold">Ambil foto dengan kamera</h3>
                            <button type="button" @click="closeCamera()" class="flex size-10 items-center justify-center rounded-xl hover:bg-pebble"><x-icon name="x" /><span class="sr-only">Tutup kamera</span></button>
                        </div>
                        <div class="flex flex-col gap-4 p-5">
                            <div class="relative aspect-square w-full overflow-hidden rounded-2xl bg-ink">
                                <video x-ref="video" x-show="! shot" playsinline muted autoplay class="size-full object-cover" :class="facing === 'user' && '-scale-x-100'"></video>
                                <img x-show="shot" :src="shot" alt="Hasil foto" class="size-full object-cover">
                                {{-- Panduan bingkai wajah --}}
                                <div x-show="! shot && streaming" aria-hidden="true" class="pointer-events-none absolute inset-[12%] rounded-full border-2 border-dashed border-white/70"></div>
                                <p x-show="status" x-text="status" role="status" class="absolute inset-x-4 top-1/2 -translate-y-1/2 rounded-2xl bg-white/95 p-4 text-center text-sm font-semibold"></p>
                            </div>
                            <p class="text-xs leading-[1.5] text-slate">Posisikan wajah di dalam lingkaran dengan pencahayaan dari depan. Foto baru tersimpan setelah kamu menekan “Simpan foto”.</p>
                            <div class="flex flex-wrap gap-2.5">
                                <template x-if="! shot">
                                    <div class="flex w-full gap-2.5">
                                        <button type="button" x-show="hasMultipleCameras" @click="switchCamera()" class="btn-ghost size-[52px] shrink-0 p-0 ring-1 ring-inset ring-hairline"><x-icon name="refresh-cw" /><span class="sr-only">Ganti kamera depan/belakang</span></button>
                                        <button type="button" @click="capture()" :disabled="! streaming" class="btn-primary flex-1"><x-icon name="camera" class="size-[18px]" />Ambil foto</button>
                                    </div>
                                </template>
                                <template x-if="shot">
                                    <div class="flex w-full gap-2.5">
                                        <button type="button" @click="retake()" class="btn-secondary flex-1">Ambil ulang</button>
                                        <button type="button" @click="usePhoto()" class="btn-primary flex-1"><x-icon name="check" class="size-[18px]" />Gunakan foto ini</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </dialog>
                    <x-input-error :messages="$errors->get('avatar')" />
                    <p class="text-xs leading-[1.5] text-slate">JPG, PNG, atau WebP, maks. 2 MB. Gunakan foto yang sopan dengan wajah terlihat jelas. Foto santri hanya terlihat oleh ustadz/ustadzah dan admin pesantren.</p>
                </form>
                @if ($user->avatar_path)
                    <form method="POST" action="{{ route('avatar.destroy') }}" onsubmit="return confirm('Hapus foto profil?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="py-1 text-sm font-bold text-danger-ink hover:underline">Hapus foto</button>
                    </form>
                @endif
            </section>

            @if ($sp)
                <x-card title="Data santri">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-xs font-bold text-slate">NIS</dt><dd class="font-mono font-bold">{{ $sp->nis }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Jenjang & kelas</dt><dd class="font-semibold">{{ $sp->level->label() }} · {{ $sp->class_name }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Tahun masuk</dt><dd class="font-semibold">{{ $sp->entry_year }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate">Wali</dt><dd class="font-semibold">{{ $sp->guardian_name ?: '—' }}</dd></div>
                    </dl>
                    <p class="mt-4 text-xs leading-[1.5] text-slate">Ada yang keliru? Sampaikan ke admin pesantren untuk diperbaiki.</p>
                </x-card>
            @endif
        </div>

        <div class="flex flex-1 flex-col gap-6">
            <div class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="rounded-3xl bg-white p-6 ring-1 ring-inset ring-hairline sm:p-8">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    <script>
        // Pilih foto dari galeri atau kamera. Hasil kamera dipotong persegi (800×800 JPEG) lalu dimasukkan ke
        // input file yang sama, sehingga tetap melewati validasi server seperti unggahan biasa.
        function avatarPicker() {
            return {
                preview: null, name: '', shot: null, blob: null, stream: null, streaming: false, status: '',
                facing: 'user', hasMultipleCameras: false,
                cameraSupported: !! (navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.isSecureContext),

                picked(file) {
                    this.name = file ? file.name : '';
                    this.preview = file ? URL.createObjectURL(file) : null;
                },

                async openCamera() {
                    this.shot = null;
                    this.$refs.dialog.showModal();
                    await this.startStream();
                },

                async startStream() {
                    this.stopStream();
                    this.status = 'Menyalakan kamera…';
                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: this.facing, width: { ideal: 1280 }, height: { ideal: 1280 } }, audio: false });
                        this.$refs.video.srcObject = this.stream;
                        await this.$refs.video.play();
                        this.streaming = true;
                        this.status = '';
                        const devices = await navigator.mediaDevices.enumerateDevices();
                        this.hasMultipleCameras = devices.filter((d) => d.kind === 'videoinput').length > 1;
                    } catch (e) {
                        this.streaming = false;
                        this.status = e.name === 'NotAllowedError'
                            ? 'Akses kamera ditolak. Izinkan kamera melalui pengaturan browser, atau gunakan “Unggah foto”.'
                            : (e.name === 'NotFoundError' ? 'Kamera tidak ditemukan di perangkat ini.' : 'Kamera tidak dapat dibuka. Pastikan kamera tidak sedang digunakan aplikasi lain.');
                    }
                },

                stopStream() {
                    this.stream?.getTracks().forEach((t) => t.stop());
                    this.stream = null;
                    this.streaming = false;
                },

                async switchCamera() {
                    this.facing = this.facing === 'user' ? 'environment' : 'user';
                    await this.startStream();
                },

                capture() {
                    const video = this.$refs.video;
                    const side = Math.min(video.videoWidth, video.videoHeight);
                    const canvas = document.createElement('canvas');
                    canvas.width = canvas.height = Math.min(800, side);
                    const ctx = canvas.getContext('2d');
                    // Kamera depan ditampilkan seperti cermin; simpan sesuai yang dilihat santri.
                    if (this.facing === 'user') { ctx.translate(canvas.width, 0); ctx.scale(-1, 1); }
                    ctx.drawImage(video, (video.videoWidth - side) / 2, (video.videoHeight - side) / 2, side, side, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob((blob) => {
                        this.blob = blob;
                        this.shot = URL.createObjectURL(blob);
                        this.stopStream();
                    }, 'image/jpeg', 0.88);
                },

                async retake() {
                    this.shot = null;
                    this.blob = null;
                    await this.startStream();
                },

                usePhoto() {
                    const file = new File([this.blob], 'foto-kamera.jpg', { type: 'image/jpeg' });
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    this.$refs.input.files = transfer.files;
                    this.picked(file);
                    this.closeCamera();
                },

                closeCamera() {
                    this.stopStream();
                    if (this.$refs.dialog.open) this.$refs.dialog.close();
                },
            };
        }
    </script>
</x-app-layout>
