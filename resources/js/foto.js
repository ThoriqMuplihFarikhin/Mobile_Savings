/**
 * Kompresi foto dan penanganan kamera bukti serah terima di klien (9.5/P6.5).
 * Mendukung 2 metode:
 * 1. Kamera langsung di aplikasi dengan live viewfinder + jepret langsung di tempat.
 * 2. Unggah foto dari galeri HP (berkas luar aplikasi).
 * Berkas dikompresi di sisi klien dan diunggah via $wire.upload.
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('kompresFoto', () => ({
        tipeFoto: 'kamera', // 'kamera' | 'galeri'
        sedangKompres: false,
        pratinjauUrl: null,
        kameraAktif: false,
        streamKamera: null,
        errorKamera: null,
        facingMode: 'environment', // Kamera belakang untuk foto serah terima

        init() {
            // Komponen kompresi foto & penanganan kamera
        },

        setTipe(tipe) {
            this.tipeFoto = tipe;
            this.errorKamera = null;
            if (tipe !== 'kamera') {
                this.tutupKamera();
            }
        },

        async bukaKamera() {
            this.errorKamera = null;
            try {
                if (this.streamKamera) {
                    this.streamKamera.getTracks().forEach((t) => t.stop());
                    this.streamKamera = null;
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.errorKamera = 'Perangkat atau browser tidak mendukung akses kamera langsung. Silakan gunakan tombol kamera HP di bawah.';
                    this.kameraAktif = false;
                    return;
                }

                const constraints = {
                    video: {
                        facingMode: { ideal: this.facingMode },
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                    },
                    audio: false,
                };

                this.streamKamera = await navigator.mediaDevices.getUserMedia(constraints);
                this.kameraAktif = true;

                this.$nextTick(() => {
                    const video = this.$refs.videoKamera;
                    if (video) {
                        video.srcObject = this.streamKamera;
                        video.play().catch(() => {});
                    }
                });
            } catch (e) {
                this.kameraAktif = false;
                this.errorKamera = 'Izin kamera belum aktif. Berikan izin di browser atau gunakan tombol kamera HP.';
            }
        },

        tutupKamera() {
            if (this.streamKamera) {
                this.streamKamera.getTracks().forEach((t) => t.stop());
                this.streamKamera = null;
            }
            this.kameraAktif = false;
        },

        async gantiKamera() {
            this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
            await this.bukaKamera();
        },

        async ambilFotoDariKamera() {
            const video = this.$refs.videoKamera;
            if (!video) return;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 720;
            const ctx = canvas.getContext('2d');

            if (this.facingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const blob = await new Promise((selesai) => canvas.toBlob(selesai, 'image/jpeg', 0.85));
            if (!blob) return;

            const nama = 'bukti-serah-terima-' + Date.now() + '.jpg';
            const berkas = new File([blob], nama, {
                type: 'image/jpeg',
                lastModified: Date.now(),
            });

            this.tutupKamera();

            if (this.pratinjauUrl) {
                URL.revokeObjectURL(this.pratinjauUrl);
            }
            this.pratinjauUrl = URL.createObjectURL(blob);

            this.sedangKompres = true;
            await this.$wire.upload(
                'buktiFoto',
                berkas,
                () => {},
                () => {}
            );
            this.sedangKompres = false;
        },

        async proses(input) {
            const berkas = input.files && input.files[0];

            if (! berkas || ! berkas.type.startsWith('image/')) {
                return;
            }

            this.tutupKamera();

            try {
                if (this.pratinjauUrl) {
                    URL.revokeObjectURL(this.pratinjauUrl);
                }
                this.pratinjauUrl = URL.createObjectURL(berkas);
            } catch (kesalahanPratinjau) {
                // Lewati jika createObjectURL tidak didukung
            }

            let hasil = berkas;
            const batasSisi = 1600;
            const batasUkuran = 4 * 1024 * 1024;

            this.sedangKompres = true;

            try {
                const bitmap = await createImageBitmap(berkas);
                const sisiMaks = Math.max(bitmap.width, bitmap.height);

                if (sisiMaks > batasSisi || berkas.size > batasUkuran) {
                    const skala = Math.min(1, batasSisi / sisiMaks);
                    const kanvas = document.createElement('canvas');
                    kanvas.width = Math.max(1, Math.round(bitmap.width * skala));
                    kanvas.height = Math.max(1, Math.round(bitmap.height * skala));
                    kanvas.getContext('2d').drawImage(bitmap, 0, 0, kanvas.width, kanvas.height);

                    const blob = await new Promise((selesai) => kanvas.toBlob(selesai, 'image/jpeg', 0.85));

                    if (blob) {
                        const nama = berkas.name.replace(/\.[^.]+$/, '') + '.jpg';
                        hasil = new File([blob], nama, { type: 'image/jpeg', lastModified: berkas.lastModified });
                        try {
                            if (this.pratinjauUrl) {
                                URL.revokeObjectURL(this.pratinjauUrl);
                            }
                            this.pratinjauUrl = URL.createObjectURL(blob);
                        } catch (kesalahanBlob) {
                            // Abaikan kesalahan penataan pratinjau blob
                        }
                    }
                }

                if (typeof bitmap.close === 'function') {
                    bitmap.close();
                }
            } catch (kesalahan) {
                // Browser tanpa dukungan canvas/bitmap: kirim berkas apa adanya.
            }

            this.sedangKompres = false;

            await this.$wire.upload(
                'buktiFoto',
                hasil,
                () => {},
                () => {
                    input.value = '';
                }
            );
        },

        hapusFoto() {
            if (this.pratinjauUrl) {
                URL.revokeObjectURL(this.pratinjauUrl);
                this.pratinjauUrl = null;
            }
            this.tutupKamera();
            this.$wire.set('buktiFoto', null);
        },
    }));
});
