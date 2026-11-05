/**
 * Picker tanggal flatpickr (D22/P4).
 * Nilai ke server tetap Y-m-d / Y-m (rentang: "Y-m-d s/d Y-m-d").
 * Dua arah: $set (client -> server) dan $watch (server -> client).
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('pickerTanggal', (konfig) => ({
        fp: null,
        flatpickr: null,
        tombolSiap: false,
        legendaSiap: false,

        async mulai() {
            const [{ default: flatpickr }, { default: bahasa }] = await Promise.all([
                import('flatpickr'),
                import('flatpickr/dist/l10n/id.js'),
            ]);

            this.flatpickr = flatpickr;

            let nilaiAwal = (this.$el.value || '').trim();

            if (! nilaiAwal && konfig.model) {
                nilaiAwal = this.$wire[konfig.model] || '';
            }
            const altFormat = konfig.mode === 'bulan' ? 'F Y' : 'j F Y';
            const altInputClasses = [
                this.$el.className || '',
                'flatpickr-alt-input',
                'cursor-pointer',
            ].filter(Boolean).join(' ');

            this.fp = flatpickr(this.$el, {
                locale: Object.assign({}, bahasa, { rangeSeparator: ' s/d ' }),
                disableMobile: true,
                allowInput: true,
                altInput: true,
                altInputClass: altInputClasses,
                altFormat: altFormat,
                dateFormat: this.formatSimpanBawaan(),
                mode: konfig.mode === 'rentang' ? 'range' : 'single',
                minDate: konfig.min || undefined,
                maxDate: konfig.max || undefined,
                onChange: (tanggal) => this.kirim(tanggal),
                onReady: () => {
                    this.pasangLegenda();
                    this.pasangTombol();
                },
                onOpen: () => {
                    this.pasangLegenda();
                    this.pasangTombol();
                },
                onDayCreate: (dObj, dStr, fp, dayElem) => {
                    this.pasangTandaHari(dayElem);
                },
            });

            if (nilaiAwal) {
                this.terima(nilaiAwal);
            }

            this.tema();

            if (konfig.model) {
                if (konfig.live) {
                    this.$wire.$set(konfig.model, this.nilaiDariElemen(), true);
                }

                this.$wire.$watch(konfig.model, (nilai) => {
                    const sekarang = this.nilaiDariElemen();

                    if ((nilai || '') !== (sekarang || '')) {
                        this.terima(nilai || '');
                    }
                });
            }

            if (konfig.tandaModel) {
                this.$wire.$watch(konfig.tandaModel, (nilaiBaru) => {
                    konfig.tanda = nilaiBaru || {};
                    if (this.fp) {
                        this.fp.redraw();
                    }
                });
            }

            this.$watch(() => this.$store.ui.isDark, () => this.tema());
        },

        formatSimpanBawaan() {
            return konfig.mode === 'bulan' ? 'Y-m' : 'Y-m-d';
        },

        nilaiDariElemen() {
            if (! this.fp) {
                return this.$el.value || '';
            }

            const tanggal = this.fp.selectedDates;

            return this.formatSimpan(tanggal);
        },

        formatSimpan(tanggal) {
            if (! tanggal || tanggal.length === 0) {
                return '';
            }

            if (konfig.mode === 'rentang') {
                if (tanggal.length < 2) {
                    return '';
                }

                return tanggal
                    .slice(0, 2)
                    .map((d) => this.flatpickr.formatDate(d, 'Y-m-d'))
                    .join(' s/d ');
            }

            if (konfig.mode === 'bulan') {
                return this.flatpickr.formatDate(tanggal[0], 'Y-m');
            }

            return this.flatpickr.formatDate(tanggal[0], 'Y-m-d');
        },

        kirim(tanggal) {
            if (! konfig.model) {
                return;
            }

            this.$wire.$set(konfig.model, this.formatSimpan(tanggal), konfig.live);
        },

        terima(nilai) {
            if (! this.fp) {
                return;
            }

            if (! nilai) {
                this.fp.clear(false);

                return;
            }

            if (konfig.mode === 'rentang') {
                const bagian = String(nilai).split(' s/d ');

                if (bagian.length === 2) {
                    this.fp.setDate(bagian, false, 'Y-m-d');
                }

                return;
            }

            this.fp.setDate(nilai, false, this.formatSimpanBawaan());
        },

        pasangTombol() {
            if (this.tombolSiap || ! this.fp.calendarContainer) {
                return;
            }

            this.tombolSiap = true;

            const baris = document.createElement('div');
            baris.className = 'flatpickr-tombol-baris';

            const hariIni = document.createElement('button');
            hariIni.type = 'button';
            hariIni.className = 'flatpickr-tombol';
            hariIni.textContent = 'Hari ini';
            hariIni.addEventListener('click', () => this.fp.setDate(new Date(), true));

            const hapus = document.createElement('button');
            hapus.type = 'button';
            hapus.className = 'flatpickr-tombol flatpickr-tombol-hapus';
            hapus.textContent = 'Hapus';
            hapus.addEventListener('click', () => this.fp.clear(true));

            baris.append(hariIni, hapus);
            this.fp.calendarContainer.appendChild(baris);
        },

        pasangTandaHari(dayElem) {
            if (! konfig.tanda || ! dayElem || ! dayElem.dateObj) {
                return;
            }

            const ymd = this.flatpickr.formatDate(dayElem.dateObj, 'Y-m-d');
            const info = konfig.tanda[ymd];

            if (! info) {
                return;
            }

            const adaAbsen = Boolean(
                (typeof info.absen === 'number' && info.absen > 0) ||
                info.absen === true ||
                info === 'absen' ||
                (Array.isArray(info) && info.includes('absen'))
            );

            const adaIzin = Boolean(
                (typeof info.izin === 'number' && info.izin > 0) ||
                info.izin === true ||
                info === 'izin' ||
                (Array.isArray(info) && info.includes('izin'))
            );

            if (! adaAbsen && ! adaIzin) {
                return;
            }

            dayElem.classList.add('has-tanda');

            const wrap = document.createElement('span');
            wrap.className = 'flatpickr-day-dots';

            if (adaAbsen) {
                const dot = document.createElement('span');
                dot.className = 'flatpickr-day-dot dot-absen';
                wrap.appendChild(dot);
            }

            if (adaIzin) {
                const dot = document.createElement('span');
                dot.className = 'flatpickr-day-dot dot-izin';
                wrap.appendChild(dot);
            }

            dayElem.appendChild(wrap);

            const ringkasan = [];
            if (adaAbsen) {
                const jml = typeof info.absen === 'number' && info.absen > 0 ? info.absen + ' ' : '';
                ringkasan.push(jml + 'kolektor absen/hadir');
            }
            if (adaIzin) {
                const jml = typeof info.izin === 'number' && info.izin > 0 ? info.izin + ' ' : '';
                ringkasan.push(jml + 'kolektor izin/cuti');
            }
            if (ringkasan.length > 0) {
                dayElem.setAttribute('title', ymd + ': ' + ringkasan.join(', '));
            }
        },

        pasangLegenda() {
            if (! konfig.tanda || this.legendaSiap || ! this.fp || ! this.fp.calendarContainer) {
                return;
            }

            this.legendaSiap = true;

            const baris = document.createElement('div');
            baris.className = 'flatpickr-legenda-baris';

            const itemAbsen = document.createElement('span');
            itemAbsen.className = 'flatpickr-legenda-item';
            itemAbsen.innerHTML = '<span class="flatpickr-day-dot dot-absen"></span> Hadir / Absen';

            const itemIzin = document.createElement('span');
            itemIzin.className = 'flatpickr-legenda-item';
            itemIzin.innerHTML = '<span class="flatpickr-day-dot dot-izin"></span> Izin / Cuti';

            baris.append(itemAbsen, itemIzin);
            this.fp.calendarContainer.appendChild(baris);
        },

        tema() {
            if (this.fp && this.fp.calendarContainer) {
                this.fp.calendarContainer.classList.toggle('dark', !! this.$store.ui.isDark);
            }
        },
    }));
});
