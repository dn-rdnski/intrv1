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

usage()
{
    echo "Usage: $0 {start|stop|status}"
    exit 0
}

if [[ $# -ne 1 ]]; then
    usage
fi

case $1 in
start)
    start
    ;;
stop)
    stop
    ;;
status)
    status
    ;;
*)
    echo "Usage: $0 {start|stop|status}"
    exit 1
    ;;
esac