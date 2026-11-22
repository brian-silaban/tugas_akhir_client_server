# Panduan Integrasi REST API JejakSumut (Untuk 4 Kelompok Mitra)

Dokumen ini menjadi panduan pertukaran data **JSON REST API** antara platform wisata Sumatera Utara **JejakSumut** dengan 4 kelompok mitra di kelas:
1. **Penyedia Tur Guide**
2. **Kuliner / Rumah Makan**
3. **Penginapan**
4. **Transportasi**

---

## 1. Alamat Layanan Aktif (VM Azure)

| Layanan | URL Aktif | Keterangan |
| :--- | :--- | :--- |
| **REST API Backend (JSON)** | `http://70.153.136.38:8080/api/v1` | Endpoint utama untuk diakses aplikasi client/mitra (`971` objek wisata di `33` kab/kota Sumut). |
| **Status Server API** | `http://70.153.136.38:8080/` | Menampilkan status koneksi database `jejaksumut_db` dan daftar rute. |
| **Aplikasi Web Frontend** | `http://70.153.136.38:8082/` | Antarmuka pengguna JejakSumut (tujuan tautan `url_detail`). |

---

## 2. Daftar Endpoint Publik JejakSumut untuk Kelompok Mitra

Seluruh endpoint di bawah ini dapat dipanggil langsung dengan metode **`GET`** tanpa token autentikasi:

| Endpoint | Parameter Query Opsional | Kegunaan |
| :--- | :--- | :--- |
| `GET /api/v1/destinasi` | `kota`, `kategori`, `kode_kabupaten`, `harga_maksimal`, `kata_kunci`, `urutan`, `halaman`, `batas` (maks `50`) | Mengambil daftar destinasi wisata Sumut beserta koordinat GPS dan harga tiket. |
| `GET /api/v1/destinasi/{slug_atau_id}` | — | Mengambil detail lengkap 1 destinasi (fasilitas, galeri, pengelola, kontak, ulasan). |
| `GET /api/v1/kategori` | — | Daftar 6 kategori wisata Sumut beserta jumlah objek wisatanya. |
| `GET /api/v1/kabupaten-kota` | — | Daftar 33 kabupaten/kota Sumatera Utara lengkap dengan `kode_kabupaten` dan jumlah destinasi. |

### Contoh Perintah Uji `curl`
```bash
# 1. Mengambil 5 destinasi wisata di sekitar Samosir
curl "http://70.153.136.38:8080/api/v1/destinasi?kota=Samosir&batas=5"

# 2. Mengambil detail lengkap Danau Toba
curl "http://70.153.136.38:8080/api/v1/destinasi/danau-toba"

# 3. Mengambil daftar 33 kabupaten/kota di Sumatera Utara
curl "http://70.153.136.38:8080/api/v1/kabupaten-kota"
```

### Contoh Respons JSON Aktual (`GET /api/v1/destinasi?kota=Samosir&batas=1`)
```json
{
  "status": "berhasil",
  "penyedia": "JejakSumut Tourism API",
  "jumlah_data": 1,
  "paginasi": {
    "halaman_sekarang": 1,
    "batas_per_halaman": 1,
    "total_data": 69,
    "total_halaman": 69
  },
  "data": [
    {
      "id": 667,
      "nama": "Danau Toba",
      "slug": "danau-toba",
      "kategori": "Wisata Alam",
      "slug_kategori": "wisata-alam",
      "sub_kategori": "Danau",
      "kode_kabupaten": "1217",
      "nama_kabupaten_kota": "Kabupaten Samosir",
      "kota": "Kabupaten Samosir",
      "provinsi": "Sumatera Utara",
      "koordinat": {
        "garis_lintang": 2.68,
        "garis_bujur": 98.85
      },
      "harga_tiket": 0,
      "label_harga_tiket": "Gratis (Rp 0)",
      "jam_operasional": "24 Jam (00:00 - 23:59)",
      "rata_rating": 0,
      "jumlah_ulasan": 0,
      "foto_utama": "...",
      "url_detail": "http://70.153.136.38:8082/destinasi/danau-toba"
    }
  ]
}
```

---

## 3. Format API yang Dikonsumsi JejakSumut dari Kelompok Mitra

Pada halaman Detail Destinasi, aplikasi Frontend JejakSumut (`LayananApiMitra.php`) akan memanggil endpoint masing-masing kelompok mitra dengan parameter:

```text
GET <URL_API_MITRA>?kota=<nama_kota>
```
Contoh panggilan dari JejakSumut:
- `GET http://<host-mitra>/api/tur_guide?kota=Samosir`
- `GET http://<host-mitra>/api/kuliner?kota=Medan`
- `GET http://<host-mitra>/api/penginapan?kota=Karo`
- `GET http://<host-mitra>/api/transportasi?kota=Langkat`

*(Catatan: JejakSumut otomatis memangkas kata `"Kabupaten "` atau `"Kota "` sebelum mengirim query `?kota=` sehingga `"Kabupaten Samosir"` dikirim sebagai `kota=Samosir`).*

### Format Respons JSON Ideal dari Mitra
```json
{
  "status": "berhasil",
  "data": [
    {
      "nama": "Pemandu Kaldera Samosir & Bukit Holbung",
      "kategori": "Tur Alam & Budaya Batak",
      "kota": "Samosir",
      "alamat": "Jl. Lingkar Tuk-tuk Siadong, Simanindo",
      "harga": 275000,
      "satuan_harga": "/ paket",
      "rating": 4.9,
      "jumlah_ulasan": 148,
      "kontak": "0812-6500-4120",
      "deskripsi": "Pendampingan rute Bukit Holbung hingga Desa Adat Tomok.",
      "foto": "https://contoh.id/gambar.jpg",
      "url_detail": "http://localhost:8085/paket/1"
    }
  ]
}
```
*(Batas waktu permintaan ke API mitra diset `3 detik`. Adapter kami juga mendukung variasi key pembungkus seperti `items`, `results`, `hasil`, atau array langsung, serta variasi nama kolom seperti `tarif`/`harga_mulai` atau `nama_paket`/`nama_hotel`.)*

---

## 4. Cara Pakai Koleksi Postman

1. Buka aplikasi **Postman**.
2. Klik **Import**, lalu pilih 3 file berikut dari folder [`postman/`](.):
   - `JejakSumut_API.postman_collection.json`
   - `JejakSumut_VM_Azure.postman_environment.json` *(untuk mengetes langsung server VM Azure)*
   - `JejakSumut_Lokal.postman_environment.json` *(untuk mengetes server di laptop lokal)*
3. Di pojok kanan atas Postman, pilih Environment **`JejakSumut — VM Azure (Online)`** atau **`JejakSumut — Lokal Development`**.
4. Jalankan folder **`01`** dan **`02`** untuk menguji endpoint publik, serta folder **`03`** dan **`04`** untuk menguji transaksi pemesanan tiket dan ulasan.
