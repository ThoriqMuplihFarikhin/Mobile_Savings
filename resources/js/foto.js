/**
 * Kompresi foto bukti serah terima di klien (9.5/P6.5).
 * Lebar maksimum 1600 px dan maksimum 4 MB; berkas hasil kompresi
 * diunggah lewat $wire.upload agar berkas asli yang besar tidak
 * pernah dikirim ke server.
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('kompresFoto', () => ({
        sedangKompres: false,

        async proses(input) {
            const berkas = input.files && input.files[0];

            if (! berkas || ! berkas.type.startsWith('image/')) {
                return;
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
    }));
});
