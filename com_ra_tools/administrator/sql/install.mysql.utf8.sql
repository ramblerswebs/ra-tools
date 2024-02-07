# 3 files in total
# 30/11/22 created
# 14/06/23 CB added ra_groups/details; lat/lon to decimal(14,12)
# 16/07/23 CB aras/title default ''
# 02/08/23 remove area / title
# 09/10/23 add Areas / cluster
# 22/01/24 sdd table clusters
-- Table structure for table `#__ra_areas`
--
CREATE TABLE IF NOT EXISTS `#__ra_areas` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `nation_id` int NOT NULL DEFAULT '1',
    `code` VARCHAR(2) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `details` mediumtext NOT NULL,
    `website` VARCHAR(150) NOT NULL,
    `co_url` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `cluster` VARCHAR(3) NULL,
    `chair_id` INT NULL,
    `latitude` decimal(14,12) NOT NULL,
    `longitude` decimal(15,13) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
# ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__ra_clusters` (
    `code` VARCHAR(3) NOT NULL,
    `name` VARCHAR(20) NOT NULL,
    `contact_id` INT NULL,
PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
# ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `#__ra_groups` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `area_id` int NOT NULL DEFAULT '1',
  `code` VARCHAR(4) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `details` mediumtext NOT NULL,
  `group_type` VARCHAR(1) NOT NULL DEFAULT 'G',
  `website` VARCHAR(150) NOT NULL,
  `co_url` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `latitude` decimal(14,12) NOT NULL,
  `longitude` decimal(15,13) NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
# ------------------------------------------------------------------------------
DROP TABLE IF EXISTS `#__ra_nations`;
CREATE TABLE `#__ra_nations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(2) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


INSERT INTO `#__ra_nations` ( `code`, `name`) VALUES
('EN', 'England'),
('SC', 'Scotland'),
('WA', 'Wales');

#ALTER TABLE `#__ra_nations`
#  ADD PRIMARY KEY (`id`);

#ALTER TABLE `#__ra_nations`
#  MODIFY `id` int NOT NULL , AUTO_INCREMENT=4;
# ------------------------------------------------------------------------------
