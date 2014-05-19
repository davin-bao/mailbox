D:
cd D:\redis\64bit
redis-server.exe redis.conf

D:
cd D:\github\laravel
php artisan queue:listen --queue=high --sleep=0 --timeout=0