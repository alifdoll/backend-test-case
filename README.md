## Langkah Instalasi

1. Clone Project dengan command "git clone".
2. Jalankan command composer install, apabila error, update composer terlebih dahulu dengan command composer update.
3. Tulis command "cp .env.example .env".
4. Lalu tulis lagi command "php artisan key:generate".
5. Masuk kedalam file .env, lalu ubah nama database menjadi product_db.
6. Buat database pada phpmyadmin dengan nama product_db.
7. Jalankan command php artisan migrate:fresh.
8. Lalu jalankan command php artisan db:seed untuk mengisi table pada database.
