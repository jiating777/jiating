/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : qiandao

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-04-13 08:47:01
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for schedule
-- ----------------------------
DROP TABLE IF EXISTS `schedule`;
CREATE TABLE `schedule` (
  `id` varchar(50) NOT NULL,
  `schoolId` int(11) DEFAULT NULL,
  `total` int(2) DEFAULT NULL COMMENT '每天总节数',
  `schedule` varchar(500) DEFAULT NULL COMMENT '序列化的作息时间',
  `createDate` int(10) DEFAULT NULL,
  `updateDate` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of schedule
-- ----------------------------
INSERT INTO `schedule` VALUES ('a8e50ce46fc22aac40cbe9cf7ab9e6ed', '3', '3', 'a:3:{i:0;a:2:{s:5:\"start\";s:5:\"08:20\";s:3:\"end\";s:5:\"09:05\";}i:1;a:2:{s:5:\"start\";s:5:\"09:15\";s:3:\"end\";s:5:\"10:00\";}i:2;a:2:{s:5:\"start\";s:5:\"10:20\";s:3:\"end\";s:5:\"11:05\";}}', '1554884989', '1554959416');
