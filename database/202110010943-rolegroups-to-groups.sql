RENAME TABLE `rolegroups` TO `groups`;
ALTER TABLE `groups` RENAME COLUMN `rolegroupOf` TO `groupOf`;
ALTER TABLE `scopes` RENAME COLUMN `rolegroup` TO `group`;
