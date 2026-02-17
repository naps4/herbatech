-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 14 Des 2025 pada 11.37
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cpb_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint UNSIGNED NOT NULL,
  `log_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `causer_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'default', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-12-12 20:33:06', '2025-12-12 20:33:06'),
(2, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 2, '[]', NULL, '2025-12-12 20:33:32', '2025-12-12 20:33:32'),
(3, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 3, '[]', NULL, '2025-12-14 03:14:28', '2025-12-14 03:14:28'),
(4, 'default', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 3, '[]', NULL, '2025-12-14 03:36:51', '2025-12-14 03:36:51'),
(5, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-12-14 03:37:10', '2025-12-14 03:37:10'),
(6, 'default', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-12-14 03:46:43', '2025-12-14 03:46:43'),
(7, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 3, '[]', NULL, '2025-12-14 03:46:55', '2025-12-14 03:46:55'),
(8, 'default', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 3, '[]', NULL, '2025-12-14 04:12:14', '2025-12-14 04:12:14'),
(9, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 2, '[]', NULL, '2025-12-14 04:12:43', '2025-12-14 04:12:43'),
(10, 'default', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 2, '[]', NULL, '2025-12-14 04:18:14', '2025-12-14 04:18:14'),
(11, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-12-14 04:18:32', '2025-12-14 04:18:32'),
(12, 'default', 'Created new user account', 'App\\Models\\User', NULL, 8, 'App\\Models\\User', 1, '[]', NULL, '2025-12-14 04:20:04', '2025-12-14 04:20:04'),
(13, 'default', 'User logged out', NULL, NULL, NULL, 'App\\Models\\User', 8, '[]', NULL, '2025-12-14 04:20:44', '2025-12-14 04:20:44'),
(14, 'default', 'User logged in', NULL, NULL, NULL, 'App\\Models\\User', 1, '[]', NULL, '2025-12-14 04:21:27', '2025-12-14 04:21:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cpbs`
--

CREATE TABLE `cpbs` (
  `id` bigint UNSIGNED NOT NULL,
  `batch_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('pengolahan','pengemasan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule_duration` int NOT NULL COMMENT 'Duration in hours',
  `status` enum('rnd','qa','ppic','wh','produksi','qc','qa_final','released') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rnd',
  `entered_current_status_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `duration_in_current_status` int NOT NULL DEFAULT '0',
  `is_overdue` tinyint(1) NOT NULL DEFAULT '0',
  `overdue_since` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `current_department_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cpbs`
--

INSERT INTO `cpbs` (`id`, `batch_number`, `type`, `product_name`, `schedule_duration`, `status`, `entered_current_status_at`, `duration_in_current_status`, `is_overdue`, `overdue_since`, `created_by`, `current_department_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'CPB-2024-001', 'pengolahan', 'Produk A', 48, 'wh', '2025-12-12 20:19:45', 0, 0, NULL, 2, 5, '2025-12-12 18:39:11', '2025-12-12 20:19:45', NULL),
(2, 'CPB-2024-002', 'pengemasan', 'Produk B', 24, 'ppic', '2025-12-12 15:39:11', 0, 0, NULL, 2, 4, '2025-12-12 18:39:11', '2025-12-12 18:39:11', NULL),
(3, 'CPB-2024-003', 'pengolahan', 'Produk C', 72, 'wh', '2025-12-10 18:39:11', 0, 1, '2025-12-11 18:39:11', 2, 5, '2025-12-12 18:39:11', '2025-12-12 18:39:11', NULL),
(4, 'CPB-2024-004', 'pengemasan', 'Produk D', 36, 'produksi', '2025-12-12 06:39:11', 0, 0, NULL, 2, 6, '2025-12-12 18:39:11', '2025-12-12 18:39:11', NULL),
(5, 'CPB-2024-005', 'pengolahan', 'Produk E', 96, 'qc', '2025-12-12 16:39:11', 0, 0, NULL, 2, 7, '2025-12-12 18:39:11', '2025-12-12 18:39:11', NULL),
(6, 'CPB-2024-006', 'pengemasan', 'Produk F', 24, 'qa_final', '2025-12-11 22:39:11', 0, 0, NULL, 2, 3, '2025-12-12 18:39:11', '2025-12-12 18:39:11', NULL),
(7, 'CPB-2023-100', 'pengolahan', 'Produk X', 48, 'released', '2025-12-02 18:39:11', 0, 0, NULL, 2, 3, '2025-12-12 18:39:11', '2025-12-12 18:39:11', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `handover_logs`
--

CREATE TABLE `handover_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `cpb_id` bigint UNSIGNED NOT NULL,
  `from_status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `handed_by` bigint UNSIGNED NOT NULL,
  `received_by` bigint UNSIGNED DEFAULT NULL,
  `handed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `received_at` timestamp NULL DEFAULT NULL,
  `duration_in_hours` int DEFAULT NULL COMMENT 'Duration at previous location',
  `was_overdue` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `handover_logs`
--

INSERT INTO `handover_logs` (`id`, `cpb_id`, `from_status`, `to_status`, `handed_by`, `received_by`, `handed_at`, `received_at`, `duration_in_hours`, `was_overdue`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'qa', 'ppic', 1, 4, '2025-12-12 20:19:17', '2025-12-12 20:19:32', 0, 0, 'ay', '2025-12-12 20:19:17', '2025-12-12 20:19:32'),
(2, 1, 'ppic', 'wh', 1, 5, '2025-12-12 20:19:45', '2025-12-12 20:19:46', 0, 0, 'ay', '2025-12-12 20:19:45', '2025-12-12 20:19:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_01_01_000002_create_cpbs_table', 1),
(6, '2024_01_01_000003_create_handover_logs_table', 1),
(7, '2024_01_01_000004_create_notifications_table', 1),
(8, '2024_01_01_000006_rename_cpbs_table', 1),
(9, '2025_12_13_033124_create_activity_log_table', 2),
(10, '2025_12_13_033125_add_event_column_to_activity_log_table', 2),
(11, '2025_12_13_033126_add_batch_uuid_column_to_activity_log_table', 2);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpb_id` bigint UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `message`, `cpb_id`, `is_read`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'handover_received', 'Anda menerima CPB: CPB-2024-001 dari Super Admin', 1, 0, '{\"from_user\": \"Super Admin\", \"handover_time\": \"2025-12-13T03:19:32.115998Z\"}', NULL, '2025-12-12 20:19:32', '2025-12-12 20:19:32'),
(2, 5, 'handover_received', 'Anda menerima CPB: CPB-2024-001 dari Super Admin', 1, 0, '{\"from_user\": \"Super Admin\", \"handover_time\": \"2025-12-13T03:19:45.951256Z\"}', NULL, '2025-12-12 20:19:45', '2025-12-12 20:19:45');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superadmin','rnd','qa','ppic','wh','produksi','qc') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rnd',
  `department` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `department`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@cpb.com', NULL, '$2y$12$PNqXjtFLqNzfUlAocsh.EOuol7gUb41knMtuTTznTfbPQGnYCtd2K', 'superadmin', 'Administration', NULL, '2025-12-12 18:39:09', '2025-12-12 18:39:09'),
(2, 'RND Manager', 'rnd@cpb.com', NULL, '$2y$12$0bfssMIl667M/ZTLlyDwo.mGawmzHq./3OZcsyjnn37juJoT6wqHq', 'rnd', 'Research & Development', NULL, '2025-12-12 18:39:10', '2025-12-12 18:39:10'),
(3, 'QA Manager', 'qa@cpb.com', NULL, '$2y$12$kOCYZBiwuxUHH.oQvgsIYu9b.GF7JDJsA1pgH00MFUpKmMkffr2p.', 'qa', 'Quality Assurance', NULL, '2025-12-12 18:39:10', '2025-12-12 18:39:10'),
(4, 'PPIC Officer', 'ppic@cpb.com', NULL, '$2y$12$jfcqJGgxTVSv.jLi34XHF.6WKoq2njkwX0N2kbS/yKXH3dHaTqBHi', 'ppic', 'PPIC', NULL, '2025-12-12 18:39:10', '2025-12-12 18:39:10'),
(5, 'Warehouse Staff', 'warehouse@cpb.com', NULL, '$2y$12$x..cmZ6789ztjakF2fsNF.GQioSzpiBV2ZlsOA6IqWYCQI/Mfka4q', 'wh', 'Warehouse', NULL, '2025-12-12 18:39:10', '2025-12-12 18:39:10'),
(6, 'Production Head', 'production@cpb.com', NULL, '$2y$12$ru.qfrCNcyflF5WX7hujeeoxwsAndO8Cv.nwTEbktDqUOrDrxgal6', 'produksi', 'Production', NULL, '2025-12-12 18:39:11', '2025-12-12 18:39:11'),
(7, 'QC Inspector', 'qc@cpb.com', NULL, '$2y$12$TAYUY7WYrZH8C64Y1VJsn.jN5wZk0jMsL4JGHE8WcfPU0n64ifUF6', 'qc', 'Quality Control', NULL, '2025-12-12 18:39:11', '2025-12-12 18:39:11'),
(8, 'Kiki Amalia', 'ppic@cpb.hbt', NULL, '$2y$12$.Jh/dOfcQvveFH5ENBgxb.ZWv60aM9BmmSSOXH2CsbDesCIV14Q7e', 'ppic', 'PPIC', NULL, '2025-12-14 04:20:04', '2025-12-14 04:20:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indeks untuk tabel `cpbs`
--
ALTER TABLE `cpbs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpbs_batch_number_unique` (`batch_number`),
  ADD KEY `cpbs_created_by_foreign` (`created_by`),
  ADD KEY `cpbs_current_department_id_foreign` (`current_department_id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `handover_logs`
--
ALTER TABLE `handover_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `handover_logs_handed_by_foreign` (`handed_by`),
  ADD KEY `handover_logs_received_by_foreign` (`received_by`),
  ADD KEY `handover_logs_cpb_id_created_at_index` (`cpb_id`,`created_at`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_cpb_id_foreign` (`cpb_id`),
  ADD KEY `notifications_user_id_is_read_created_at_index` (`user_id`,`is_read`,`created_at`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `cpbs`
--
ALTER TABLE `cpbs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `handover_logs`
--
ALTER TABLE `handover_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cpbs`
--
ALTER TABLE `cpbs`
  ADD CONSTRAINT `cpbs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cpbs_current_department_id_foreign` FOREIGN KEY (`current_department_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `handover_logs`
--
ALTER TABLE `handover_logs`
  ADD CONSTRAINT `handover_logs_cpb_id_foreign` FOREIGN KEY (`cpb_id`) REFERENCES `cpbs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `handover_logs_handed_by_foreign` FOREIGN KEY (`handed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `handover_logs_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_cpb_id_foreign` FOREIGN KEY (`cpb_id`) REFERENCES `cpbs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
