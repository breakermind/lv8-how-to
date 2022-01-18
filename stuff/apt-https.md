### Instalacja pakietów https
```sh
sudo apt install -y apt-transport-https
sed -i 's/http\:/https\:/g' /etc/apt/sources.list

sudo apt update -y

sudo apt install -y net-tools mailutils dnsutils ufw nginx mariadb-server php-fpm postfix
```
