-- phpMyAdmin SQL Dump
-- version 4.2.7.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2015
-- Server version: 5.5.39
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` varchar(32) NOT NULL DEFAULT '',
  `total_votes` int(11) NOT NULL DEFAULT '0',
  `total_value` float(11,4) NOT NULL DEFAULT '0.0000',
  `used_ips` longtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `ratings`
 ADD PRIMARY KEY (`id`);
