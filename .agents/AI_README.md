# AI_README.md — Konteks Proyek untuk AI/Coding Agent

> Baca file ini SEBELUM mengerjakan prompt tahap apa pun di proyek ini.
> File ini merangkum seluruh keputusan desain yang sudah disepakati dengan pemilik proyek.
> Jangan mengubah keputusan yang tercatat di sini tanpa konfirmasi eksplisit dari pemilik proyek.

> **CATATAN RIWAYAT:** Proyek ini awalnya dirancang untuk Android native (Kotlin + Jetpack
> Compose + Room). Keputusan itu **SUDAH DIGANTI** menjadi **web app Laravel** yang responsif
> (mobile & desktop). Seluruh keputusan arsitektur/domain logic di bawah ini tetap berlaku,
> hanya diterjemahkan dari Kotlin ke PHP/Laravel. Jangan membangun apa pun dengan Kotlin/Android lagi.

---

## 1. Apa Aplikasi Ini

Aplikasi web **Sistem Pendukung Keputusan (SPK) Beyblade X** — bukan sekadar database.
Merekomendasikan kombinasi Blade + Ratchet + Bit terbaik berdasarkan tujuan build yang
dipilih user (Attack, Defense, Stamina, Balance, Counter, Custom Preference), menggunakan
metode **TOPSIS**. Aplikasi juga berfungsi sebagai database Beyblade pribadi yang bisa
dikelola mandiri oleh pemilik (CRUD sendiri, tidak bergantung selamanya pada scraping).

**Stack:** Laravel 13, Livewire 3 (server-driven, minim JS), Eloquent ORM, Blade + Tailwind
CSS untuk tampilan responsif (mobile & desktop dari satu codebase yang sama — bukan app
terpisah). Database: MySQL (default), tapi migration harus tetap kompatibel SQLite untuk
kemudahan development lokal.

---

## 2. Prinsip Arsitektur yang TIDAK BOLEH Dilanggar

1. **TOPSIS Engine harus berupa PHP class murni**, tidak boleh bergantung pada Eloquent
   Model atau Facade Laravel apa pun. Menerima array/DTO performance profile, bukan Model
   Blade/Ratchet/Bit secara langsung. Harus bisa diuji dengan PHPUnit tanpa perlu boot
   Eloquent/database.
2. **Jangan ganti metode TOPSIS dengan SAW/Weighted Product.** Wajib 8 tahap lengkap:
   matriks keputusan → normalisasi → pembobotan → solusi ideal positif/negatif → jarak →
   preferensi → ranking.
3. **Bobot (WeightProfile untuk TOPSIS, ContributionMatrix untuk Performance Engine) adalah
   DATA di database**, bukan konstanta di kode PHP. Harus bisa diubah dari UI tanpa deploy
   ulang/ubah kode.
4. **Data mentah (raw) di database TIDAK PERNAH dinormalisasi saat disimpan.** Normalisasi
   hanya terjadi di service layer saat komputasi.
5. **Management (CRUD Livewire) dan Recommendation Engine adalah namespace/concern
   terpisah.** Management tidak boleh memanggil TopsisEngine langsung. Recommendation
   Engine tidak boleh tahu soal validasi form/Livewire. Satu-satunya jembatan resmi:
   `CacheInvalidator` service, dipanggil oleh Management setelah create/update/delete data
   yang mempengaruhi hasil rekomendasi.
6. **Height Ratchet disimpan numerik murni** (contoh: "5-70" → `height = 70.00`), BUKAN
   kategori string. Kategori Low/Mid/High dihitung on-demand oleh service/helper terpisah,
   tidak disimpan di kolom database.
7. **Gambar disimpan sebagai file lewat Laravel Filesystem** (`storage/app/public/...`),
   database hanya menyimpan path relatif, bukan file binary atau URL eksternal.
8. **Official Beyblade menyimpan foreign key ke Blade/Ratchet/Bit**, bukan copy atributnya.

---

## 3. Keputusan Desain Kunci (jangan ditanya ulang, sudah final)

### Atribut Fisik vs Performa
- **Fisik** (weight, height, diameter): nilai asli (gram, mm), tidak dipaksa skala 0–100
  saat disimpan.
- **Performa** (attack, defense, stamina, speed, grip, control, stability, smash, upper,
  recoil, burstResistance): skala 0–100.
- Normalisasi atribut fisik (kalau ikut kriteria performa gabungan) pakai **Min-Max dengan
  batas teoretis** (theoreticalMin/Max berdasarkan spesifikasi desain game), BUKAN
  min-max dari dataset aktual — supaya skor lama tidak bergeser diam-diam saat ada part
  baru dengan nilai ekstrem masuk database.
- `weight` total kombinasi = penjumlahan langsung (blade + ratchet, dalam gram; Bit tidak
  punya atribut weight terpisah), bukan dinormalisasi ke 0–100 di Performance Engine.

### Contribution Matrix (Performance Engine)
Setiap kriteria performa gabungan dibentuk dari kontribusi berbobot 3 part, BUKAN rata-rata
sederhana. Disimpan di tabel `contribution_matrix`, bisa diubah tanpa ubah kode. Nilai
default sudah disepakati (lihat prompt Tahap 2) berdasarkan alasan mekanis tiap part —
jangan diubah tanpa alasan mekanis yang jelas.

