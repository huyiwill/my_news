/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : easycms

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2018-01-09 16:37:56
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `easy_news_cate`
-- ----------------------------
DROP TABLE IF EXISTS `easy_news_cate`;
CREATE TABLE `easy_news_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_cate` varchar(55) NOT NULL DEFAULT '' COMMENT '新闻类别',
  `news_title` varchar(75) NOT NULL DEFAULT '' COMMENT '新闻标题',
  `news_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '新闻描述',
  `news_time` varchar(55) NOT NULL DEFAULT '' COMMENT '新闻发布时建',
  `news_img_url` varchar(255) NOT NULL DEFAULT '' COMMENT '新闻图片地址',
  `news_content_url` varchar(255) NOT NULL DEFAULT '' COMMENT '新闻详情地址',
  `news_filename` varchar(255) DEFAULT '' COMMENT '新闻标志',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1304 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of easy_news_cate
-- ----------------------------
