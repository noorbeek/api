-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server versie:                10.5.9-MariaDB-1:10.5.9+maria~focal - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Versie:              11.2.0.6213
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Structuur van  tabel cc.activities wordt geschreven
CREATE TABLE IF NOT EXISTS `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT 'Nameless',
  `startAt` datetime DEFAULT NULL,
  `leadTime` float NOT NULL DEFAULT 0,
  `activityOf` int(11) DEFAULT NULL,
  `type` varchar(64) NOT NULL DEFAULT 'task',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `owners` text DEFAULT NULL,
  `rolegroups` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Dumpen data van tabel cc.activities: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;

-- Structuur van  tabel cc.contacts wordt geschreven
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization` int(11) NOT NULL DEFAULT 0,
  `location` int(11) NOT NULL DEFAULT 0,
  `nameFirst` varchar(64) NOT NULL DEFAULT '',
  `nameLast` varchar(128) NOT NULL DEFAULT '',
  `title` varchar(64) NOT NULL DEFAULT '',
  `jobTitle` varchar(128) NOT NULL DEFAULT '',
  `department` varchar(128) NOT NULL DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '',
  `mobile` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `subscriptions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumpen data van tabel cc.contacts: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;

-- Structuur van  tabel cc.files wordt geschreven
CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) NOT NULL DEFAULT '',
  `extension` varchar(32) NOT NULL DEFAULT '',
  `size` varchar(50) NOT NULL DEFAULT '0',
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `scope` varchar(64) DEFAULT NULL,
  `scopeId` varchar(16) DEFAULT NULL,
  `token` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumpen data van tabel cc.files: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
/*!40000 ALTER TABLE `files` ENABLE KEYS */;

-- Structuur van  tabel cc.locations wordt geschreven
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `organization` int(11) NOT NULL DEFAULT 0,
  `street` varchar(128) DEFAULT NULL,
  `houseNumber` varchar(16) DEFAULT NULL,
  `houseNumberExtension` varchar(16) DEFAULT NULL,
  `postalCode` varchar(16) DEFAULT NULL,
  `district` varchar(128) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `county` varchar(128) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Dumpen data van tabel cc.locations: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;

-- Structuur van  tabel cc.mailboxes wordt geschreven
CREATE TABLE IF NOT EXISTS `mailboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inType` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IMAP',
  `inHost` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inPort` smallint(6) NOT NULL DEFAULT 25,
  `inSsl` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inUsername` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inPassword` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outType` varchar(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SMTP',
  `outHost` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outPort` smallint(6) NOT NULL DEFAULT 25,
  `outSsl` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outPassword` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outUsername` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outSendFrom` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `Id` (`id`) USING BTREE,
  FULLTEXT KEY `Label` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- Dumpen data van tabel cc.mailboxes: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `mailboxes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mailboxes` ENABLE KEYS */;

-- Structuur van  tabel cc.notifications wordt geschreven
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scope` varchar(32) NOT NULL,
  `scopeId` int(11) NOT NULL DEFAULT 0,
  `from` varchar(32) NOT NULL DEFAULT '',
  `to` text NOT NULL,
  `cc` text NOT NULL,
  `bcc` text NOT NULL,
  `subject` varchar(256) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `body` mediumtext NOT NULL,
  `action` varchar(1024) NOT NULL DEFAULT '',
  `actionText` varchar(128) NOT NULL DEFAULT '',
  `attachments` text NOT NULL,
  `priority` tinyint(4) NOT NULL DEFAULT 0,
  `callback` text NOT NULL,
  `sendAt` datetime NOT NULL DEFAULT current_timestamp(),
  `mediums` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumpen data van tabel cc.notifications: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;

