sudo apt update

echo "INSTALL PHP..."
sudo apt install php php-cli php-fpm php-json php-common php-mysql php-zip php-gd php-mbstring php-curl php-xml php-pear php-bcmath

echo "INSTALL NGINX..."
sudo apt-get install nginx

sudo service nginx stop

rm /etc/nginx/conf.d/default

sudo cp nginx.conf /etc/nginx/conf.d/default

sudo service nginx restart

echo "INSTALL GCC..."
sudo apt install build-essential

echo "INSTALL GIT..."
sudo apt install git

echo "INSTALL 3PROXY..."
git clone https://github.com/z3apa3a/3proxy
cd 3proxy
ln -s Makefile.Linux Makefile
make
sudo make install
cd ..
rm -rf 3proxy

echo "3PROXY PHP CONFIGURE"
sudo chmod -R 777 /etc/3proxy/3proxy.cfg

echo "RUN 3proxy.conf"
php 3proxyconf.php

sudo apt-get install supervisor

printf "[program:mtproxy]\ncommand=3proxy /etc/3proxy/3proxy.cfg\nautostart=true\nautorestart=true\nchmod=0777\nchown=appuser:supervisor\n" > /etc/supervisor/conf.d/mtproxy.conf

echo "SUPERVISORCTL UPDATING..."
sudo supervisorctl update

echo "RESTARTING SUPERVISORCTL..."
sudo supervisorctl restart mtproxy

git clone https://github.com/mehmetbeyHZ/m2x-proxy.git

rm -rf /var/www/html/

mv m2x-proxy/ /var/www/html/

sudo chmod -R 777 /var/www/html/

cd /var/www/html/

composer install