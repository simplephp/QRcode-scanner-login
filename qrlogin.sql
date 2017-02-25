/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : demo

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-02-23 00:43:37
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for qrlogin
-- ----------------------------
DROP TABLE IF EXISTS `qrlogin`;
CREATE TABLE `qrlogin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '生成ID',
  `uname` varchar(120) DEFAULT '' COMMENT '用户名',
  `token` varchar(120) NOT NULL COMMENT '二维码唯一标识',
  PRIMARY KEY (`id`,`token`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of qrlogin
-- ----------------------------
INSERT INTO `qrlogin` VALUES ('3', 'kevin', '664AD32B-5F53-CC2A-A0EB-8D545AC3E4C4');
INSERT INTO `qrlogin` VALUES ('4', '', '99381A6C-1113-6315-D288-F935342310D1');
INSERT INTO `qrlogin` VALUES ('5', '', '152811A0-D834-B310-D74C-2DFDA97C779A');
INSERT INTO `qrlogin` VALUES ('6', '', '0017B8A9-C93A-F381-00BC-344661D98E68');
INSERT INTO `qrlogin` VALUES ('7', '', '998FCC3E-2702-5A9A-A06D-3B2068E13288');
INSERT INTO `qrlogin` VALUES ('8', '', 'BF5B0DBF-8704-6F58-D473-14B7EC6B0D7A');
INSERT INTO `qrlogin` VALUES ('9', '', '8AA475F6-AFB9-4910-D47C-7EFBEA5D5696');
INSERT INTO `qrlogin` VALUES ('10', '', '46C4C9A8-B14E-AEDC-CC4B-ABE86DB9CE04');
INSERT INTO `qrlogin` VALUES ('11', '', 'DDBC8A16-96E0-E14F-7D19-A4D905CAC8AA');
INSERT INTO `qrlogin` VALUES ('12', '', 'B1F316A9-EB36-6FF6-3FE2-4089B3F740A0');
INSERT INTO `qrlogin` VALUES ('13', '', '9B4916A6-C0B6-D60D-E7E2-4771C53DFA3E');
INSERT INTO `qrlogin` VALUES ('14', '', '47859106-6FB9-0524-0A70-9B25C38BCD77');
INSERT INTO `qrlogin` VALUES ('15', 'kevin', 'F007FD81-186F-C24D-D4B9-12903454A515');
INSERT INTO `qrlogin` VALUES ('16', 'kevin', 'BC4EEC2D-F2C9-2261-946D-180EE2944E8E');
INSERT INTO `qrlogin` VALUES ('17', 'kevin', 'B54F56FC-2056-6CCA-A673-27AA6B089228');
INSERT INTO `qrlogin` VALUES ('18', 'kevin', '97B6FAB1-11BF-1142-9421-B0D375BCF25F');
INSERT INTO `qrlogin` VALUES ('19', 'kevin', '847C8C48-F675-CF88-C0ED-D540CC7FB972');
