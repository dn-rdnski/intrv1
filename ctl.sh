#!/bin/bash

start()
{
    systemctl start audit-daemon
    systemctl start php-fpm
    systemctl start httpd
}

stop()
{
    systemctl stop audit-daemon
    systemctl stop httpd
    systemctl stop php-fpm
}

status()
{
    systemctl status httpd php-fpm audit-daemon
}