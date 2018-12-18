# SBNews
メール配信用ニュースレターの作成を補助するWebアプリケーションです。

## インストール
SBNewsはLAMPスタック上で稼働する汎用的なWebアプリケーションです。以下の環境で動作検証を行っています。

### Linux
Alibaba CloudのOSイメージ：「CentOS 7.4 64bit(セキュリティ強化)」。
ファイアウォール設定で「TCP:80」のプロトコル／ポートを許可。

### SELinux
SELinuxが有効かチェック。「enforcing」になっていなければ、下記の設定ファイルを修正して、サーバを再起動。
さらにsemanageコマンドのインストール。

```
# getenforce
disabled   //SELinux無効なので下記の config を修正

# vi /etc/selinux/config

SELINUX=enforcing

# reboot    // サーバの再起動

# yum provides /usr/sbin/semanage    // パッケージ名を確認する
policycoreutils-python-2.5-22.el7.x86_64 : SELinux policy core python utilities

# yum -y install policycoreutils-python-2.5-22.el7.x86_64
```

### Apache
```
# yum check-update
# yum -y install httpd
# httpd -v
Server version: Apache/2.4.6 (CentOS)

# service httpd start
# chkconfig httpd on

// ウェルカムページの無効化
# cd /etc/httpd/conf.d/
# mv welcome.conf welcome.conf.org
# mv autoindex.conf autoindex.conf.org
# rm -f README 

// httpd.confの設定
# vim /etc/httpd/conf/httpd.conf

ServerAdmin root@localhost // 適宜変更
ServerName localhost:80    // IPアドレスを入れておく

<Directory "/var/www/html">
Options FollowSymLinks
AllowOverride None
#Require all granted
</Directory>

// ファイル最終行に追加
TraceEnable off
ServerTokens ProductOnly
ServerSignature off
Header append X-FRAME-OPTIONS "SAMEORIGIN"
Header set X-Content-Type-Options nosniff
Header set X-XSS-Protection "1; mode=block"


// Apache再起動
# systemctl restart httpd
```
### PHP
php7.2系をインストールします。デフォルトのphp(5.4系）がインストールされていないことを確認。

```
yum list installed | grep php
```

もし5.4系がインストールされていたら、php5系のパッケージを削除。

```
# yum remove php*
```

CentOS 7系デフォルトのレポジトリにphpの7.2系は含まれていないので、remiレポジトリを追加してインストールする。

```
# yum -y install http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
# yum -y install --enablerepo=remi,remi-php72 php php-mbstring php-xml php-xmlrpc php-gd php-pdo php-pecl-mcrypt php-mysqlnd php-pecl-mysql

# php -v
PHP 7.2.13

// php.iniの設定
# vim /etc/php.ini

expose_php = Off
max_execution_time = 3600
max_input_time = 3600
memory_limit = 2048M
post_max_size = 20M
upload_max_filesize = 20M
date.timezone = "Asia/Tokyo"
session.cookie_httponly = 1
session.gc_divisor = 1
session.gc_maxlifetime = 3600
mbstring.language = Japanese
mbstring.internal_encoding = UTF-8
mbstring.http_input = UTF-8
mbstring.http_output = pass
mbstringm.encoding_translation = On
mbstring.detect_order = auto
mbstring.substitute_character = none

// Apache再起動
# systemctl restart httpd
```

### MariaDB
```
# curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
# yum -y install mariadb-server
# systemctl start mariadb
# systemctl enable mariadb
# mysql_secure_installation

# mysql -V
mysql  Ver 15.1 Distrib 10.3.11-MariaDB, for Linux (x86_64) using readline 5.1

// my.cnfの設定
# vim /etc/my.cnf
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

// DBの再起動
# systemctl restart mariadb

// SBNews用のデータベースとユーザーを作成、DB名（sbnews_db）／ユーザー名（sbnews_user）は任意で
# mysql -u root -p

DROP DATABASE IF EXISTS sbnews_db;
CREATE DATABASE sbnews_db DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'sbnews_user'@'localhost' IDENTIFIED BY 'XXXXXXXXXXXXXXXX';
GRANT ALL ON sbnews_db.* TO 'sbnews_user'@'localhost';
```

### SBNewsインストール
```
// 必要なツールを用意しておく
# yum install unzip
# yum install rsync

// SBNews用のドキュメントルート
# mkdir /var/www/html/sbnews
# chown apache:apache /var/www/html/sbnews
# chmod 774 /var/www/html/sbnews

// SBNewsのソース取得
# cd /var/tmp/
# wget https://github.com/softbankbiz/SBNews/archive/master.zip
# unzip master.zip

// SBNewsのデプロイ
# rsync -avrP ./SBNews-master/ /var/www/html/sbnews/
# chown -R apache:apache /var/www/html/sbnews

// SELinuxの設定
# semanage fcontext -a -t httpd_sys_rw_content_t /var/www/html/sbnews
# restorecon -v /var/www/html/sbnews
# semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/sbnews/images(/.*)?"
# restorecon -R -v /var/www/html/sbnews/images

# cat /etc/selinux/targeted/contexts/files/file_contexts.local  // 設定内容確認
# reboot  // サーバ再起動

// クローラのスケジュールジョブ設定
crontab -e
0 * * * * php -f /var/www/html/sbnews/cron_job.php
```

### SBNewsアップデート
```
// SBNewsのソース取得
# cd /var/tmp/
# wget https://github.com/softbankbiz/SBNews/archive/master.zip
# unzip master.zip

// SBNewsのデプロイ
# rsync -avrP ./SBNews-master/ /var/www/html/sbnews/
# chown -R apache:apache /var/www/html/sbnews

// 不要になったファイルの削除
# rm -f master.zip
# rm -rdf SBNews-master
```

## SBNewsのセットアップ
インストールが完了したら `http://***your-server-ip-address***/sbnews/` にアクセスします。
インストール直後は、「SBNewsのセットアップ」ページが開きます。指示に従って「データベース名」
「データベースのユーザー名」「データベースのパスワード」を設定してください。
「書き込み権限がない」と言われたら、SELinuxの設定を確認します。
セットアップが完了したら「利用開始する」ボタンをクリックします。

ログインページが開いたら、

|設定項目 |値 |
|:---|:---|
|企業ID： |root |
|ユーザーID： |root |
|パスワード： |root |

でログインできます。初回ログイン時には、パスワードの変更を求められます。その後の操作は、トップメニューの「ドキュメント」を参照してください。


