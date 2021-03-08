DROP TABLE IF EXISTS article_candidate;
CREATE TABLE article_candidate (url VARCHAR(255) NOT NULL, title VARCHAR(100) NOT NULL, ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP, created DATETIME, summary VARCHAR(924), category VARCHAR(80), class_name VARCHAR(40), confidence FLOAT(10), site_name VARCHAR(80), company_id VARCHAR(20) NOT NULL, news_id VARCHAR(100) NOT NULL, cid VARCHAR(24), cid_alias VARCHAR(100));
CREATE UNIQUE INDEX index_url_newsid ON article_candidate (url, news_id);
CREATE INDEX index_article_candidate ON article_candidate (company_id,news_id,created);
DROP TABLE IF EXISTS category_list;
CREATE TABLE category_list (category_id VARCHAR(80) NOT NULL, category_data TEXT NOT NULL, company_id VARCHAR(20) NOT NULL);
CREATE INDEX index_category_list ON category_list (company_id);
DROP TABLE IF EXISTS rss_list;
CREATE TABLE rss_list (rss_id VARCHAR(80) NOT NULL, rss_data TEXT NOT NULL, company_id VARCHAR(20) NOT NULL);
CREATE INDEX index_rss_list ON rss_list (company_id);
DROP TABLE IF EXISTS site_names_list;
CREATE TABLE site_names_list (site_names_id VARCHAR(80) NOT NULL, site_names_data TEXT NOT NULL, company_id VARCHAR(20) NOT NULL);
DROP TABLE IF EXISTS click_counter;
CREATE TABLE click_counter (company_id VARCHAR(20) NOT NULL, news_id VARCHAR(100) NOT NULL, url VARCHAR(1024) NOT NULL, issue CHAR(10) NOT NULL, ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP, title VARCHAR(100));
CREATE INDEX index_click_counter ON click_counter (company_id,news_id,issue);
DROP TABLE IF EXISTS access_counter;
CREATE TABLE access_counter (company_id VARCHAR(20) NOT NULL, news_id VARCHAR(100) NOT NULL, issue CHAR(10) NOT NULL, ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE INDEX index_access_counter ON access_counter (company_id,news_id,issue);
DROP TABLE IF EXISTS configuration;
<<<<<<< HEAD
CREATE TABLE configuration (company_id VARCHAR(40) NOT NULL, w_apikey VARCHAR (80), w_url VARCHAR (200));
=======
CREATE TABLE configuration (company_id VARCHAR(40) NOT NULL, w_username VARCHAR(80), w_password VARCHAR(80));
>>>>>>> origin/master
DROP TABLE IF EXISTS users_list;
CREATE TABLE users_list (company_id VARCHAR(20) NOT NULL, user_id VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, password_expires DATETIME NOT NULL, role VARCHAR(10) NOT NULL);
INSERT INTO users_list (company_id, user_id, password, password_expires, role) VALUES ( "root", "root", "root", "2060-01-01 00:00:00", "su");
DROP TABLE IF EXISTS classifier_list;
CREATE TABLE classifier_list (cid VARCHAR(24) PRIMARY KEY, cid_alias VARCHAR(100) NOT NULL, company_id VARCHAR(20) NOT NULL, ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
DROP TABLE IF EXISTS preference;
CREATE TABLE preference (news_id VARCHAR(100) NOT NULL, cid_alias VARCHAR(100) NOT NULL, company_id VARCHAR(20) NOT NULL, category_id VARCHAR(80) NOT NULL, rss_id VARCHAR(80) NOT NULL, site_names_id VARCHAR(80) NOT NULL, default_title VARCHAR(100) NOT NULL, period_day VARCHAR(40) NOT NULL, period_hour CHAR(2) NOT NULL, fetch_num CHAR(4) NOT NULL, signature TEXT NOT NULL);
DROP TABLE IF EXISTS login_record;
<<<<<<< HEAD
CREATE TABLE login_record (company_id VARCHAR(20) NOT NULL, user_id VARCHAR(20) NOT NULL, stamp VARCHAR(10) NOT NULL, description VARCHAR(20) NOT NULL, ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
=======
CREATE TABLE login_record (company_id VARCHAR(20) NOT NULL, user_id VARCHAR(20) NOT NULL, stamp VARCHAR(10) NOT NULL, description VARCHAR(20) NOT NULL, ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
>>>>>>> origin/master
