# Konfiguracja Laravel 9, Vue 3
Instalacja serwerów aplikacji: Debian 11, Php 8.0/8.1, MariaDB 10.5.12-MariaDB, Postfix

## Pobierz
```sh
mkdir -p /home/max/www
cd /home/max/www
# Clone (sample repo)
git clone git@github.com:breakermind/vueauth.git app.xx
# Custom keys (sample repo)
git clone git@breakermind-github.com:breakermind/vueauth.git app.xx
```

## Apt https
```sh
sudo apt install -y apt-transport-https
sed -i 's/http\:/https\:/g' /etc/apt/sources.list
sudo apt update
sudo apt install net-tools mailutils dnsutils ufw nginx mariadb-server php-fpm postfix
```

## Baza danych i użytkownik
mysql -u root -p
```sql
CREATE DATABASE IF NOT EXISTS app_xx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS app_xx_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON app_xx.* TO app_xx@localhost IDENTIFIED BY 'toor' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON app_xx.* TO app_xx@127.0.0.1 IDENTIFIED BY 'toor' WITH GRANT OPTION;

GRANT ALL PRIVILEGES ON app_xx_testing.* TO app_xx@localhost IDENTIFIED BY 'toor' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON app_xx_testing.* TO app_xx@127.0.0.1 IDENTIFIED BY 'toor' WITH GRANT OPTION;

FLUSH PRIVILEGES;
```

## Katalog aplikacji (app.xx)
```sh
sudo mkdir -p /home/max/www/app.xx
# app
sudo chown -R max:www-data /home/max/www/app.xx
sudo chmod -R 2775 /home/max/www/app.xx
# app storage
sudo chown -R www-data:max /home/max/www/app.xx/storage
sudo chmod -R 775 /home/max/www/app.xx/storage
```

## Konfiguracja aplikacji .env
nano .env
```
# App
APP_NAME='Company Name'

# Production env
# APP_ENV=production
# APP_DEBUG=false

# Queue
QUEUE_CONNECTION=database

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app_xx
DB_USERNAME=app_xx
DB_PASSWORD=toor

# Localhost smtp (mail host name == localhost tls cert cn="" name)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@localhost
MAIL_FROM_NAME="${APP_NAME}"

# Remote smtp with gmail.com (change email address)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=YourPassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

# Default Storage::disk()
FILESYSTEM_DISK=public

# Sessions
SESSION_DRIVER=file
SESSION_LIFETIME=120
# SESSION_SECURE_COOKIE=true
```

## Vue 3 konfiguracja (old)
```sh
# Jako root
su
sudo apt install npm
npm install -g @vue/cli
exit

# Jako user
cd app.xx

# Dev
npm install
npm run dev

# Producja
npm install --production
npm run prod

# Zainstalowane pakiety Vue w package.json
npm install vue@next --save
npm install vuex@next --save
npm install vue-i18n@next --save
npm install vue-router@4 --save
npm install vue-axios --save
npm install vue-loader@next --save-dev

# Konfiguracja Vue z Laravel
webpack.mix.js
```

## Vue webpack.mix.js
```php
const mix = require('laravel-mix')
const axios = require('axios')

// mix.js('resources/js/app.js', 'public/js').vue().postCss('resources/css/app.css', 'public/css', []).extract(['vue'])

mix.js('resources/js/app.js', 'public/js')
	.vue()
	.postCss('resources/css/app.css', 'public/css', [
		// processCssUrls: false
	])
	.webpackConfig((webpack) => {
		 return {
			plugins: [
				 new webpack.DefinePlugin({
					VUE_OPTIONS_API: true,
					VUE_PROD_DEVTOOLS: false,

					// Vue js config console.log("Env", process.env.LOCALE_FALLBACK)
					'process.env': {
						// NODE_ENV: '"development"',
						// ENDPOINT: '"http://localhost:3000"',
						FOO: "'BAR'",
						LOCALES: '"en|pl"',
						LOCALE_FALLBACK: '"en"'
					}
				}),
			],
		};
	})
	.options({
		terser: {
			extractComments: false,
		}
	})

mix.sass('resources/sass/app.scss', 'public/css').options({
	// processCssUrls: false
});

if (mix.inProduction()) {
	mix.version();
}
```

## Laravel aktualizacja pakietów
```sh
rm -rf vendor

composer update
composer dump-autoload -o
```

## Klucze, konfig, cache
```sh
php artisan key:generate
php artisan config:clear
php artisan cache:clear
```

## Migracja db
```sh
php artisan migrate:fresh --seed
php artisan migrate:fresh --env=testing --seed
```

