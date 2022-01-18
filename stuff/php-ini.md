### Php.ini konfiguracja
/etc/php/8.1/fpm/php.ini
```sh
allow_url_fopen=off
allow_url_include=off

memory_limit = 500M

post_max_size=100M
upload_max_filesize = 10M
max_file_uploads = 10

session.use_strict_mode=On
session.use_only_cookies=On 
session.cookie_httponly=On
session.cookie_samesite="Strict"
# https
session.cookie_secure=On

max_execution_time = 600 
max_input_vars = 300 
max_input_time = 1000
```
