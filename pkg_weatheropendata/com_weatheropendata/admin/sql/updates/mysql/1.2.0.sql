-- Products
-- Migrate table.
RENAME TABLE `#__weatheropendata_products` TO `#__weatheropendata_products_old`;
CREATE TABLE `#__weatheropendata_products` (
    `id` int(11) NOT NULL auto_increment,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `protocol` VARCHAR(10) NOT NULL DEFAULT '',
    `file` VARCHAR(255) NOT NULL DEFAULT '',
    `product` VARCHAR(20) NOT NULL DEFAULT '',
    `cache_minutes` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY  (`id`),
    INDEX `idx_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
INSERT INTO `#__weatheropendata_products` (`name`, `protocol`, `file`, `product`, `cache_minutes`)
SELECT `name`, `protocol`, `file`, `product`, `cache_minutes`
FROM `#__weatheropendata_products_old`;
DROP TABLE `#__weatheropendata_products_old`;

-- Charts
CREATE TABLE IF NOT EXISTS `#__weatheropendata_charts` (
  `id` int(11) NOT NULL auto_increment,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `file` VARCHAR(255) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `template` TEXT NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX `idx_name` (`name`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Try copy data from old table.
-- Create the old table, if not exists to suppress error messages.
CREATE TABLE IF NOT EXISTS `#__weatherchart_templates` (
    `id` int(11) NOT NULL auto_increment,
    `name` VARCHAR(255) NOT NULL DEFAULT '',
    `file` VARCHAR(255) NOT NULL DEFAULT '',
    `timestamp` int(11) NOT NULL DEFAULT '0',
    `template` TEXT NOT NULL,
    PRIMARY KEY  (`id`),
    INDEX `idx_name` (`name`)
    )  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__weatheropendata_charts` (`name`, `file`, `timestamp`, `template`)
SELECT `name`, `file`, `timestamp`, `template`
FROM `#__weatherchart_templates`;

-- Insert default data, if table is still empty.
INSERT INTO `#__weatheropendata_charts` (`name`, `file`, `timestamp`, `template`)
SELECT 'RueckblickVorhersage_OBS_FC', 'rueckvor_obs_fc', 1524659556, '@SAMPLE_CHART@'
WHERE NOT EXISTS (SELECT * FROM `joomla_weatheropendata_charts`)
