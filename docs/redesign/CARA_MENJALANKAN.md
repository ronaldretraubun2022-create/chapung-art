# CARA MENJALANKAN PHASE 1 SAMPAI PHASE 14

## 1. Buka Project

```bash
cd /c/laragon/www/chapung-art
```

## 2. Periksa Git

```bash
git status
git log -5 --oneline
```

Pastikan perubahan lama sudah dipahami sebelum memulai.

## 3. Buat atau Gunakan Branch Redesign

```bash
git checkout -b feat/art-marketplace-redesign
```

Jika branch sudah ada:

```bash
git checkout feat/art-marketplace-redesign
```

## 4. Jalankan Aplikasi Lokal

Terminal pertama:

```bash
php artisan serve
```

Terminal kedua:

```bash
npm run dev
```

Buka:

```text
http://127.0.0.1:8000
```

## 5. Eksekusi Setiap Phase

Urutan file:

```text
PHASE_01_CODEX_PROMPT.txt
PHASE_02_CODEX_PROMPT.txt
PHASE_03_CODEX_PROMPT.txt
PHASE_04_CODEX_PROMPT.txt
PHASE_05_CODEX_PROMPT.txt
PHASE_06_CODEX_PROMPT.txt
PHASE_07_CODEX_PROMPT.txt
PHASE_08_CODEX_PROMPT.txt
PHASE_09_CODEX_PROMPT.txt
PHASE_10_CODEX_PROMPT.txt
PHASE_11_CODEX_PROMPT.txt
PHASE_12_CODEX_PROMPT.txt
PHASE_13_CODEX_PROMPT.txt
PHASE_14_CODEX_PROMPT.txt
```

Untuk setiap phase:

1. Salin isi prompt phase.
2. Tempel ke Codex.
3. Biarkan Codex menyelesaikan hanya phase tersebut.
4. Periksa hasil sebelum lanjut.

## 6. Pemeriksaan Setelah Setiap Phase

```bash
git status
git diff --stat
git diff
php artisan test
npm run build
```

Gunakan browser untuk menguji halaman terkait phase.

## 7. Commit Manual Setelah Phase Lulus

Codex tidak boleh commit otomatis. Setelah Anda yakin:

```bash
git add .
git commit -m "feat: complete Chapung Art redesign phase XX"
```

Ganti `XX` dengan nomor phase.

Push hanya saat siap:

```bash
git push origin feat/art-marketplace-redesign
```

## 8. Aturan Hemat Token Codex

- Jalankan satu prompt per phase.
- Jangan meminta Codex mengulang audit yang sudah ada.
- Berikan hasil error terminal secara langsung bila ada.
- Jangan menggabungkan dua phase.
- Periksa `git diff` agar Codex tidak mengubah file di luar scope.

## 9. Larangan

- Jangan mengubah `.env`.
- Jangan membagikan credential.
- Jangan menginstal produk berbayar.
- Jangan menghapus fitur transaksi.
- Jangan membuat dashboard perupa.
- Jangan menambah fitur kurator.
- Jangan deploy sebelum PHASE 14 selesai.
