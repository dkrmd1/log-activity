ALTER TABLE `server_checks`
  ADD COLUMN `rejected_by` bigint unsigned NULL AFTER `signed_at`,
  ADD COLUMN `rejected_name` varchar(255) NULL AFTER `rejected_by`,
  ADD COLUMN `reject_note` text NULL AFTER `rejected_name`,
  ADD COLUMN `rejected_at` timestamp NULL AFTER `reject_note`,
  ADD CONSTRAINT `server_checks_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_01_01_000018_add_rejection_to_server_checks_table', MAX(`batch`) + 1 FROM `migrations`;
