# run migration
# php artisan migrate

# seed database
# php artisan db:seed --class=DemoSeeder

php artisan migrate:fresh --seed --seeder=DemoSeeder
