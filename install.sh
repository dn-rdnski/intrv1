#!/bin/bash

cd "$(dirname $0)"

web_server_dir="/var/www/html/"

install_dependencies() 
{
    echo "starting..."
    dnf install -y httpd php php-fpm
    dnf -y install dnf-plugins-core
    if [[ $? -ne 0 ]]; then
        echo "install not ok"
        exit 0
    else
        echo "ok"
    fi
}

build_and_install_daemon() 
{
    local $program="storage-daemon"

    pushd storagedaemon/ >/dev/null
        make
        if [[ $? -ne 0 ]]; then
            echo "build not ok"
            exit 0
        fi
        cp $program /usr/local/bin
        chmod 755 /usr/local/bin/$program
    popd > /dev/null

    cp static/audit-daemon.service /etc/systemd/
    systemctl daemon-reload
}

copy_php_scripts() 
{
    cp "http/*.php" $web_server_dir
    chown apache:apache "$web_server_dir/*"
}

install_dependencies && copy_php_scripts
build_and_install_daemon