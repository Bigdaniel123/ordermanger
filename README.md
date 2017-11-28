
#线上
域名：xyd.yongshangju.com
FTP:47.96.180.17/xyd_yongshangju_com/xyd_yongshangju_com1988
数据库：47.96.180.17/xyd/xyd1988

#本地：    
后台密码
admin admin123

# update sql

ALTER TABLE `daikuan`.`cmf_fangkuan` ADD COLUMN `imgs` varchar(500) NULL DEFAULT NULL COMMENT '多图片';
ALTER TABLE `daikuan`.`cmf_fangkuan` ADD COLUMN `time_int` int(5) NULL DEFAULT NULL COMMENT '如何按期,每期多少天';