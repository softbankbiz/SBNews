# SBNews
メール配信用ニュースレターの作成を補助するWebアプリケーションです。

## インストール
SBNewsはLAMPスタック上で稼働する汎用的なWebアプリケーションです。Azureの「オペレーティング システム：Linux (centos 7.6.1810）」「サイズ：Standard B1s (1 vcpu 数、1 GiB メモリ)」で検証しています（仮想マシンのメモリが0.5GiBだと、インストールに失敗します）。

### インストールスクリプトの準備
Azureダッシュボードの「Cloud shell」からの実行を想定しています。仮想マシンにログインしたら、エディタを使ってホームディレクトリにインストールスクリプト用のファイルを作成します。後述のインストールスクリプトをコピー＆ペーストし、4〜7行のデータベース関連情報を記載し保存します。なお、ここで記載したデータベース関連情報は、SBNewsセットアップ時に必要となります。

```
$ cd
$ sudo vi SBNews_install.sh
```

作成したインストールスクリプトを実行します。パスワードを聞かれたら、ログインユーザーのパスワードを手動で入力します。

```
$ bash SBNews_install.sh
$ password for LOGINUSERNAME:            <= パスワード入力
```

インストールが終わったら、Azureコンソールから<span style="color: red; ">仮想マシンを再起動</span>します。

### インストールスクリプト

```
#!/bin/bash

################### 変更してください ###################
dbname=""              # SBNews用に作成するデータベース名（SBNewsのセットアップ時に必要）
dbuser=""              # 上記を利用するユーザー名（SBNewsのセットアップ時に必要）
dbpassword=""          # 上記ユーザーのパスワード（SBNewsのセットアップ時に必要）
DBRootPassword=""      # 上記データベースのルートパスワード
################### 変更してください ###################

sudo yum check-update

# install Apache
sudo yum -y install httpd
sudo service httpd start
sudo chkconfig httpd on

# setup Apache
sudo rm  -f /etc/httpd/conf.d/welcome.conf
sudo rm  -f /etc/httpd/conf.d/autoindex.conf
sudo rm  -f /etc/httpd/conf.d/README
sudo sed -i 's/Options Indexes FollowSymLinks/Options FollowSymLinks/' /etc/httpd/conf/httpd.conf
sudo sed -i '156s/Require all granted/\# Require all granted/' /etc/httpd/conf/httpd.conf
sudo sed -i '$a TraceEnable off' /etc/httpd/conf/httpd.conf
sudo sed -i '$a ServerTokens ProductOnly' /etc/httpd/conf/httpd.conf
sudo sed -i '$a ServerSignature off' /etc/httpd/conf/httpd.conf
sudo sed -i '$a Header append X-FRAME-OPTIONS \"SAMEORIGIN\"' /etc/httpd/conf/httpd.conf
sudo sed -i '$a Header set X-Content-Type-Options nosniff' /etc/httpd/conf/httpd.conf
sudo sed -i '$a Header set X-XSS-Protection \"1; mode=block\"' /etc/httpd/conf/httpd.conf
sudo systemctl restart httpd

# install php
sudo yum -y install http://rpms.famillecollet.com/enterprise/remi-release-7.rpm
sudo yum -y install --enablerepo=remi,remi-php72 php php-mbstring php-xml php-xmlrpc php-gd php-pdo php-pecl-mcrypt php-mysqlnd php-pecl-mysql

# setup php
sudo sed -i 's/expose_php = On/expose_php = Off/' /etc/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 3600/' /etc/php.ini
sudo sed -i 's/max_input_time = 60/max_input_time = 3600/' /etc/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 2048M/' /etc/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php.ini
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php.ini
sudo sed -i 's|;date.timezone =|date.timezone = \"Asia/Tokyo\"|' /etc/php.ini
sudo sed -i 's/session.cookie_httponly =/session.cookie_httponly = 1/' /etc/php.ini
sudo sed -i 's/session.gc_divisor = 1000/session.gc_divisor = 1/' /etc/php.ini
sudo sed -i 's/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 3600/' /etc/php.ini
sudo sed -i 's/;mbstring.language = Japanese/mbstring.language = Japanese/' /etc/php.ini
sudo sed -i 's/;mbstring.internal_encoding =/mbstring.internal_encoding = UTF-8/' /etc/php.ini
sudo sed -i 's/;mbstring.http_input =/mbstring.http_input = UTF-8/' /etc/php.ini
sudo sed -i 's/;mbstring.http_output =/mbstring.http_output = pass/' /etc/php.ini
sudo sed -i 's/;mbstring.encoding_translation = Off/mbstring.encoding_translation = On/' /etc/php.ini
sudo sed -i 's/;mbstring.detect_order = auto/mbstring.detect_order = auto/' /etc/php.ini
sudo sed -i 's/;mbstring.substitute_character = none/mbstring.substitute_character = none/' /etc/php.ini
sudo systemctl restart httpd

# install MariaDB
curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | sudo bash
sudo yum -y install mariadb-server
sudo systemctl start mariadb
sudo systemctl enable mariadb

# setup MariaDB
sudo mysqladmin -u root -f DROP test
sudo mysqladmin -u root password $DBRootPassword
sudo sed -i '1c [mysqld]' /etc/my.cnf
sudo sed -i '2c datadir=/var/lib/mysql' /etc/my.cnf
sudo sed -i '3c socket=/var/lib/mysql/mysql.sock' /etc/my.cnf
sudo sed -i '4c symbolic-links=0' /etc/my.cnf
sudo sed -i '5c character-set-server=utf8' /etc/my.cnf
sudo sed -i "6c default-time-zone=\'+9:00\'" /etc/my.cnf
sudo sed -i '7c \\n' /etc/my.cnf
sudo sed -i '8c [mysqld_safe]' /etc/my.cnf
sudo sed -i '9c log-error=/var/log/mariadb/mariadb.log' /etc/my.cnf
sudo sed -i '10c pid-file=/var/run/mariadb/mariadb.pid' /etc/my.cnf
sudo sed -i '11c \\n' /etc/my.cnf
sudo sed -i '$a !includedir /etc/my.cnf.d' /etc/my.cnf
sudo systemctl restart mariadb

# setup DB for SBNews
sudo echo DELETE FROM mysql.user WHERE user = \'\'\; > /tmp/setup.mysql
sudo echo DELETE FROM mysql.user WHERE host != \'localhost\'\;  >> /tmp/setup.mysql
sudo echo DROP DATABASE IF EXISTS $dbname\; >> /tmp/setup.mysql
sudo echo CREATE DATABASE $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci\; >> /tmp/setup.mysql
sudo echo CREATE USER \'$dbuser\'@\'localhost\' IDENTIFIED BY \'$dbpassword\'\; >> /tmp/setup.mysql
sudo echo GRANT ALL ON $dbname.* TO \'$dbuser\'@\'localhost\'\; >> /tmp/setup.mysql
sudo mysql -uroot -p$DBRootPassword < /tmp/setup.mysql
sudo rm -f /tmp/setup.mysql

# install utilitiy tools
sudo yum -y install wget
sudo yum -y install unzip
sudo yum -y install rsync

# install SBNews
sudo mkdir /var/www/html/sbnews
sudo chmod 774 /var/www/html/sbnews
cd /var/tmp/
sudo wget https://github.com/softbankbiz/SBNews/archive/master.zip
sudo unzip master.zip
sudo rsync -avrP ./SBNews-master/ /var/www/html/sbnews/
sudo chown -R apache:apache /var/www/html/sbnews
sudo rm -f master.zip
sudo rm -rdf SBNews-master
cd
sudo echo '0 * * * * php -f /var/www/html/sbnews/cron_job.php' > cron.conf
sudo crontab ./cron.conf

# setup SELinux
sudo yum -y install policycoreutils-python.x86_64
sudo semanage fcontext -a -t httpd_sys_rw_content_t /var/www/html/sbnews
sudo restorecon -v /var/www/html/sbnews
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/sbnews/images(/.*)?"
sudo restorecon -R -v /var/www/html/sbnews/images

# completed
echo ""
echo ""
echo "SBNews_install.sh completed."
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


## SBNewsアップデート

SBNewsをアップデートするには、仮想マシンにログインして、下記の処理を実施します。

```
// SBNewsのソース取得
$ cd /var/tmp/
$ sudo wget https://github.com/softbankbiz/SBNews/archive/master.zip
$ sudo unzip master.zip

// SBNewsのデプロイ
$ sudo rsync -avrP ./SBNews-master/ /var/www/html/sbnews/
$ sudo chown -R apache:apache /var/www/html/sbnews

// 不要になったファイルの削除
$ sudo rm -f master.zip
$ sudo rm -rdf SBNews-master
```