### Cache
`combo_cache` menyimpan skor TOPSIS per (kombinasi × weight profile) untuk performa di
skala ribuan–puluhan ribu kombinasi. Invalidation wajib terjadi saat:
- Part diupdate/dihapus → invalidate cache yang mengandung part itu.
- ContributionMatrix diubah → invalidate SEMUA cache.
- WeightProfile diubah → invalidate cache untuk profile itu saja.

### Series & Product
Entity relasional sendiri (bukan string bebas di tabel Blade), supaya bisa di-CRUD mandiri
tanpa data yatim/typo. **Belum dibuat di Tahap 1** (vertical slice — lihat Bagian 5).

---

## 4. Struktur Folder Laravel yang Disepakati

```
app/
  Models/
    Blade.php, Ratchet.php, Bit.php, ...      -> Eloquent, hanya representasi data + relasi

  Repositories/
    Contracts/
      BladeRepositoryInterface.php
      RatchetRepositoryInterface.php
      BitRepositoryInterface.php
    Eloquent/
      BladeRepository.php
      RatchetRepository.php
      BitRepository.php

  Domain/
    Recommendation/
      Topsis/           -> TopsisEngine (pure PHP, 8 tahap eksplisit)
      Performance/       -> PerformanceEngine, ContributionRule (pure PHP)
      Normalization/     -> NormalizationService (pure PHP)
      Weight/            -> WeightProfile DTO
    Management/
      Validators/        -> aturan validasi per entity
      CacheInvalidator.php (interface + implementasi)

  Livewire/
    Recommendation/      -> BuildGoalSelector, PartPicker, RecommendationResult
    Management/          -> BladeManager, RatchetManager, BitManager, ImportExport

  Providers/
    RepositoryServiceProvider.php   -> bind interface -> implementasi

database/
  migrations/
  seeders/                -> seeder data awal (dari JSON scraping)

resources/
  views/livewire/         -> Blade view tiap Livewire component
  css/ (Tailwind)

storage/
  app/public/images/      -> gambar part & Beyblade yang diupload user
```

**Prinsip:** `Domain/Recommendation/*` tidak boleh punya `use` statement ke Eloquent Model
atau `Illuminate\*` Facade apa pun (kecuali interface kontrak murni jika benar-benar perlu).
Livewire component memanggil lewat Repository/UseCase, tidak pernah memanggil
`Domain/Recommendation/*` langsung dengan Model Eloquent sebagai parameter — selalu lewat
konversi ke array/DTO dulu.

---

## 5. Urutan Implementasi (Vertical Slice — Opsi C)

- [ ] **Tahap 1** — Migration + Model + Repository untuk Blade/Ratchet/Bit saja
      (Series/Product/Image/Beyblade SENGAJA belum ada). *(Prompt terlampir: `PROMPT_TAHAP_1.md`)*
- [ ] **Tahap 2** — Performance Engine + TOPSIS Engine (pure PHP, testable via PHPUnit
      tanpa database).
- [ ] **Tahap 3** — Alur end-to-end Livewire sederhana: pilih Blade→Ratchet→Bit → lihat
      skor rekomendasi (UI minimal, styling seadanya).
- [ ] **Tahap 4** — Management CRUD (Livewire component form tambah/edit/hapus + validasi).
- [ ] **Tahap 5** — Import/Export JSON + Image upload (Laravel Filesystem).
- [ ] **Tahap 6** — Series/Product entity, Deck Builder, AHP untuk Custom Preference, fitur
      lanjutan lain.

**Jangan lompat tahap.** Kalau prompt tahap tertentu tampak butuh sesuatu dari tahap yang
belum selesai, hentikan dan laporkan ke pemilik proyek — jangan berimprovisasi membuat
tabel/struktur baru di luar prompt yang diberikan.

---

## 6. Format Data Referensi (dari scraping asli pemilik proyek)

```json
// Blade
{ "id": 1, "name": "Wizard Rod", "series": "UX", "productCode": "UX-03",
  "weight": 35.2, "attack": 68, "defense": 82, "stamina": 98, "smash": 55,
  "upper": 40, "recoil": 25, "burstResistance": 86 }

// Ratchet
{ "id": 15, "name": "5-70", "height": 70, "weight": 7.1,
  "stability": 90, "burstResistance": 82 }

// Bit
{ "id": 9, "name": "Ball", "speed": 35, "stamina": 100, "grip": 40,
  "control": 90, "movement": 30, "dash": 15 }
```

Catatan: `id` di JSON scraping adalah ID sumber data lama — di database Laravel, primary key
mengikuti auto-increment Laravel standar (`id` bigint). ID dari JSON tidak wajib dipertahankan
sama persis, cukup `name` yang dijaga unik sebagai kunci pencocokan saat import.

---

## 7. Kalau Ragu

Kalau ada prompt/instruksi baru yang tampak bertentangan dengan Bagian 2 (prinsip
arsitektur), **tanyakan konfirmasi ke pemilik proyek dulu** sebelum mengimplementasikan.
Jangan langsung menimpa keputusan yang sudah disepakati di file ini.
