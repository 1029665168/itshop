/*
Navicat MySQL Data Transfer

Source Server         : 本地
Source Server Version : 50624
Source Host           : localhost:3306
Source Database       : itshop

Target Server Type    : MYSQL
Target Server Version : 50624
File Encoding         : 65001

Date: 2017-07-28 17:31:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for it_manager
-- ----------------------------
DROP TABLE IF EXISTS `it_manager`;
CREATE TABLE `it_manager` (
  `manager_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `manager_name` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT 'email',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  `last_login` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(15) NOT NULL DEFAULT '' COMMENT '最后登录ip',
  `role_id` smallint(5) DEFAULT NULL COMMENT '角色id',
  PRIMARY KEY (`manager_id`),
  KEY `user_name` (`manager_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='后台管理员数据表';

-- ----------------------------
-- Records of it_manager
-- ----------------------------
INSERT INTO `it_manager` VALUES ('8', 'admin', '4f35c0f63709f538fa0edfe8d8fc5b72', '', '0', '1500969362', '127.0.0.1', null);

-- ----------------------------
-- Table structure for it_users
-- ----------------------------
DROP TABLE IF EXISTS `it_users`;
CREATE TABLE `it_users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `user_name` varchar(30) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `password` varchar(36) NOT NULL COMMENT '用户名登陆密码',
  `user_real_name` varchar(20) NOT NULL DEFAULT '' COMMENT '用户真实名称',
  `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户性别，0->保密 1->男 2->女',
  `mobile` varchar(30) DEFAULT NULL COMMENT '手机号码',
  `email` varchar(50) DEFAULT NULL COMMENT '电子邮箱',
  `reg_time` int(11) NOT NULL COMMENT '用户注册时间',
  `reg_ip` varchar(30) NOT NULL COMMENT '用户注册时ip',
  `last_time` int(11) DEFAULT NULL COMMENT '用户最后登陆的时间',
  `last_ip` varchar(30) DEFAULT NULL COMMENT '用户最后登陆的ip',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '当前数据状态，1，表示显示，0，表示隐藏',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='注册用户表';

-- ----------------------------
-- Records of it_users
-- ----------------------------
INSERT INTO `it_users` VALUES ('1', 'lining', 'aecd9e41aad6c4dfa0257eaee46fcb61', '', '0', null, '1029665168@qq.com', '1501227450', '127.0.0.1', '1501228538', '127.0.0.1', '1');
INSERT INTO `it_users` VALUES ('2', 'zhangming', 'aecd9e41aad6c4dfa0257eaee46fcb61', '', '0', null, '', '1501233329', '127.0.0.1', '1501233334', '127.0.0.1', '1');
