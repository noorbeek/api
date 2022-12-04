CREATE TABLE `registry` (
	`property` CHAR(255) NOT NULL COLLATE 'utf8mb4_general_ci',
	`value` TEXT DEFAULT NULL,
	`datatype` CHAR(50) NOT NULL DEFAULT 'string' COLLATE 'utf8mb4_general_ci',
	PRIMARY KEY (`property`) USING BTREE,
	INDEX `property` (`property`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;
