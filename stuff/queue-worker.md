# Queue, Tasks

# Cron job dla job seduler
crontab -e
```sh
# Without logs
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

# With log file
* * * * * cd /path-to-your-project && php artisan schedule:run >> /path-to-your-project/storage/logs/cron.log
```

# Instalacja queue workera
```sh
sudo apt-get install supervisor
```

### Queue worker logs
```sh
tail -f /var/log/supervisor/supervisord.log
```

### Konfiguracja workera (zmień: [username])
/etc/supervisor/conf.d/laravel.worker.conf
```sh
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
# Aws
# command=php /home/[username]/www/app.xx/artisan queue:work sqs --sleep=5 --tries=3 --max-time=3600 --backoff=10
# Database
command=php /home/[username]/www/app.xx/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --backoff=10
stdout_logfile=/home/[username]/www/app.xx/storage/logs/queue.log
user=[username]
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
redirect_stderr=true
stopwaitsecs=3600

```

### Uruchom queue:worker
```sh
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*

sudo service supervisor stop
sudo service supervisor start
```
