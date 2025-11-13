-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 13, 2025 lúc 09:17 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `ttmanga_v21`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_bookmarks`
--

CREATE TABLE `core_bookmarks` (
  `user_id` int(250) NOT NULL,
  `manga_id` int(250) NOT NULL,
  `is_read` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_chapters`
--

CREATE TABLE `core_chapters` (
  `id` int(250) NOT NULL,
  `manga_id` int(250) NOT NULL,
  `user_upload` int(255) NOT NULL,
  `index` int(250) NOT NULL,
  `name` varchar(500) NOT NULL,
  `image` text NOT NULL,
  `download` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_comments`
--

CREATE TABLE `core_comments` (
  `id` int(250) NOT NULL,
  `refid` int(250) NOT NULL,
  `manga_id` int(250) NOT NULL,
  `chapter_id` int(250) NOT NULL,
  `user_id` int(250) NOT NULL,
  `text` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_config_upload`
--

CREATE TABLE `core_config_upload` (
  `id` int(250) NOT NULL,
  `name` varchar(500) NOT NULL,
  `cookie` text NOT NULL,
  `album_id` int(250) NOT NULL,
  `note` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_genres`
--

CREATE TABLE `core_genres` (
  `id` int(250) NOT NULL,
  `name` varchar(128) NOT NULL,
  `text` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_history`
--

CREATE TABLE `core_history` (
  `id` int(250) NOT NULL,
  `user_id` int(250) NOT NULL,
  `data` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_mangas`
--

CREATE TABLE `core_mangas` (
  `id` int(250) NOT NULL,
  `id_last_chapter` int(250) NOT NULL,
  `user_upload` int(250) NOT NULL,
  `team_ids` text NOT NULL,
  `team_name_ids` text NOT NULL,
  `name_other_ids` text NOT NULL,
  `auth_ids` text NOT NULL,
  `genres_id` text NOT NULL,
  `name` varchar(500) NOT NULL,
  `image` varchar(500) NOT NULL,
  `cover` varchar(500) NOT NULL,
  `status` int(1) NOT NULL,
  `text` text NOT NULL,
  `links` text NOT NULL,
  `view` int(250) NOT NULL DEFAULT 0,
  `follow` int(250) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `is_trash` tinyint(1) NOT NULL,
  `trash_by` int(250) NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_manga_authors`
--

CREATE TABLE `core_manga_authors` (
  `id` int(250) NOT NULL,
  `name` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_manga_other_names`
--

CREATE TABLE `core_manga_other_names` (
  `id` int(250) NOT NULL,
  `name` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_manga_team_names`
--

CREATE TABLE `core_manga_team_names` (
  `id` int(250) NOT NULL,
  `name` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_messenger`
--

CREATE TABLE `core_messenger` (
  `id` int(250) NOT NULL,
  `refid` int(250) NOT NULL,
  `from_user_id` int(250) NOT NULL,
  `to_user_id` int(250) NOT NULL,
  `is_delete` varchar(50) NOT NULL,
  `is_spam_from` varchar(50) NOT NULL,
  `is_spam_to` varchar(50) NOT NULL,
  `time` int(50) NOT NULL,
  `text` text NOT NULL,
  `seen` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_notification`
--

CREATE TABLE `core_notification` (
  `id` int(250) NOT NULL,
  `user_id` int(250) NOT NULL,
  `from_user_id` int(250) NOT NULL,
  `type` int(2) NOT NULL,
  `seen` int(1) NOT NULL DEFAULT 0,
  `data` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_register_key`
--

CREATE TABLE `core_register_key` (
  `id` int(250) NOT NULL,
  `key` varchar(500) NOT NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  `quantity` int(4) NOT NULL,
  `creator_id` int(250) NOT NULL,
  `note` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_register_key_user_register`
--

CREATE TABLE `core_register_key_user_register` (
  `register_key_id` int(250) NOT NULL,
  `user_id` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_reports`
--

CREATE TABLE `core_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(250) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `manga_id` int(250) DEFAULT NULL,
  `chapter_id` int(250) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  `user_update` int(250) NOT NULL,
  `created_at` varchar(250) NOT NULL,
  `updated_at` varchar(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_request_join_team`
--

CREATE TABLE `core_request_join_team` (
  `id` int(250) NOT NULL,
  `user_id` int(250) NOT NULL,
  `team_id` int(250) NOT NULL,
  `note` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_roles`
--

CREATE TABLE `core_roles` (
  `id` int(250) NOT NULL,
  `name` varchar(255) NOT NULL,
  `perms` text NOT NULL,
  `color` varchar(20) NOT NULL,
  `level` int(4) NOT NULL,
  `is_default` int(1) NOT NULL DEFAULT 0,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_smileys`
--

CREATE TABLE `core_smileys` (
  `id` int(250) NOT NULL,
  `type` int(2) NOT NULL,
  `user_id` int(250) NOT NULL,
  `name` varchar(500) NOT NULL,
  `images` text NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_teams`
--

CREATE TABLE `core_teams` (
  `id` int(250) NOT NULL,
  `own_id` int(250) NOT NULL,
  `name` varchar(500) NOT NULL,
  `desc` text NOT NULL,
  `avatar` text NOT NULL,
  `cover` text NOT NULL,
  `facebook` varchar(500) NOT NULL,
  `active` int(1) NOT NULL DEFAULT 0,
  `config_id` int(250) NOT NULL,
  `note` text NOT NULL,
  `rule` text NOT NULL,
  `user_ban` int(250) NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_team_mangas`
--

CREATE TABLE `core_team_mangas` (
  `team_id` int(250) NOT NULL,
  `manga_id` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `core_users`
--

CREATE TABLE `core_users` (
  `id` int(250) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `name` varchar(75) NOT NULL,
  `sex` varchar(1) NOT NULL,
  `date_of_birth` varchar(11) NOT NULL,
  `avatar` text NOT NULL,
  `cover` text NOT NULL,
  `bio` text NOT NULL,
  `facebook` varchar(250) NOT NULL,
  `role_id` int(250) NOT NULL,
  `perms` text NOT NULL,
  `settings` text NOT NULL,
  `rep` int(50) NOT NULL,
  `team_id` int(250) NOT NULL,
  `forgot_key` varchar(150) NOT NULL,
  `forgot_time` int(11) NOT NULL DEFAULT 0,
  `auth_session` text NOT NULL,
  `limit_device` varchar(50) NOT NULL DEFAULT '0',
  `user_ban` int(250) NOT NULL,
  `adm` int(10) NOT NULL,
  `created_at` int(250) NOT NULL,
  `updated_at` int(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `core_bookmarks`
--
ALTER TABLE `core_bookmarks`
  ADD PRIMARY KEY (`user_id`,`manga_id`);

--
-- Chỉ mục cho bảng `core_chapters`
--
ALTER TABLE `core_chapters`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_comments`
--
ALTER TABLE `core_comments`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_config_upload`
--
ALTER TABLE `core_config_upload`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_genres`
--
ALTER TABLE `core_genres`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_history`
--
ALTER TABLE `core_history`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_mangas`
--
ALTER TABLE `core_mangas`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_manga_authors`
--
ALTER TABLE `core_manga_authors`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_manga_other_names`
--
ALTER TABLE `core_manga_other_names`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_manga_team_names`
--
ALTER TABLE `core_manga_team_names`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_messenger`
--
ALTER TABLE `core_messenger`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_notification`
--
ALTER TABLE `core_notification`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_register_key`
--
ALTER TABLE `core_register_key`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_register_key_user_register`
--
ALTER TABLE `core_register_key_user_register`
  ADD PRIMARY KEY (`register_key_id`,`user_id`);

--
-- Chỉ mục cho bảng `core_reports`
--
ALTER TABLE `core_reports`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_request_join_team`
--
ALTER TABLE `core_request_join_team`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_roles`
--
ALTER TABLE `core_roles`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_smileys`
--
ALTER TABLE `core_smileys`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_teams`
--
ALTER TABLE `core_teams`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `core_team_mangas`
--
ALTER TABLE `core_team_mangas`
  ADD PRIMARY KEY (`team_id`,`manga_id`);

--
-- Chỉ mục cho bảng `core_users`
--
ALTER TABLE `core_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `core_chapters`
--
ALTER TABLE `core_chapters`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_comments`
--
ALTER TABLE `core_comments`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_config_upload`
--
ALTER TABLE `core_config_upload`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_genres`
--
ALTER TABLE `core_genres`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_history`
--
ALTER TABLE `core_history`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_mangas`
--
ALTER TABLE `core_mangas`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_manga_authors`
--
ALTER TABLE `core_manga_authors`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_manga_other_names`
--
ALTER TABLE `core_manga_other_names`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_manga_team_names`
--
ALTER TABLE `core_manga_team_names`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_messenger`
--
ALTER TABLE `core_messenger`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_notification`
--
ALTER TABLE `core_notification`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_register_key`
--
ALTER TABLE `core_register_key`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_reports`
--
ALTER TABLE `core_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_request_join_team`
--
ALTER TABLE `core_request_join_team`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_roles`
--
ALTER TABLE `core_roles`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_smileys`
--
ALTER TABLE `core_smileys`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_teams`
--
ALTER TABLE `core_teams`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `core_users`
--
ALTER TABLE `core_users`
  MODIFY `id` int(250) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
