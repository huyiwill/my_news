/*
Navicat MySQL Data Transfer

Source Server         : hdm144528485_db
Source Server Version : 50148
Source Host           : hdm144528485.my3w.com:3306
Source Database       : hdm144528485_db

Target Server Type    : MYSQL
Target Server Version : 50148
File Encoding         : 65001

Date: 2018-01-25 15:14:55
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `easy_map`
-- ----------------------------
DROP TABLE IF EXISTS `easy_map1`;
CREATE TABLE `easy_map1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lng` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '经度',
  `lat` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '纬度',
  `pos` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '用户地理位置',
  `time` varchar(55) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT '记录时间',
  `ip` varchar(75) CHARACTER SET utf8 NOT NULL DEFAULT '' COMMENT 'IP地址',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `pos` (`pos`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of easy_map
-- ----------------------------
