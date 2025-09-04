#!/bin/bash

PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
rm -rf /var/www/Amdr/laravel-echo-server.lock && laravel-echo-server-with-webhooks start
