/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-03-05 15:07:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for test_grab_log
-- ----------------------------
DROP TABLE IF EXISTS `test_grab_log`;
CREATE TABLE `test_grab_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `num` int(11) DEFAULT NULL COMMENT '抢的数量',
  `product_id` int(11) DEFAULT NULL COMMENT '产品id',
  `grab_product_id` int(11) DEFAULT NULL COMMENT '秒杀id',
  `total` int(11) DEFAULT NULL COMMENT '秒杀总数',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of test_grab_log
-- ----------------------------
INSERT INTO `test_grab_log` VALUES ('1', '2314134', '34', '2134123412', '12342423', '400', '123421421');

-- ----------------------------
-- Table structure for test_grab_product
-- ----------------------------
DROP TABLE IF EXISTS `test_grab_product`;
CREATE TABLE `test_grab_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `num` int(11) DEFAULT NULL COMMENT '单人抢固定数量',
  `partition` int(11) NOT NULL DEFAULT '0' COMMENT '份数',
  `rest` int(11) DEFAULT NULL,
  `total` int(11) NOT NULL DEFAULT '0' COMMENT '总数',
  `mode` tinyint(4) NOT NULL DEFAULT '0' COMMENT '模式 1固定，2随机',
  `product_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '状态 0不可用，1 可用',
  `add_params` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT '额外参数',
  `start_time` int(11) DEFAULT '0' COMMENT '活动时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of test_grab_product
-- ----------------------------
INSERT INTO `test_grab_product` VALUES ('21', 'fsdaf', '1', '5', '5', '5', '0', '265', null, '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('22', 'asdfsaf', '1', '5', '5', '5', '0', '165', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('23', 'asdfdsaf', '1', '5', '5', '5', '0', '2342', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('24', 'asdfasf', '1', '5', '5', '5', '0', '2341', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('25', 'asdfasf', '1', '50', '50', '50', '0', '265265', null, '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('26', 'asdfasf', '1', '50', '50', '50', '0', '2165215', null, '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('27', 'asdfsa', '1', '20', '20', '20', '0', '2298895', '1', '', '1520216424', '1520916424');
INSERT INTO `test_grab_product` VALUES ('28', 'asdfdsafd', '1', '20', '20', '20', '0', '299', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('29', 'asdfsafd', '1', '20', '20', '20', '0', '9566', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('30', 'asdfasfasdf', '1', '50', '50', '50', '1', '95695', '1', '', '1520216424', '1520916424');
INSERT INTO `test_grab_product` VALUES ('31', '165', '1', '20', '20', '20', '0', '959', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('32', 'asdfasfasdfsa', '1', '20', '20', '20', '0', '5959', '1', '', '1520059521', '1520959521');
INSERT INTO `test_grab_product` VALUES ('33', '12342141asfdsafsaf', '1', '20', '20', '20', '0', '295', '1', '', '1520059521', '1520959521');
