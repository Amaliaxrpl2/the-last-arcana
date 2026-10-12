THE LAST ARCANA — STARTER PROJECT

Isi folder:
- index.php       Halaman utama dan logika sederhana PHP
- style.css       Tampilan responsif untuk HP dan komputer
- data/cards.json Data kartu tarot

PENTING:
1. GitHub Pages tidak menjalankan PHP. Upload kode ke GitHub untuk menyimpannya, tetapi untuk menjalankan index.php kamu membutuhkan server/hosting yang mendukung PHP.
2. Proyek ini tidak memakai MySQL. Data kartu ada di file JSON. Progres pemain sementara disimpan menggunakan PHP session, sehingga progres dapat hilang saat session berakhir.
3. Jangan gunakan tombol Stage sebagai sistem pertarungan final; ini masih prototipe awal untuk menguji level, EXP, HP, dan navigasi.

CARA MENAMBAH KARTU:
Buka data/cards.json lalu tambahkan objek kartu dengan properti id, number, name, rarity, symbol, dan description. Pastikan format JSON tetap valid.

LANGKAH BERIKUTNYA:
- Tambahkan gambar kartu buatan sendiri
- Tambahkan sistem inventory yang lebih rapi
- Buat cerita Stage 1 dan pilihan yang berpengaruh
- Simpan progres dengan penyimpanan yang sesuai jika sudah memakai hosting
