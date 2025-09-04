#!/bin/bash

sudo adduser www-data
sudo chown -R $USER:www-data /var/www/Amdr
sudo find /var/www/Amdr -type f -exec chmod 644 {} \;
sudo find /var/www/Amdr -type d -exec chmod 755 {} \;
sudo chgrp -R www-data /var/www/Amdr/storage bootstrap/cache
sudo chmod -R ug+rwx /var/www/Amdr/storage bootstrap/cache
sudo chmod -R 777 /var/www/Amdr/storage
