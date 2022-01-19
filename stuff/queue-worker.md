# Queue, Tasks

# Harmonogram zadań (schelude:work)
crontab -e
```sh
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

# Queue instalacja
```sh
sudo apt-get install supervisor
```

### Konfiguracja workera (zmień: [username])
/etc/supervisor/conf.d/laravel.worker.conf
```sh
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/[username]/www/app.xx/artisan queue:work sqs --sleep=5 --tries=3 --max-time=3600 --backoff=10
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=[username]
numprocs=2
redirect_stderr=true
stdout_logfile=/home/[username]/.cache/app-xx-worker.log
stopwaitsecs=3600
```

### Uruchom queue:worker
```sh
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```
