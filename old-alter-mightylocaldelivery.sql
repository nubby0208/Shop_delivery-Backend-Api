/* 20-07-22 */
/*
ALTER TABLE `app_settings` ADD `otp_verify_on_pickup_delivery` TINYINT NULL DEFAULT '1' AFTER `distance`,
                           ADD `currency` VARCHAR(255) NULL DEFAULT NULL AFTER `otp_verify_on_pickup_delivery`,
                           ADD `currency_code` VARCHAR(255) NULL DEFAULT NULL AFTER `currency`,
                           ADD `currency_position` VARCHAR(255) NULL DEFAULT NULL AFTER `currency_code`;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (NULL, '2022_06_27_131039_add_otp_verify_on_pickup_delivery', '5');
*/
/* 23-06-22 */
/* noting to import */
/* 11-06-20222 */
/*
ALTER TABLE `users` ADD `fcm_token` TEXT NULL DEFAULT NULL AFTER `uid`; 

ALTER TABLE `orders` ADD `auto_assign` TINYINT NULL DEFAULT NULL AFTER `total_parcel`,
                     ADD `cancelled_delivery_man_ids` TEXT NULL DEFAULT NULL AFTER `auto_assign`; 

ALTER TABLE `app_settings` ADD `auto_assign` TINYINT NULL DEFAULT '0' AFTER `notification_settings`,
                           ADD `distance_unit` VARCHAR(255) NULL DEFAULT NULL COMMENT 'km, mile' AFTER `auto_assign`,
                           ADD `distance` DOUBLE NULL DEFAULT '0' AFTER `distance_unit`;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (NULL, '2022_05_30_063501_add_fcm_token_to_users_table', '4'), (NULL, '2022_05_31_101332_add_auto_assign_to_orders', '4'), (NULL, '2022_06_02_065520_add_distance_to_app_settings', '4');
*/
/* 23-05-20222*/
/*
ALTER TABLE `orders` ADD `total_parcel` DOUBLE NULL DEFAULT NULL AFTER `pickup_confirm_by_delivery_man`; 

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (NULL, '2022_05_11_080007_add_total_parcel_orders_table', '3');
*/


/* 20-04-20222*/
/*Table structure for table `documents` */
/*
DROP TABLE IF EXISTS `documents`;

CREATE TABLE `documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `is_required` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/
/*Data for the table `documents` */

/*Table structure for table `delivery_man_documents` */
/*
DROP TABLE IF EXISTS `delivery_man_documents`;

CREATE TABLE `delivery_man_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `delivery_man_id` bigint unsigned DEFAULT NULL,
  `document_id` bigint unsigned DEFAULT NULL,
  `is_verified` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_man_documents_document_id_foreign` (`document_id`),
  KEY `delivery_man_documents_delivery_man_id_foreign` (`delivery_man_id`),
  CONSTRAINT `delivery_man_documents_delivery_man_id_foreign` FOREIGN KEY (`delivery_man_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delivery_man_documents_document_id_foreign` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/
/*Data for the table `delivery_man_documents` */
/*
insert  into `migrations`(`id`,`migration`,`batch`) values (18,'2022_04_14_084202_create_documents_table',2),(19,'2022_04_14_084351_create_delivery_man_documents_table',2);
*/