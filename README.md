git clone https://github.com/Konst-Dz/journal

cd journal

docker compose up -d --build

cp .env.example .env

Отредактировать .env для MySQL

DB_CONNECTION=mysql

DB_HOST=mysql

DB_PORT=3306

DB_DATABASE=laravel_db

DB_USERNAME=laravel

DB_PASSWORD=password

docker compose run --rm composer install

docker compose run --rm artisan key:generate

docker compose run --rm artisan migrate --force

docker compose run --rm artisan db:seed -force

http://localhost:8080
