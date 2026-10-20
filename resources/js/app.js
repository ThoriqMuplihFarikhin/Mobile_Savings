import './admin-ui';
import './foto';
import './tanggal';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import SignaturePad from 'signature_pad';

window.L = L;
window.SignaturePad = SignaturePad;

// Alpine store & inisialisasi ukuran teks besar A+ (D37)
document.addEventListener('alpine:init', () => {
    let initialBig = false;
    try {
        initialBig = localStorage.getItem('td-text-size') === 'big';
        if (initialBig) {
            document.documentElement.classList.add('big');
        }
    } catch (e) {}

    window.Alpine.store('textSize', {
        isBig: initialBig,
        toggle() {
            this.isBig = !this.isBig;
            try {
                if (this.isBig) {
                    document.documentElement.classList.add('big');
                    localStorage.setItem('td-text-size', 'big');
                } else {
                    document.documentElement.classList.remove('big');
                    localStorage.setItem('td-text-size', '');
                }
            } catch (e) {}
        }
    });
});

window.toggleUkuranTeks = function () {
    try {
        const isBig = document.documentElement.classList.toggle('big');
        localStorage.setItem('td-text-size', isBig ? 'big' : '');
        if (window.Alpine && window.Alpine.store('textSize')) {
            window.Alpine.store('textSize').isBig = isBig;
        }
    } catch (e) {}
};

