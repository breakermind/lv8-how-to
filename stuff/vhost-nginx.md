### Virtual host
/etc/nginx/sites-enabled/app.xx.conf
```sh
server {
	listen 80;
	listen [::]:80;
	server_name app.xx;
	root /www/app.xx/public;
	index index.php index.html;

	disable_symlinks off;
	client_max_body_size 100M;

	access_log /var/log/nginx/app.xx.access.log;
	error_log /var/log/nginx/app.xx.error.log warn;

	location / {
		# try_files $uri $uri/ =404;
		try_files $uri $uri/ /index.php$is_args$args;
	}

	location ~ \.php$ {
		# fastcgi_pass 127.0.0.1:9000;
		fastcgi_pass unix:/run/php/php8.1-fpm.sock;
		include snippets/fastcgi-php.conf;
	}

	location ~* \.(jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp3|mp4|mov|ogg|ogv|webm|webp)$ {
		expires 1M;
		access_log off;
		add_header Cache-Control "public, no-transform";
	}

	location = /favicon.ico {
		rewrite . /favicon/favicon.ico;
	}
}
```
