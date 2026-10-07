#!/usr/bin/env bash
# Membangun proyek Android Capacitor untuk satu flavor: nasabah | kolektor | admin.
# Wajib: APP_DOMAIN (domain produksi HTTPS tanpa skema, mis. app.contoh.com).
# Opsional: VERSION_CODE (angka versionCode Android, mis. github.run_number),
#           ANDROID_KEYSTORE_BASE64 + ANDROID_KEYSTORE_PASSWORD + ANDROID_KEY_ALIAS + ANDROID_KEY_PASSWORD
#           untuk APK release bertanda tangan; tanpa secret dihasilkan APK debug.
set -euo pipefail

FLAVOR="${1:-}"
if [ -z "$FLAVOR" ]; then
    echo "Penggunaan: build-flavor.sh <nasabah|kolektor|admin>" >&2
    exit 1
fi

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
MOBILE="$ROOT/mobile"
BUILD="$MOBILE/build/$FLAVOR"

APP_DOMAIN="${APP_DOMAIN:-}"
if [ -z "$APP_DOMAIN" ]; then
    echo "APP_DOMAIN wajib diisi (mis. app.contoh.com)" >&2
    exit 1
fi

baca_flavor() {
    node -p "require('$MOBILE/flavors.json')['$FLAVOR'].$1" || {
        echo "Flavor tidak dikenal: $FLAVOR" >&2
        exit 1
    }
}

APPLICATION_ID="$(baca_flavor applicationId)"
APP_NAME="$(baca_flavor name)"
START_PATH="$(baca_flavor startPath)"
VERSION_NAME="${VERSION_NAME:-0.0.0}"
VERSION_CODE="${VERSION_CODE:-1}"

echo "==> Menyiapkan proyek flavor $FLAVOR ($APPLICATION_ID)"
rm -rf "$BUILD"
mkdir -p "$BUILD"
cp -R "$MOBILE/template/." "$BUILD/"
cp "$MOBILE/package.json" "$BUILD/package.json"

node -e "
const fs = require('fs');
const path = process.argv[1];
let cfg = fs.readFileSync(path, 'utf8');
for (const [kunci, nilai] of Object.entries({
    APPLICATION_ID: process.argv[2],
    APP_NAME: process.argv[3],
    APP_DOMAIN: process.argv[4],
    START_PATH: process.argv[5],
    FLAVOR: process.argv[6],
})) {
    cfg = cfg.split('{{' + kunci + '}}').join(nilai);
}
fs.writeFileSync(path, cfg);
" "$BUILD/capacitor.config.json" "$APPLICATION_ID" "$APP_NAME" "$APP_DOMAIN" "$START_PATH" "$FLAVOR"

echo "==> Memasang dependensi Node"
cd "$BUILD"
npm install --no-audit --no-fund

echo "==> Menambahkan proyek Android"
npx cap add android

MANIFEST="$BUILD/android/app/src/main/AndroidManifest.xml"
echo "==> Memasang izin Android (INTERNET, kamera, lokasi)"
for IZIN in \
    "android.permission.INTERNET" \
    "android.permission.CAMERA" \
    "android.permission.ACCESS_FINE_LOCATION" \
    "android.permission.ACCESS_COARSE_LOCATION"; do
    if ! grep -q "$IZIN" "$MANIFEST"; then
        sed -i "s#</manifest>#    <uses-permission android:name=\"$IZIN\" />\n</manifest>#" "$MANIFEST"
    fi
done

STRINGS="$BUILD/android/app/src/main/res/values/strings.xml"
echo "==> Mengatur nama aplikasi: $APP_NAME"
sed -i "s#<string name=\"capacitor_label\">[^<]*</string>#<string name=\"capacitor_label\">$APP_NAME</string>#" "$STRINGS"

echo "==> Menyiapkan ikon dan splash screen"
mkdir -p "$BUILD/assets"
cp "$MOBILE/assets/$FLAVOR/icon.png" "$BUILD/assets/icon.png"
cp "$MOBILE/assets/$FLAVOR/splash.png" "$BUILD/assets/splash.png"
npx capacitor-assets generate --android

GRADLE="$BUILD/android/app/build.gradle"
echo "==> Mengatur versionCode=$VERSION_CODE versionName=$VERSION_NAME"
sed -i "s/versionCode [0-9]*/versionCode $VERSION_CODE/" "$GRADLE"
sed -i "s/versionName \"[^\"]*\"/versionName \"$VERSION_NAME\"/" "$GRADLE"

cd "$BUILD/android"

APK_DIR="$BUILD/android/app/build/outputs/apk"
if [ -n "${ANDROID_KEYSTORE_BASE64:-}" ]; then
    echo "==> Membangun APK release bertanda tangan"
    echo "$ANDROID_KEYSTORE_BASE64" | base64 --decode > "$BUILD/upload-keystore.jks"
    ./gradlew assembleRelease
    UNSIGNED="$(find "$APK_DIR/release" -name '*-unsigned.apk' | head -n1)"
    APKSIGNER="$(find "$ANDROID_HOME/build-tools" -name apksigner | sort -V | tail -n1)"
    SIGNED="$BUILD/tabungan-$FLAVOR-$VERSION_NAME.apk"
    "$APKSIGNER" sign \
        --ks "$BUILD/upload-keystore.jks" \
        --ks-pass "pass:$ANDROID_KEYSTORE_PASSWORD" \
        --ks-key-alias "$ANDROID_KEY_ALIAS" \
        --key-pass "pass:$ANDROID_KEYSTORE_PASSWORD" \
        --out "$SIGNED" \
        "$UNSIGNED"
    rm -f "$BUILD/upload-keystore.jks"
    echo "==> APK: $SIGNED"
else
    echo "==> Secret keystore tidak ada - membangun APK debug untuk uji"
    ./gradlew assembleDebug
    DEBUG="$(find "$APK_DIR/debug" -name '*.apk' | head -n1)"
    SIGNED="$BUILD/tabungan-$FLAVOR-$VERSION_NAME-debug.apk"
    cp "$DEBUG" "$SIGNED"
    echo "==> APK: $SIGNED"
fi

echo "==> Selesai: $SIGNED"
