# SBNews
メール配信用ニュースレターの作成を補助するWebアプリケーションです。

## インストール
SBNewsはLAMPスタック上で稼働する汎用的なWebアプリケーションです。以下の環境で動作検証を行っています。

### Linux
Google Compute Engineの「centos-7-v20180611」、およびAlibaba Cloudの「CentOS 7.4 64bit(セキュリティ強化)」。
ファイアウォール設定では「TCP:80」のプロトコル／ポートを許可。

### Apache
```
# httpd -v
Server version: Apache/2.4.6 (CentOS)

// ウェルカムページの無効化
# cd /etc/httpd/conf.d/
# mv welcome.conf welcome.conf.org
# mv autoindex.conf autoindex.conf.org

// httpd.conf
<Directory "/var/www/html">
Options FollowSymLinks
AllowOverride None
#Require all granted
</Directory>

// confファイル最終行に追加
TraceEnable off
Header append X-FRAME-OPTIONS "SAMEORIGIN"
ServerTokens ProductOnly
ServerSignature off
```
### PHP
```
# php -v
PHP 7.2.8

# vi /etc/php.ini

expose_php = Off
max_execution_time = 3600
max_input_time = 3600
post_max_size = 20M
upload_max_filesize = 20M
date.timezone = "Asia/Tokyo"
mbstring.language = Japanese
mbstring.internal_encoding = UTF-8
mbstring.http_input = UTF-8
mbstring.http_output = pass
mbstringm.encoding_translation = On
mbstring.detect_order = auto
mbstring.substitute_character = non
```

### MariaDB
```
Server version: 5.5.56-MariaDB MariaDB Server

// mysql_secure_installation の実行

# vi /etc/my.cnf
[mysqld]
datadir=/var/lib/mysql
socket=/var/lib/mysql/mysql.sock
# Disabling symbolic-links is recommended to prevent assorted security risks
symbolic-links=0
# Settings user and group are ignored when systemd is used.
# If you need to run mysqld under a different user or group,
# customize your systemd unit file for mariadb according to the
# instructions in http://fedoraproject.org/wiki/Systemd
character-set-server=utf8
default-time-zone='+9:00'

[mysqld_safe]
log-error=/var/log/mariadb/mariadb.log
pid-file=/var/run/mariadb/mariadb.pid

#
# include all files from the config directory
#
!includedir /etc/my.cnf.d
```

### SBNews
```
# mkdir /var/www/html/sbnews
# chown apache:apache /var/www/html/sbnews
# chmod 774 /var/www/html/sbnews
# cd /var/tmp/
# wget https://github.com/softbankbiz/SBNews/archive/master.zip
# unzip SBNews-master.zip
# rsync -avP ./SBNews-master/ /var/www/html/sbnews/
# chown -R apache:apache /var/www/html/sbnews/*
```

## SBNewsのセットアップ
