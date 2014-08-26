-- ----------------------------
-- Table structure for `sys_cron`
-- ----------------------------
DROP TABLE IF EXISTS `sys_cron`;
CREATE TABLE `sys_cron` (
  `cronId` int(11) NOT NULL AUTO_INCREMENT,
  `task` varchar(255) NOT NULL COMMENT '任务名',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不可用， 1可用',
  `mhdmd` varchar(255) NOT NULL COMMENT '音间',
  `command` varchar(255) NOT NULL COMMENT '命令',
  `runAt` int(11) NOT NULL DEFAULT '0' COMMENT '运行时间',
  `timeout` int(11) NOT NULL DEFAULT '0' COMMENT '超时警告时间设置',
  `logFile` varchar(255) NOT NULL DEFAULT '' COMMENT '超时之后回调脚本',
  PRIMARY KEY (`cronId`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- 初始数据
-- ----------------------------
/**
INSERT INTO `sys_cron` VALUES(1, 'kill pid (守护进程)', 1, '*/1 * * * *', 'php path/cmd.php pclose d', '', '', '');
**/