## Linki do przesyłanych plików
```sh
php artisan storage:link
```

## Sesje php
```sh
php artisan session:table
php artisan migrate
```

## Kolejka emails
```sh
php artisan queue:table
php artisan migrate
```

# Użytkownicy demo
Usunąć po testach z bazy danych (lub zmienić hasła)
```sh
# Użytkownik
user@app.xx | admin@app.xx | worker@app.xx
# Hasło:
password123
```

## Php

### Php.ini konfiguracja
sudo nano /etc/php/8.0/fpm/php.ini
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

### Instalacja 8.0/8.1
```sh
sudo apt install -y curl wget gnupg2 ca-certificates lsb-release software-properties-common

sudo curl https://packages.sury.org/php/apt.gpg | gpg --dearmor > /usr/share/keyrings/sury-php.gpg
sudo echo "deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/sury-php.list

sudo apt update && sudo apt upgrade -y

sudo apt install -y php8.0-fpm
sudo apt install -y php8.0-{mysql,xml,curl,mbstring,opcache,gd,imagick,imap,bcmath,bz2,zip,intl,redis,memcache,memcached}

sudo update-alternatives --list php
sudo update-alternatives --set php /usr/bin/php8.0
```

### Php restart
```sh
sudo service php8.0-fpm restart
```

## Nginx

### Mginx lokalna domena (app.xx)
nano /etc/hosts
```sh
127.0.0.1 app.xx
```

### Nginx konfiguracja hosta (app.xx)
sudo nano /etc/nginx/sites-enabled
```conf
server {
	listen 80;
	listen [::]:80;
	server_name app.xx;
	root /home/www/app.xx/public;
	index index.php index.html;

    charset utf-8;
	disable_symlinks off;
	client_max_body_size 100M;

	access_log /home/www/app.xx/storage/logs/nginx/app.xx.access.log;
	error_log /home/www/app.xx/storage/logs/nginx/app.xx.error.log warn;

	location / {
		# try_files $uri $uri/ =404;
		try_files $uri $uri/ /index.php$is_args$args;
	}

	location ~ \.php$ {
		# fastcgi_pass 127.0.0.1:9000;
		fastcgi_pass unix:/run/php/php8.0-fpm.sock;
		include snippets/fastcgi-php.conf;
	}

	location ~* \.(js|css|scss|txt|pdf|jpg|jpeg|gif|png|webp|ico|svg|gz|mp3|mp4|mov|ogg|ogv|webm)$ {
		expires 1M;
		access_log off;
		add_header Cache-Control "public, no-transform";
	}

	location = /favicon.ico {
		rewrite . /favicon/favicon.ico;
	}
}
```

### Nginx restart
```sh
sudo service php8.0-fpm restart
sudo service nginx restart
```

## Postfix

### hostname
```sh
sudo nano /etc/hostname
new-server-name-here

sudo nano /etc/hosts
127.0.0.1 new-server-name-here localhost
```

### Smtp host i domena
```cf
mydestination = $myhostname, app.xx, vps.localhost, localhost.localhost, localhost
relayhost =
relay_domains =
```

### Wirtualne skrzynki konfiguracja
sudo nano /etc/postfix/main.cf
```cf
virtual_alias_domains = app.xx
virtual_alias_maps = hash:/etc/postfix/virtual
```

### Wirtualne skrzynki lista
sudo nano /etc/postfix/virtual
```sh
admin@app.xx root
@app.xx username
```

### Aktualizacja listy
```sh
postmap /etc/postfix/virtual
sudo service postfix restart
```

## Ufw firewall

### Ufw porty
```sh
# Dla wszystkich ip
sudo ufw allow 22/tcp

# Dla maski adresów
sudo ufw allow from 1.2.0.0/16 to any port 22
sudo ufw allow from 1.2.0.0/16 to any port 22 proto tcp
```

## Ufw http, https
```sh
# sudo ufw allow 25/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### Ufw defaultowa polityka
```sh
sudo ufw default allow outgoing
sudo ufw default deny incoming
```

### Ufw włącz
```sh
sudo ufw logging on
sudo ufw enable
```

## Restart serwerów
```sh
sudo service php8.0-fpm restart
sudo service nginx restart
sudo service postfix restart
```

# Testing

### Migracje bazy danych
```sh
php artisan --env=testing migrate:fresh --seed
```

### Testowanie aplikacji
```sh
cd app-dir
php artisan test --stop-on-failure
```

### Ustawienia
phpunit.xml
```xml
<env name="APP_ENV" value="testing" force="true"/>
<env name="APP_DEBUG" value="true" force="true"/>
```