-- Structuur van  tabel cc.organizations wordt geschreven
CREATE TABLE IF NOT EXISTS `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL DEFAULT 'Nameless',
  `description` text DEFAULT NULL,
  `organizationOf` int(11) DEFAULT NULL,
  `customerOf` int(11) DEFAULT NULL,
  `accountManager` int(11) DEFAULT NULL,
  `supportManager` int(11) DEFAULT NULL,
  `technicalManager` int(11) DEFAULT NULL,
  `projectManager` int(11) DEFAULT NULL,
  `financialAccount` varchar(64) DEFAULT NULL,
  `street` varchar(128) DEFAULT NULL,
  `houseNumber` varchar(32) DEFAULT NULL,
  `houseNumberExtension` varchar(32) DEFAULT NULL,
  `zipCode` varchar(32) DEFAULT NULL,
  `city` varchar(128) DEFAULT NULL,
  `website` varchar(1024) DEFAULT NULL,
  `phoneNumber` varchar(32) DEFAULT NULL,
  `logo` mediumtext DEFAULT NULL,
  `color` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE,
  KEY `organizationOf` (`organizationOf`),
  KEY `customerOf` (`customerOf`),
  KEY `accountManager` (`accountManager`),
  KEY `supportManager` (`supportManager`),
  KEY `technicalManager` (`technicalManager`),
  KEY `projectManager` (`projectManager`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Dumpen data van tabel cc.organizations: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `organizations` DISABLE KEYS */;
/*!40000 ALTER TABLE `organizations` ENABLE KEYS */;

-- Structuur van  tabel cc.rolegroups wordt geschreven
CREATE TABLE IF NOT EXISTS `rolegroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT 'Nameless',
  `rolegroupOf` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumpen data van tabel cc.rolegroups: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `rolegroups` DISABLE KEYS */;
/*!40000 ALTER TABLE `rolegroups` ENABLE KEYS */;

-- Structuur van  tabel cc.scopes wordt geschreven
CREATE TABLE IF NOT EXISTS `scopes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rolegroup` int(11) NOT NULL DEFAULT 0,
  `scope` varchar(50) DEFAULT NULL,
  `mode` varchar(1) NOT NULL DEFAULT 'r',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Dumpen data van tabel cc.scopes: ~0 rows (ongeveer)
/*!40000 ALTER TABLE `scopes` DISABLE KEYS */;
/*!40000 ALTER TABLE `scopes` ENABLE KEYS */;

-- Structuur van  tabel cc.sessions wordt geschreven
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(64) NOT NULL DEFAULT '',
  `requests` int(11) NOT NULL DEFAULT 0,
  `startAt` datetime NOT NULL DEFAULT current_timestamp(),
  `validity` int(11) DEFAULT 60,
  `endAt` datetime DEFAULT NULL,
  `type` varchar(32) NOT NULL DEFAULT 'default',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- Structuur van  tabel cc.users wordt geschreven
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `email` varchar(128) NOT NULL DEFAULT '',
  `password` varchar(256) NOT NULL DEFAULT '',
  `pin` varchar(128) NOT NULL DEFAULT '',
  `api` varchar(256) NOT NULL DEFAULT '',
  `role` smallint(6) NOT NULL DEFAULT 999,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp(),
  `organization` int(11) DEFAULT NULL,
  `memberOf` text DEFAULT NULL,
  `ownerOf` text DEFAULT NULL,
  `verified` tinyint(4) NOT NULL DEFAULT 0,
  `blocked` tinyint(4) NOT NULL DEFAULT 0,
  `removed` tinyint(4) NOT NULL DEFAULT 0,
  `signature` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `removed` (`removed`),
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `email` (`email`),
  FULLTEXT KEY `memberOf` (`memberOf`),
  FULLTEXT KEY `ownerOf` (`ownerOf`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

-- Dumpen data van tabel cc.users: ~1 rows (ongeveer)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
REPLACE INTO `users` (`id`, `name`, `email`, `password`, `pin`, `api`, `role`, `createdAt`, `organization`, `memberOf`, `ownerOf`, `verified`, `blocked`, `removed`, `signature`) VALUES
	(1, 'Administrator', 'admin@cc.local', '$2y$10$PuDzAegI9pEiZg/enzSyh.SciOkOUatbuAPWfkqaEQ4vU94u1Qvoe', '', '', 100, '2021-09-07 11:26:47', NULL, NULL, NULL, 1, 0, 0, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;


CREATE TABLE `apiDatabaseMigrations` (
	`id` CHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`migratedAt` DATETIME NOT NULL DEFAULT current_timestamp(),
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `id` (`id`) USING BTREE
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
;


/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
