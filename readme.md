## TEST PADELMANAGER - CRISTIAN MAXIMILIANO SAVINO

# Comandos para correr el Docker
* 1) docker-compose up -d --build database && docker-compose up -d --build app && docker-compose up -d --build web

# Comandos a correr con el Docker UP

* en otro terminal
* 2) docker exec -it laravel_app bash
* -> a) php artisan migrate
* -> b) redis-server

* en otro terminal
* 3) docker exec -it laravel_app bash
* -> a) php artisan queue:work