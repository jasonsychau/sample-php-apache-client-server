### To run

```
cd PROJECT_ROOT
docker-compose build && docker-compose up
```

Run schema build migration (in a new terminal)

```
docker exec -it php-apache bash
cd /var/www/html/JasonsProject
php artisan migrate:fresh --seed --seeder=DemoSeeder
````

Find website at http://localhost:8000.

### To stop and clean up

In a new terminal

```
docker stop php-apache mysql
docker rm php-apache mysql
```

### Features

- Auth0 authentication
- REST api
- database seeder
- Eloquent ORM Model class
- data querying and aggregating