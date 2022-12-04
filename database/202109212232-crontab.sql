CREATE TABLE `apiCronjobs` (
	`task` CHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`schedule` CHAR(50) NOT NULL DEFAULT '0 * * * *' COLLATE 'utf8mb4_general_ci',
	`enabled` TINYINT(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`task`) USING BTREE,
	INDEX `task` (`task`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
