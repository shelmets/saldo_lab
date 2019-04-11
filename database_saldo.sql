drop database if exists saldo_lab;

create database saldo_lab;

use saldo_lab;

create table charges(id_charge int UNSIGNED NOT NULL AUTO_INCREMENT,
number_flat int UNSIGNED NOT NULL, 
date date NOT NULL,
cash int UNSIGNED NOT NULL, 
PRIMARY KEY (id_charge));

create table payments (id_payment int UNSIGNED NOT NULL AUTO_INCREMENT, 
number_flat int UNSIGNED NOT NULL, 
date date NOT NULL, 
cash int UNSIGNED NOT NULL, 
PRIMARY KEY (id_payment));

create table saldo (id_saldo int NOT NULL AUTO_INCREMENT, 
_id_charge int UNSIGNED NOT NULL, 
_id_payment int UNSIGNED NOT NULL,
PRIMARY KEY (id_saldo)
);


