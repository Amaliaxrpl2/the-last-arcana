THE LAST ARCANA — STARTER PROJECT

Isi folder:
- index.php       Halaman utama dan logika sederhana PHP
- style.css       Tampilan responsif untuk HP dan komputer
- data/cards.json Data kartu tarot

PENTING:
1. GitHub Pages tidak menjalankan PHP. Upload kode ke GitHub untuk menyimpannya, tetapi untuk menjalankan index.php kamu membutuhkan server/hosting yang mendukung PHP.
2. Proyek ini tidak memakai MySQL. Data kartu ada di file JSON. Status pemain (level, EXP, HP, dan stage) sementara disimpan menggunakan PHP session. Koleksi Arcana dan jumlah kartu duplikat disimpan terpisah dalam JSON di `data/.arcana/`, lalu dikaitkan dengan cookie browser agar tetap ada setelah halaman dimuat ulang.
3. Jangan gunakan tombol Stage sebagai sistem pertarungan final; ini masih prototipe awal untuk menguji level, EXP, HP, dan navigasi.

CARA MENAMBAH KARTU:
Buka data/cards.json lalu tambahkan objek kartu dengan properti id, number, name, rarity, symbol, dan description. Pastikan format JSON tetap valid.

LANGKAH BERIKUTNYA:
- Tambahkan gambar kartu buatan sendiri
- Tambahkan sistem inventory yang lebih rapi
- Buat cerita Stage 1 dan pilihan yang berpengaruh
- Simpan progres dengan penyimpanan yang sesuai jika sudah memakai hosting
