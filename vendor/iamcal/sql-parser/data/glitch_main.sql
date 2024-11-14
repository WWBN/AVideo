-- MySQL dump 10.13  Distrib 5.5.40-36.1, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ts_main
-- ------------------------------------------------------
-- Server version	5.5.40-36.1-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `achievements_counts`
--

DROP TABLE IF EXISTS `achievements_counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements_counts` (
  `achievement_id` int(10) unsigned NOT NULL,
  `num_players` int(10) unsigned NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_counts_by_day`
--

DROP TABLE IF EXISTS `achievements_counts_by_day`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements_counts_by_day` (
  `day` date NOT NULL,
  `total` int(10) unsigned NOT NULL DEFAULT '0',
  `share_worthy` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `achievements_share`
--

DROP TABLE IF EXISTS `achievements_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements_share` (
  `share_code` varchar(6) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `service` char(1) NOT NULL,
  `share_date` int(10) unsigned NOT NULL,
  `share_day` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `quickstart` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`share_code`,`service`),
  KEY `share_day` (`share_day`,`service`,`clicks`),
  KEY `player_tsid` (`player_tsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `animal_renames`
--

DROP TABLE IF EXISTS `animal_renames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `animal_renames` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_rename` int(10) unsigned NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `item_tsid` varchar(255) NOT NULL,
  `item_class_tsid` varchar(255) NOT NULL,
  `name_from` varchar(255) NOT NULL,
  `name_to` varchar(255) NOT NULL,
  `is_reviewed` tinyint(3) unsigned NOT NULL,
  `is_bad` tinyint(3) unsigned NOT NULL,
  `date_reviewed` int(10) unsigned NOT NULL,
  `reviewed_by` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_reviewed` (`is_reviewed`,`date_rename`),
  KEY `player_tsid` (`player_tsid`,`date_rename`),
  KEY `is_reviewed_3` (`is_reviewed`,`date_reviewed`,`reviewed_by`),
  KEY `date_rename` (`date_rename`)
) ENGINE=InnoDB AUTO_INCREMENT=1271071 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_auth_reqs`
--

DROP TABLE IF EXISTS `api_auth_reqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_auth_reqs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `checksum` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  `response_type` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `date_authed` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`),
  KEY `date_authed` (`date_authed`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_data_blob`
--

DROP TABLE IF EXISTS `api_data_blob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_data_blob` (
  `id` bigint(20) unsigned NOT NULL,
  `secret` varchar(10) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `key_id` int(10) unsigned NOT NULL,
  `added_user_id` int(10) unsigned NOT NULL,
  `added_player_id` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_data_lists`
--

DROP TABLE IF EXISTS `api_data_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_data_lists` (
  `id` bigint(20) unsigned NOT NULL,
  `key_id` int(10) unsigned NOT NULL,
  `list_id` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `list_id` (`list_id`,`date_create`),
  KEY `list_id_2` (`list_id`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_key_tokens`
--

DROP TABLE IF EXISTS `api_key_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_key_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `checksum` varchar(255) NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `scope` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`player_id`,`client_id`,`scope`)
) ENGINE=InnoDB AUTO_INCREMENT=69946 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_keys`
--

DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_keys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `checksum` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `redirect_uri` varchar(255) NOT NULL,
  `allow_code` tinyint(3) unsigned NOT NULL,
  `allow_token` tinyint(3) unsigned NOT NULL,
  `allow_password` tinyint(3) unsigned NOT NULL,
  `allow_scope_identity` tinyint(3) unsigned NOT NULL,
  `allow_scope_read` tinyint(3) unsigned NOT NULL,
  `allow_scope_write` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `icon_ts` int(10) unsigned NOT NULL,
  `push_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=487 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auctions`
--

DROP TABLE IF EXISTS `auctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auctions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_tsid` varchar(255) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_expires` int(10) unsigned NOT NULL,
  `date_sold` int(10) unsigned NOT NULL,
  `class_tsid` varchar(255) NOT NULL,
  `stack_tsid` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `count` int(10) unsigned NOT NULL,
  `tool_state` varchar(15) NOT NULL,
  `tool_uses` int(10) unsigned NOT NULL,
  `tool_capacity` int(10) unsigned NOT NULL,
  `furniture_data` text NOT NULL,
  `cost` int(10) unsigned NOT NULL,
  `cost_per` float NOT NULL,
  `expired` tinyint(3) unsigned NOT NULL,
  `buyer_tsid` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_tsid` (`player_tsid`,`uid`),
  KEY `date_expires` (`date_expires`),
  KEY `player_tsid_2` (`player_tsid`,`expired`,`date_create`),
  KEY `category` (`category`,`expired`,`date_create`),
  KEY `class_tsid` (`class_tsid`,`expired`,`date_create`),
  KEY `class_tsid_2` (`class_tsid`,`expired`,`cost_per`),
  KEY `date_create` (`expired`,`date_create`),
  KEY `buyer_tsid` (`buyer_tsid`,`date_sold`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auctions_summaries`
--

DROP TABLE IF EXISTS `auctions_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auctions_summaries` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_plural` varchar(255) NOT NULL,
  `class_tsid` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `iconic_url` varchar(255) NOT NULL,
  `broken_iconic_url` varchar(255) NOT NULL,
  `display_wear` tinyint(3) unsigned NOT NULL,
  `count_items` int(10) unsigned NOT NULL,
  `count_auctions` int(10) unsigned NOT NULL,
  `best_cost_per` float unsigned NOT NULL,
  PRIMARY KEY (`id`,`class_tsid`),
  KEY `category` (`category`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avatars_defaults`
--

DROP TABLE IF EXISTS `avatars_defaults`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatars_defaults` (
  `code` varchar(255) NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `rendered` tinyint(3) unsigned NOT NULL,
  `date_singles` int(10) unsigned NOT NULL,
  `date_sheets` int(10) unsigned NOT NULL,
  PRIMARY KEY (`code`),
  KEY `rendered` (`rendered`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avatars_errors`
--

DROP TABLE IF EXISTS `avatars_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatars_errors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `date_started` int(10) unsigned NOT NULL,
  `queue_id` int(10) unsigned NOT NULL,
  `mode` varchar(255) NOT NULL,
  `step` varchar(255) NOT NULL,
  `error` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=782830 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avatars_queue`
--

DROP TABLE IF EXISTS `avatars_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatars_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `special` varchar(255) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `date_start_after` int(10) unsigned NOT NULL,
  `date_started` int(10) unsigned NOT NULL,
  `errors` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_started` (`date_started`,`date_create`),
  KEY `date_create` (`date_create`),
  KEY `special` (`special`),
  KEY `special_2` (`special`,`date_started`),
  KEY `mode` (`mode`,`special`,`date_started`),
  KEY `mode_2` (`mode`,`date_started`)
) ENGINE=InnoDB AUTO_INCREMENT=1270145 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avatars_rendered`
--

DROP TABLE IF EXISTS `avatars_rendered`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatars_rendered` (
  `checksum` varchar(255) NOT NULL,
  `date_singles` int(10) unsigned NOT NULL,
  `date_sheets` int(10) unsigned NOT NULL,
  `time_queue` int(10) unsigned NOT NULL,
  `time_singles` int(10) unsigned NOT NULL,
  `time_sheets` int(10) unsigned NOT NULL,
  `version` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`checksum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ballots`
--

DROP TABLE IF EXISTS `ballots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ballots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `url` varchar(50) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_start` int(10) unsigned NOT NULL,
  `date_ends` int(10) unsigned NOT NULL,
  `days` int(5) unsigned NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`),
  KEY `active` (`active`,`date_ends`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ballots_log`
--

DROP TABLE IF EXISTS `ballots_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ballots_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `ballot_id` int(10) unsigned NOT NULL,
  `option_id` int(10) unsigned NOT NULL,
  `votes` int(10) unsigned NOT NULL,
  `votes_remaining` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`ballot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3479 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ballots_options`
--

DROP TABLE IF EXISTS `ballots_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ballots_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ballot_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `votes` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `referendum_id` (`ballot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `beta_fbconnect`
--

DROP TABLE IF EXISTS `beta_fbconnect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beta_fbconnect` (
  `fb_uid` varchar(255) NOT NULL,
  `date_connect` int(10) unsigned NOT NULL,
  `signedup` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`fb_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `beta_impressions`
--

DROP TABLE IF EXISTS `beta_impressions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beta_impressions` (
  `bucket` varchar(255) NOT NULL,
  `num_users` int(10) unsigned NOT NULL,
  `num_hits` int(10) unsigned NOT NULL,
  PRIMARY KEY (`bucket`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `beta_invite_batches`
--

DROP TABLE IF EXISTS `beta_invite_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beta_invite_batches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `date_closed` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(15) NOT NULL,
  `opened_by` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(15) NOT NULL,
  `size` int(5) NOT NULL,
  `per_user` int(5) NOT NULL,
  `sent` int(5) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `beta_invites`
--

DROP TABLE IF EXISTS `beta_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beta_invites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_sent` int(10) unsigned NOT NULL,
  `date_clicked` int(10) unsigned NOT NULL,
  `date_signup` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `date_added` int(10) unsigned NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `added_method` varchar(255) NOT NULL,
  `have_launched` tinyint(3) unsigned NOT NULL,
  `locations_visited` int(10) unsigned NOT NULL,
  `dont_contact` int(10) unsigned NOT NULL,
  `date_last_client` int(10) unsigned NOT NULL,
  `date_deleted` int(10) unsigned NOT NULL,
  `no_reminders` tinyint(3) unsigned NOT NULL,
  `date_last_reminder` int(10) unsigned NOT NULL,
  `date_bounced` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `account_id` (`account_id`),
  KEY `code` (`code`),
  KEY `added_method` (`added_method`),
  KEY `date_signup` (`date_signup`)
) ENGINE=InnoDB AUTO_INCREMENT=74062 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `beta_signups`
--

DROP TABLE IF EXISTS `beta_signups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beta_signups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `survey_version` tinyint(4) NOT NULL,
  `bucket` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `fb_uid` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `did_survey` tinyint(3) unsigned NOT NULL,
  `name` varchar(2355) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `age` varchar(255) NOT NULL,
  `time` varchar(255) NOT NULL,
  `fbgame` varchar(255) NOT NULL,
  `ogame` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `devices` varchar(255) NOT NULL,
  `activities` varchar(255) NOT NULL,
  `beta` varchar(255) NOT NULL,
  `more` text NOT NULL,
  `send_invite` tinyint(3) unsigned NOT NULL,
  `ref_url` varchar(255) NOT NULL,
  `invite_by` varchar(255) NOT NULL,
  `invite_date_added` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `send_invite` (`send_invite`),
  KEY `bucket` (`bucket`)
) ENGINE=InnoDB AUTO_INCREMENT=73677 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blog_old_posts`
--

DROP TABLE IF EXISTS `blog_old_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_old_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_created` int(10) unsigned NOT NULL,
  `date_modified` int(10) unsigned NOT NULL,
  `date_published` int(10) unsigned NOT NULL,
  `is_published` tinyint(3) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `stub` varchar(64) NOT NULL,
  `author` varchar(32) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stub` (`stub`),
  KEY `date_created` (`date_created`),
  KEY `date_published` (`date_published`),
  KEY `is_published` (`is_published`,`date_published`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_performance_testing_results`
--

DROP TABLE IF EXISTS `client_performance_testing_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_performance_testing_results` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `session_id` int(10) unsigned NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `loc_tsid` varchar(20) NOT NULL,
  `renderer` varchar(20) NOT NULL,
  `fake_friends` tinyint(3) unsigned NOT NULL,
  `segment_stage_width` smallint(5) unsigned NOT NULL,
  `segment_stage_height` smallint(5) unsigned NOT NULL,
  `segment_vp_width` smallint(5) unsigned NOT NULL,
  `segment_vp_height` smallint(5) unsigned NOT NULL,
  `segment_name` varchar(255) NOT NULL,
  `segment_time` mediumint(8) unsigned NOT NULL,
  `segment_frames` smallint(5) unsigned NOT NULL,
  `segment_avg_fps` tinyint(3) unsigned NOT NULL,
  `segment_avg_mem` smallint(5) unsigned NOT NULL,
  `segment_mem_delta` smallint(5) unsigned NOT NULL,
  `dupe` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=170651 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_performance_testing_sessions`
--

DROP TABLE IF EXISTS `client_performance_testing_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_performance_testing_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `trial_name` varchar(20) NOT NULL,
  `os` varchar(255) NOT NULL,
  `flash_version` varchar(255) NOT NULL,
  `flash_major_version` tinyint(3) unsigned NOT NULL,
  `flash_minor_version` tinyint(3) unsigned NOT NULL,
  `gpu_available` tinyint(3) unsigned NOT NULL,
  `gpu_driver_info` varchar(255) NOT NULL,
  `gave_reward` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player_tsid` (`player_tsid`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=11056 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversions`
--

DROP TABLE IF EXISTS `conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversions` (
  `b_cookie` bigint(20) unsigned NOT NULL,
  `date_hit` int(10) unsigned NOT NULL,
  `ref_url` varchar(255) NOT NULL,
  `bucket` varchar(255) NOT NULL,
  `hit_signup` tinyint(3) unsigned NOT NULL,
  `done_signup` tinyint(3) unsigned NOT NULL,
  `used_fb` tinyint(3) unsigned NOT NULL,
  `date_signup` int(10) unsigned NOT NULL,
  `is_pre_tracking` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`b_cookie`),
  KEY `hit_signup` (`is_pre_tracking`,`hit_signup`,`done_signup`,`used_fb`),
  KEY `bucket` (`is_pre_tracking`,`bucket`,`hit_signup`,`done_signup`,`used_fb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `economy_furniture`
--

DROP TABLE IF EXISTS `economy_furniture`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economy_furniture` (
  `furniture_tsid` varchar(255) NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  `owner_tsid` varchar(255) NOT NULL,
  `location_tsid` varchar(255) NOT NULL,
  `location_type` varchar(255) NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `upgrade_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`furniture_tsid`),
  KEY `date_updated` (`date_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `economy_sales`
--

DROP TABLE IF EXISTS `economy_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economy_sales` (
  `id` bigint(20) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `seller_tsid` varchar(255) NOT NULL,
  `buyer_tsid` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `item_class_tsid` varchar(255) NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `total_price` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`),
  KEY `source` (`source`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `economy_sdbs`
--

DROP TABLE IF EXISTS `economy_sdbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `economy_sdbs` (
  `sdb_tsid` varchar(255) NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  `owner_tsid` varchar(255) NOT NULL,
  `location_tsid` varchar(255) NOT NULL,
  `location_type` varchar(255) NOT NULL,
  `item_class_tsid` varchar(255) NOT NULL,
  `qty` int(10) unsigned NOT NULL,
  `price_per_unit` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sdb_tsid`),
  KEY `date_updated` (`date_updated`),
  KEY `location_type` (`location_type`,`item_class_tsid`,`price_per_unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_bounces`
--

DROP TABLE IF EXISTS `email_bounces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_bounces` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `date_sent` int(10) unsigned NOT NULL,
  `date_bounced` int(10) unsigned NOT NULL,
  `date_logged` int(10) unsigned NOT NULL,
  `date_processed` int(10) unsigned NOT NULL,
  `is_processed` tinyint(3) unsigned NOT NULL,
  `event_type` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`,`date_logged`),
  KEY `is_processed` (`is_processed`)
) ENGINE=InnoDB AUTO_INCREMENT=14475 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `email_queue`
--

DROP TABLE IF EXISTS `email_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_queue` (
  `id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emr_jobs`
--

DROP TABLE IF EXISTS `emr_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emr_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submission_time` int(10) NOT NULL,
  `user` varchar(255) NOT NULL,
  `type` varchar(255) CHARACTER SET ascii NOT NULL,
  `query` varchar(1024) DEFAULT NULL,
  `jobflow_id` varchar(255) CHARACTER SET ascii NOT NULL,
  `script` varchar(2048) CHARACTER SET ascii NOT NULL,
  `status` varchar(255) CHARACTER SET ascii NOT NULL,
  `finish_time` int(10) DEFAULT NULL,
  `result` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jobflow_id` (`jobflow_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evasion_records`
--

DROP TABLE IF EXISTS `evasion_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evasion_records` (
  `version` smallint(6) NOT NULL,
  `location_tsid` varchar(255) NOT NULL,
  `pc_tsid` varchar(255) NOT NULL,
  `secs` smallint(5) unsigned NOT NULL,
  `data` text NOT NULL,
  `vx_max` smallint(6) NOT NULL,
  `vy_max` smallint(6) NOT NULL,
  `vy_jump` smallint(6) NOT NULL,
  `jetpack` tinyint(4) NOT NULL,
  `door_moves` smallint(6) NOT NULL,
  `px_per_sec` int(6) NOT NULL,
  `last_updated` int(10) unsigned NOT NULL,
  `date_created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_tsid`,`version`),
  KEY `pc_tsid` (`pc_tsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `facebook_graph_cache`
--

DROP TABLE IF EXISTS `facebook_graph_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facebook_graph_cache` (
  `fb_uid` varchar(255) NOT NULL,
  `friends` text NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  PRIMARY KEY (`fb_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feats_batches`
--

DROP TABLE IF EXISTS `feats_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feats_batches` (
  `feat_id` int(10) unsigned NOT NULL,
  `batch_id` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  `contributors` int(10) unsigned NOT NULL,
  `attempts` tinyint(4) NOT NULL,
  `completions` tinyint(4) NOT NULL,
  PRIMARY KEY (`feat_id`,`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feats_contributions`
--

DROP TABLE IF EXISTS `feats_contributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feats_contributions` (
  `feat_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `after_completion` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`feat_id`,`player_id`,`after_completion`),
  KEY `feat_id` (`feat_id`,`amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feats_progress`
--

DROP TABLE IF EXISTS `feats_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feats_progress` (
  `feat_id` int(10) unsigned NOT NULL,
  `counter` int(10) unsigned NOT NULL,
  `is_complete` tinyint(3) unsigned NOT NULL,
  `completed_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`feat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feats_share`
--

DROP TABLE IF EXISTS `feats_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feats_share` (
  `share_code` varchar(6) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `service` char(1) NOT NULL,
  `share_date` int(10) unsigned NOT NULL,
  `share_day` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `quickstart` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`share_code`,`service`),
  KEY `share_day` (`share_day`),
  KEY `player_tsid` (`player_tsid`),
  KEY `share_day_2` (`share_day`,`service`,`clicks`),
  KEY `share_day_3` (`share_day`,`service`,`quickstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_replies`
--

DROP TABLE IF EXISTS `forum_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `is_staff` tinyint(3) unsigned NOT NULL,
  `body` text NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `date_last_edited` int(10) unsigned NOT NULL,
  `last_edited_by_user` int(10) unsigned NOT NULL,
  `replies_since` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`,`date_create`),
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=326095 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_topics`
--

DROP TABLE IF EXISTS `forum_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forum_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` tinyint(3) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `is_staff` tinyint(3) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `date_last_edited` int(10) unsigned NOT NULL,
  `last_edited_by_user` int(10) unsigned NOT NULL,
  `num_replies` int(10) unsigned NOT NULL,
  `is_locked` tinyint(3) unsigned NOT NULL,
  `date_locked` int(10) unsigned NOT NULL,
  `locked_by` varchar(255) NOT NULL,
  `is_sticky` tinyint(3) unsigned NOT NULL,
  `is_hot` tinyint(3) unsigned NOT NULL,
  `hot_tag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `forum_id` (`forum_id`,`date_update`),
  KEY `forum_id_2` (`forum_id`,`is_sticky`,`date_update`)
) ENGINE=InnoDB AUTO_INCREMENT=30791 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `giants`
--

DROP TABLE IF EXISTS `giants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `giants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `points` int(10) unsigned NOT NULL,
  `date_last_points` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `points` (`points`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `glitchamaphone_tokens`
--

DROP TABLE IF EXISTS `glitchamaphone_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glitchamaphone_tokens` (
  `id` int(10) unsigned NOT NULL,
  `secret` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `glitchamaphone_tracks`
--

DROP TABLE IF EXISTS `glitchamaphone_tracks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `glitchamaphone_tracks` (
  `id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `token_id` int(10) unsigned NOT NULL,
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `secret` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_mp3` int(10) unsigned NOT NULL,
  `date_thumb` int(10) unsigned NOT NULL,
  `parent_track_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `track_data` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`,`is_deleted`,`date_create`),
  KEY `token_id` (`token_id`,`is_deleted`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tsid` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `mode` varchar(255) NOT NULL,
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `is_public` tinyint(3) unsigned NOT NULL,
  `member_count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tsid` (`tsid`),
  KEY `is_deleted` (`is_deleted`,`is_public`,`member_count`),
  KEY `is_deleted_2` (`is_deleted`,`is_public`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=4107 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups_replies`
--

DROP TABLE IF EXISTS `groups_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `body` text NOT NULL,
  `date_last_edited` int(10) unsigned NOT NULL,
  `last_edited_by_user` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`,`date_create`),
  KEY `topic_id_2` (`topic_id`,`is_deleted`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=81745 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups_topics`
--

DROP TABLE IF EXISTS `groups_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `num_replies` int(10) unsigned NOT NULL,
  `date_last_edited` int(10) unsigned NOT NULL,
  `last_edited_by_user` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`,`is_deleted`,`date_update`),
  KEY `group_id_2` (`group_id`,`date_create`),
  KEY `is_deleted` (`is_deleted`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=14221 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hadoop_jobs`
--

DROP TABLE IF EXISTS `hadoop_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hadoop_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `submission_time` int(10) NOT NULL,
  `user` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `query` varchar(1024) DEFAULT NULL,
  `status` varchar(255) CHARACTER SET ascii NOT NULL,
  `finish_time` int(10) DEFAULT NULL,
  `result` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `player_level` int(10) unsigned NOT NULL,
  `abuse_player_id` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `is_billing` tinyint(3) unsigned NOT NULL,
  `casetype` varchar(255) NOT NULL DEFAULT 'general',
  `priority` tinyint(3) NOT NULL,
  `staff_opened` varchar(255) DEFAULT NULL,
  `assigned` varchar(255) NOT NULL,
  `ua` varchar(255) NOT NULL,
  `fv` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `client_error_id` int(10) unsigned NOT NULL,
  `bug_tracker_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assigned` (`assigned`,`status`),
  KEY `client_error_id` (`client_error_id`),
  KEY `user_id` (`user_id`,`status`),
  KEY `priority` (`priority`,`date_update`),
  KEY `abuse_player_id` (`abuse_player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60612 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_canned`
--

DROP TABLE IF EXISTS `help_canned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_canned` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `in_order` int(10) unsigned NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  `edited_by` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `in_order` (`in_order`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_incident_notes`
--

DROP TABLE IF EXISTS `help_incident_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_incident_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `incident_id` int(10) unsigned NOT NULL,
  `who` varchar(255) NOT NULL,
  `type` varchar(15) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  `old_data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `incident_id` (`incident_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_incidents`
--

DROP TABLE IF EXISTS `help_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_incidents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `opened_by` varchar(255) NOT NULL,
  `assigned` varchar(255) NOT NULL,
  `headline` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `irc_log` text NOT NULL,
  `chat_log` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_log`
--

DROP TABLE IF EXISTS `help_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `day` date NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `player_name` varchar(255) NOT NULL,
  `msg` text NOT NULL,
  `channel` varchar(255) NOT NULL,
  `group_id` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`),
  KEY `day` (`channel`,`day`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=7248329 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_notes`
--

DROP TABLE IF EXISTS `help_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `case_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `who` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `old_value` varchar(255) NOT NULL,
  `new_value` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=228892 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_replies`
--

DROP TABLE IF EXISTS `help_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `case_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `who` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ua` varchar(255) NOT NULL,
  `fv` varchar(255) NOT NULL,
  `is_casenote` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `case_id` (`case_id`,`date_create`),
  KEY `case_id_2` (`case_id`,`is_casenote`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=108732 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `home_images_approve`
--

DROP TABLE IF EXISTS `home_images_approve`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_images_approve` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_tsid` varchar(255) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `date_added` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_tsid` (`location_tsid`)
) ENGINE=InnoDB AUTO_INCREMENT=12720005 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `home_images_delete`
--

DROP TABLE IF EXISTS `home_images_delete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_images_delete` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_tsid` varchar(255) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `date_delete` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `home_images_failures`
--

DROP TABLE IF EXISTS `home_images_failures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_images_failures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_tsid` varchar(255) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `date_failure` int(10) unsigned NOT NULL,
  `info` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_tsid` (`location_tsid`)
) ENGINE=InnoDB AUTO_INCREMENT=11009 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `home_images_jobs`
--

DROP TABLE IF EXISTS `home_images_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `home_images_jobs` (
  `id` bigint(20) unsigned NOT NULL,
  `location_tsid` varchar(255) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_complete` int(10) unsigned NOT NULL,
  `is_ok` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_tsid` (`location_tsid`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `homepage_quotes`
--

DROP TABLE IF EXISTS `homepage_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_quotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `service` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `date_posted` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_gs_extras`
--

DROP TABLE IF EXISTS `item_gs_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_gs_extras` (
  `item_id` int(10) DEFAULT NULL,
  `class_id` varchar(255) DEFAULT NULL,
  `gs_data` text,
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `landings`
--

DROP TABLE IF EXISTS `landings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `landings` (
  `landing_code` varchar(255) NOT NULL,
  `num_visits` int(10) unsigned NOT NULL,
  `num_starts` int(10) unsigned NOT NULL,
  `num_regs` int(10) unsigned NOT NULL,
  `num_fb_scans` int(10) unsigned NOT NULL,
  `num_staff_visits` int(10) unsigned NOT NULL,
  `date_last_visit` int(10) unsigned NOT NULL,
  `landing_version` int(11) NOT NULL,
  PRIMARY KEY (`landing_code`,`landing_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `landings_logs`
--

DROP TABLE IF EXISTS `landings_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `landings_logs` (
  `id` bigint(20) unsigned NOT NULL,
  `date_loaded` int(10) unsigned NOT NULL,
  `landing_code` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `ts_auth` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leaderboards_cache`
--

DROP TABLE IF EXISTS `leaderboards_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaderboards_cache` (
  `player_id` int(10) unsigned NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  `needs_rebuild` tinyint(3) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`player_id`),
  KEY `date_updated` (`date_updated`),
  KEY `needs_rebuild` (`needs_rebuild`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `leaderboards_players_new`
--

DROP TABLE IF EXISTS `leaderboards_players_new`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leaderboards_players_new` (
  `player_id` int(10) unsigned NOT NULL,
  `leaderboard` varchar(255) NOT NULL,
  `score` int(10) unsigned NOT NULL,
  PRIMARY KEY (`player_id`,`leaderboard`),
  KEY `leaderboard` (`leaderboard`,`score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `location_gs_data`
--

DROP TABLE IF EXISTS `location_gs_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_gs_data` (
  `location_tsid` varchar(255) DEFAULT NULL,
  `location_name` varchar(255) NOT NULL,
  `gs_data` mediumtext,
  UNIQUE KEY `location_tsid` (`location_tsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logins`
--

DROP TABLE IF EXISTS `logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logins` (
  `user_id` int(10) unsigned NOT NULL,
  `b_cookie` bigint(20) unsigned NOT NULL,
  `date_login` int(10) unsigned NOT NULL,
  `ua` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`,`b_cookie`,`date_login`),
  KEY `b_cookie` (`b_cookie`,`date_login`),
  KEY `user_id` (`user_id`,`date_login`),
  KEY `date_login` (`date_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logins_cookies`
--

DROP TABLE IF EXISTS `logins_cookies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logins_cookies` (
  `b_cookie` bigint(20) unsigned NOT NULL,
  `num_u` int(10) unsigned NOT NULL,
  `num_t` int(10) unsigned NOT NULL,
  PRIMARY KEY (`b_cookie`),
  KEY `num_u` (`num_u`),
  KEY `num_t` (`num_t`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logins_users`
--

DROP TABLE IF EXISTS `logins_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logins_users` (
  `user_id` int(10) unsigned NOT NULL,
  `num_u` int(10) unsigned NOT NULL,
  `num_t` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `num_u` (`num_u`),
  KEY `num_t` (`num_t`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organizations`
--

DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organizations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tsid` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `member_count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tsid` (`tsid`),
  KEY `is_deleted` (`is_deleted`,`member_count`),
  KEY `is_deleted_2` (`is_deleted`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_giftpacks`
--

DROP TABLE IF EXISTS `payments_giftpacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_giftpacks` (
  `user_id` int(10) unsigned NOT NULL,
  `total_amount` int(10) unsigned NOT NULL,
  `num_packs` int(10) unsigned NOT NULL,
  `choices` text NOT NULL,
  `status` text NOT NULL,
  `is_completed` tinyint(3) unsigned NOT NULL,
  `is_applied` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_paypal_ipn`
--

DROP TABLE IF EXISTS `payments_paypal_ipn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_paypal_ipn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_create` int(10) unsigned NOT NULL,
  `txn_id` varchar(20) NOT NULL,
  `recurring_id` varchar(20) NOT NULL,
  `data` text NOT NULL,
  `error` text NOT NULL,
  `is_confirmed` tinyint(3) unsigned NOT NULL,
  `is_processed` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`),
  KEY `txn_id` (`txn_id`,`date_create`),
  KEY `is_confirmed` (`is_confirmed`,`is_processed`),
  KEY `recurring_id` (`recurring_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=18808 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_paypal_temp`
--

DROP TABLE IF EXISTS `payments_paypal_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_paypal_temp` (
  `token` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `data` varchar(255) NOT NULL,
  PRIMARY KEY (`token`),
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_recurring`
--

DROP TABLE IF EXISTS `payments_recurring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_recurring` (
  `service` varchar(255) NOT NULL,
  `id` varchar(20) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_cancelled` int(10) unsigned NOT NULL,
  `date_first_payment` int(10) unsigned NOT NULL,
  `date_next_payment` int(10) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL,
  `product` varchar(255) NOT NULL,
  PRIMARY KEY (`service`,`id`),
  KEY `user_id` (`user_id`),
  KEY `date_cancelled` (`date_cancelled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_tempstate`
--

DROP TABLE IF EXISTS `payments_tempstate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_tempstate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`),
  KEY `user_id` (`user_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=568 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_transactions`
--

DROP TABLE IF EXISTS `payments_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_transactions` (
  `service` varchar(255) NOT NULL,
  `id` varchar(20) NOT NULL,
  `recurring_id` varchar(20) NOT NULL,
  `refund_for` varchar(20) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `target_user_id` int(11) NOT NULL,
  `target_tsid` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `day_create` date NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `display` tinyint(3) unsigned NOT NULL,
  `is_good` tinyint(3) unsigned NOT NULL,
  `is_complete` tinyint(3) unsigned NOT NULL,
  `is_extension` tinyint(3) unsigned NOT NULL,
  `basket` varchar(255) NOT NULL,
  `applied` tinyint(3) unsigned NOT NULL,
  `email_sent` tinyint(3) unsigned NOT NULL,
  `gift_email_sent` tinyint(3) unsigned NOT NULL,
  `has_been_refunded` tinyint(3) unsigned NOT NULL,
  `mark_for_refund` tinyint(3) unsigned NOT NULL,
  `refund_choice` varchar(255) NOT NULL,
  `refund_target` varchar(255) NOT NULL,
  `refund_target_full` varchar(255) NOT NULL,
  `refunded_date` int(10) unsigned NOT NULL,
  `chosen_target` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`service`,`id`),
  KEY `date_create` (`date_create`),
  KEY `day_create` (`day_create`,`type`,`status`,`basket`),
  KEY `id` (`id`),
  KEY `refund_for` (`refund_for`),
  KEY `recurring_id` (`recurring_id`),
  KEY `user_id` (`user_id`,`display`,`date_create`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments_transactions_notes`
--

DROP TABLE IF EXISTS `payments_transactions_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_transactions_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) NOT NULL,
  `date_create` int(11) NOT NULL,
  `who` varchar(255) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=11806 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `user_shard` smallint(5) unsigned NOT NULL,
  `is_primary` tinyint(3) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_norm` varbinary(255) NOT NULL,
  `tsid` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_last_client` int(10) unsigned NOT NULL,
  `date_last_lb` int(10) unsigned NOT NULL,
  `date_last_active` int(10) unsigned NOT NULL,
  `date_last_login_end` int(10) unsigned NOT NULL,
  `is_dormant` tinyint(3) unsigned NOT NULL,
  `seen_intro` tinyint(3) unsigned NOT NULL,
  `stats_cache` text NOT NULL,
  `needs_avatar_set` tinyint(3) unsigned NOT NULL,
  `forced_rename` tinyint(3) unsigned NOT NULL,
  `av_singles` varchar(255) NOT NULL,
  `av_sheets` varchar(255) NOT NULL,
  `av_needs_update` tinyint(3) unsigned NOT NULL,
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `affected_by_evil` tinyint(3) unsigned NOT NULL,
  `abuse_level` tinyint(3) unsigned NOT NULL,
  `is_in_timeout` tinyint(3) unsigned NOT NULL,
  `help_silenced` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tsid` (`tsid`),
  KEY `user_id` (`user_id`),
  KEY `date_create` (`date_create`),
  KEY `name` (`name`),
  KEY `is_dormant` (`is_dormant`,`date_last_client`),
  KEY `date_last_active` (`date_last_active`)
) ENGINE=InnoDB AUTO_INCREMENT=192525 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_agents`
--

DROP TABLE IF EXISTS `players_agents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_agents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_latest` int(10) unsigned NOT NULL,
  `ua` varchar(255) NOT NULL,
  `fv` varchar(255) NOT NULL,
  `num` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_id` (`player_id`,`ua`,`fv`),
  KEY `player_id_2` (`player_id`,`date_latest`)
) ENGINE=InnoDB AUTO_INCREMENT=9118978 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_friendcache`
--

DROP TABLE IF EXISTS `players_friendcache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_friendcache` (
  `player_id` int(10) unsigned NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_renames`
--

DROP TABLE IF EXISTS `players_renames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_renames` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `old_name` varchar(255) NOT NULL,
  `new_name` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=34946 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_reset_tokens`
--

DROP TABLE IF EXISTS `players_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_reset_tokens` (
  `player_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `reset_token` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  KEY `player_id` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_visitme`
--

DROP TABLE IF EXISTS `players_visitme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_visitme` (
  `player_tsid` varchar(255) NOT NULL,
  `recent_visitors` int(10) unsigned NOT NULL,
  `is_active` tinyint(3) unsigned NOT NULL,
  `quartile` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`player_tsid`),
  KEY `recent_visitors` (`recent_visitors`),
  KEY `is_included` (`quartile`),
  KEY `is_active` (`is_active`,`recent_visitors`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `players_wardrobe_state`
--

DROP TABLE IF EXISTS `players_wardrobe_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players_wardrobe_state` (
  `player_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `state` text NOT NULL,
  PRIMARY KEY (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `press_invites`
--

DROP TABLE IF EXISTS `press_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `press_invites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `code` char(5) NOT NULL,
  `added_by` varchar(255) NOT NULL,
  `date_added` int(10) unsigned NOT NULL,
  `date_expires` int(10) unsigned NOT NULL,
  `max_uses` int(10) unsigned NOT NULL,
  `use_count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `realty`
--

DROP TABLE IF EXISTS `realty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `realty` (
  `tsid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `has_owner` tinyint(4) NOT NULL,
  `is_hidden` tinyint(3) unsigned NOT NULL,
  `cost` int(11) NOT NULL,
  `mote_name` varchar(255) NOT NULL,
  `hub_name` varchar(255) NOT NULL,
  `ext_img` varchar(255) NOT NULL,
  PRIMARY KEY (`tsid`),
  KEY `is_hidden` (`is_hidden`,`has_owner`,`name`),
  KEY `is_hidden_2` (`is_hidden`,`has_owner`,`hub_name`,`name`),
  KEY `is_hidden_3` (`is_hidden`,`has_owner`,`mote_name`,`hub_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referrals_share`
--

DROP TABLE IF EXISTS `referrals_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals_share` (
  `share_code` varchar(6) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `service` char(1) NOT NULL,
  `share_date` int(10) unsigned NOT NULL,
  `share_day` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `quickstart` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`share_code`,`service`),
  KEY `share_day` (`share_day`),
  KEY `player_tsid` (`player_tsid`),
  KEY `share_day_2` (`share_day`,`service`,`clicks`),
  KEY `share_day_3` (`share_day`,`service`,`quickstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `repl_test`
--

DROP TABLE IF EXISTS `repl_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `repl_test` (
  `id` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `value` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `routing_items`
--

DROP TABLE IF EXISTS `routing_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `routing_items` (
  `location_tsid` varchar(255) NOT NULL,
  `item_class_tsid` varchar(255) NOT NULL,
  `num_items` int(10) unsigned NOT NULL,
  `date_updated` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_tsid`,`item_class_tsid`),
  KEY `item_class_tsid` (`item_class_tsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shortlinks`
--

DROP TABLE IF EXISTS `shortlinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shortlinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `class` char(1) NOT NULL,
  `code` varchar(6) NOT NULL,
  `url` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`class`,`code`),
  KEY `class_2` (`class`,`url`)
) ENGINE=InnoDB AUTO_INCREMENT=2302930 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `signups`
--

DROP TABLE IF EXISTS `signups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `fb_uid` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `date_sent` int(10) unsigned NOT NULL,
  `date_clicked` int(10) unsigned NOT NULL,
  `date_signup` int(10) unsigned NOT NULL,
  `date_bounced` int(10) unsigned NOT NULL,
  `date_last_reminder` int(10) unsigned NOT NULL,
  `reminder_count` int(10) unsigned NOT NULL,
  `added_by` int(10) unsigned NOT NULL,
  `added_method` varchar(255) NOT NULL,
  `replenished` tinyint(3) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `name` varchar(2355) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `age` varchar(255) NOT NULL,
  `dob` varchar(255) NOT NULL,
  `bucket` varchar(255) NOT NULL,
  `ref_url` varchar(255) NOT NULL,
  `dont_remind` tinyint(3) unsigned NOT NULL,
  `email_bounced` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `email_bounced` (`email_bounced`,`dont_remind`,`date_signup`),
  KEY `date_sent` (`date_sent`,`date_create`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=156829 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `snaps_download_queue`
--

DROP TABLE IF EXISTS `snaps_download_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snaps_download_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `date_create` int(11) NOT NULL,
  `date_started` int(11) NOT NULL,
  `date_end` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `player_id` (`player_id`),
  KEY `date_started` (`date_started`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=2840 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `snaps_share`
--

DROP TABLE IF EXISTS `snaps_share`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snaps_share` (
  `share_code` varchar(6) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `service` char(1) NOT NULL,
  `share_date` int(10) NOT NULL,
  `share_day` date NOT NULL,
  `clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `quickstart` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`share_code`,`service`),
  KEY `player_tsid` (`player_tsid`),
  KEY `share_day` (`share_day`,`service`,`clicks`),
  KEY `share_day_2` (`share_day`,`service`,`quickstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `snaps_starred`
--

DROP TABLE IF EXISTS `snaps_starred`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snaps_starred` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `snap_id` int(10) unsigned NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `shard` int(5) unsigned NOT NULL,
  `starred_by` varchar(50) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date_create` (`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=3280 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_shown` tinyint(3) unsigned NOT NULL,
  `is_online` tinyint(3) unsigned NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `survey_answers`
--

DROP TABLE IF EXISTS `survey_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `date_answered` int(10) unsigned NOT NULL,
  `answer` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`question_id`,`date_answered`)
) ENGINE=InnoDB AUTO_INCREMENT=880536 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `survey_questions`
--

DROP TABLE IF EXISTS `survey_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_live` tinyint(3) unsigned NOT NULL,
  `question` varchar(255) NOT NULL,
  `mode` tinyint(3) unsigned NOT NULL,
  `pre_text` varchar(255) NOT NULL,
  `post_text` varchar(255) NOT NULL,
  `choice_1` varchar(255) NOT NULL,
  `choice_2` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test_data`
--

DROP TABLE IF EXISTS `test_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `str` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tweets`
--

DROP TABLE IF EXISTS `tweets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tweets` (
  `url` varchar(255) NOT NULL,
  `account` varchar(255) NOT NULL,
  `date_posted` int(10) unsigned NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`url`),
  KEY `account` (`account`,`date_posted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_stub` tinyint(3) unsigned NOT NULL,
  `user_shard` smallint(5) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_conf` tinyint(3) unsigned NOT NULL,
  `email_bouncing` tinyint(4) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_signup` int(10) unsigned NOT NULL,
  `date_register` int(10) unsigned NOT NULL,
  `last_activity` int(10) unsigned NOT NULL,
  `fb_uid` varchar(255) NOT NULL,
  `twitter_uid` varchar(255) NOT NULL,
  `friends_last_update` int(10) unsigned NOT NULL,
  `invites_suggest_last_update` int(10) unsigned NOT NULL,
  `last_survey` int(10) unsigned NOT NULL,
  `is_dormant` tinyint(3) unsigned NOT NULL,
  `tsa_user` varchar(255) NOT NULL,
  `client_enabled` tinyint(3) unsigned NOT NULL,
  `fb_token` varchar(255) NOT NULL,
  `fb_token_expires` int(10) unsigned NOT NULL,
  `credits` int(10) unsigned NOT NULL,
  `tokens` int(10) unsigned NOT NULL,
  `votes` int(10) unsigned NOT NULL,
  `invites` int(10) unsigned NOT NULL,
  `is_subscriber` tinyint(3) unsigned NOT NULL,
  `sub_expires` date NOT NULL,
  `sub_type` varchar(255) NOT NULL,
  `image_date` int(10) unsigned NOT NULL,
  `bio_name` varchar(255) NOT NULL,
  `bio_desc` text NOT NULL,
  `bio_edited` tinyint(3) unsigned NOT NULL,
  `show_name` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `is_deleted` tinyint(3) unsigned NOT NULL,
  `date_deleted` int(10) NOT NULL DEFAULT '0',
  `forum_blocked` tinyint(3) unsigned NOT NULL,
  `signup_method` varchar(255) NOT NULL,
  `invite_id` int(10) unsigned NOT NULL,
  `invite_email` varchar(255) NOT NULL,
  `ref_player_id` int(10) unsigned NOT NULL,
  `ref_code` varchar(255) NOT NULL,
  `fb_likes` tinyint(3) unsigned NOT NULL,
  `shortlink_ref_id` int(10) unsigned NOT NULL,
  `ref_url` varchar(255) NOT NULL,
  `origin_url` varchar(255) NOT NULL,
  `has_auto_refunds` tinyint(4) NOT NULL,
  `has_manual_refunds` tinyint(4) NOT NULL,
  `has_chosen_refunds` tinyint(3) unsigned NOT NULL,
  `needs_refund_targets` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fb_uid` (`fb_uid`),
  KEY `email` (`email`),
  KEY `is_subscriber` (`is_subscriber`),
  KEY `twitter_uid` (`twitter_uid`),
  KEY `signup_method` (`signup_method`,`is_stub`,`date_signup`),
  KEY `ref_code` (`ref_code`,`is_stub`,`date_signup`)
) ENGINE=InnoDB AUTO_INCREMENT=192953 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_casenotes`
--

DROP TABLE IF EXISTS `users_casenotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_casenotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `player_tsid` varchar(255) NOT NULL,
  `date_create` int(10) NOT NULL,
  `note` text NOT NULL,
  `who` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`player_tsid`)
) ENGINE=InnoDB AUTO_INCREMENT=5712 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_credits`
--

DROP TABLE IF EXISTS `users_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_credits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `new_balance` int(10) unsigned NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`date_create`),
  KEY `delta` (`delta`)
) ENGINE=InnoDB AUTO_INCREMENT=2030495 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_devices`
--

DROP TABLE IF EXISTS `users_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_devices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `device_type` varchar(255) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `registration_id` varchar(255) DEFAULT '',
  `date_added` int(10) unsigned NOT NULL,
  `date_last_sent` int(10) unsigned NOT NULL,
  `date_failed` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_2` (`user_id`,`device_type`,`device_id`,`registration_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=112630 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_email_updates`
--

DROP TABLE IF EXISTS `users_email_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_email_updates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `email_old` varchar(255) NOT NULL,
  `email_new` varchar(255) NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `who` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email_old` (`email_old`,`email_new`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=386 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_emails`
--

DROP TABLE IF EXISTS `users_emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_emails` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `signup_id` int(10) unsigned NOT NULL,
  `sent_to` varchar(255) NOT NULL,
  `date_sent` int(10) unsigned NOT NULL,
  `day_sent` date NOT NULL,
  `msg_type` varchar(255) NOT NULL,
  `sub_type` varchar(255) NOT NULL,
  `skipped` tinyint(3) unsigned NOT NULL,
  `is_notice` tinyint(3) unsigned NOT NULL,
  `is_cleared` tinyint(3) unsigned NOT NULL,
  `date_cleared` int(10) unsigned NOT NULL,
  `date_opened` int(10) unsigned NOT NULL,
  `date_clicked` int(10) unsigned NOT NULL,
  `resub_date` int(10) unsigned NOT NULL,
  `resub_level` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`date_sent`),
  KEY `user_id_2` (`user_id`,`is_notice`,`is_cleared`,`date_sent`),
  KEY `msg_type` (`msg_type`,`skipped`,`resub_date`),
  KEY `sent_to` (`sent_to`)
) ENGINE=InnoDB AUTO_INCREMENT=328668 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_emails_clicks`
--

DROP TABLE IF EXISTS `users_emails_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_emails_clicks` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `signup_id` int(10) unsigned NOT NULL,
  `email_id` int(10) unsigned NOT NULL,
  `date_clicked` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_links`
--

DROP TABLE IF EXISTS `users_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `scanned_twitter` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11038 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_manual_refunds`
--

DROP TABLE IF EXISTS `users_manual_refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_manual_refunds` (
  `user_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `refund_method` varchar(255) NOT NULL,
  `paypal_email` varchar(255) NOT NULL,
  `name_address` text NOT NULL,
  `is_done` tinyint(3) unsigned NOT NULL,
  `refunded_date` int(10) unsigned NOT NULL,
  `txns_num` int(10) unsigned NOT NULL,
  `txns_amt` int(10) unsigned NOT NULL,
  `pp_txn` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_prefs`
--

DROP TABLE IF EXISTS `users_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_prefs` (
  `user_id` int(10) unsigned NOT NULL,
  `email_skill_finished` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `email_contact_added` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `email_announce` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `email_tips` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `email_allow` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `ballot_explain` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `referral_explain` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_subscriptions`
--

DROP TABLE IF EXISTS `users_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=43840 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_subscriptions_stipends`
--

DROP TABLE IF EXISTS `users_subscriptions_stipends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_subscriptions_stipends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `day_apply` date NOT NULL,
  `date_applied` int(10) unsigned NOT NULL,
  `is_applied` tinyint(3) unsigned NOT NULL,
  `level` varchar(255) NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60678 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_suggestions`
--

DROP TABLE IF EXISTS `users_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_suggestions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `target_user_id` int(10) unsigned NOT NULL,
  `target_player_id` int(10) unsigned NOT NULL,
  `target_player_tsid` varchar(255) NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `fb_name` varchar(255) NOT NULL,
  `fb_picture` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `target_user_id` (`target_user_id`),
  KEY `target_player_id` (`target_player_id`),
  KEY `user_id` (`user_id`,`target_player_tsid`)
) ENGINE=InnoDB AUTO_INCREMENT=11714557 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_suggestions_invites`
--

DROP TABLE IF EXISTS `users_suggestions_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_suggestions_invites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `status` varchar(15) NOT NULL,
  `target_signup_id` int(10) unsigned NOT NULL,
  `date_update` int(10) unsigned NOT NULL,
  `fb_uid` varchar(255) NOT NULL,
  `fb_name` varchar(255) NOT NULL,
  `fb_picture` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=916140 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_tokens`
--

DROP TABLE IF EXISTS `users_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `new_balance` int(10) unsigned NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=696616 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_votes`
--

DROP TABLE IF EXISTS `users_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date_create` int(10) unsigned NOT NULL,
  `delta` int(11) NOT NULL,
  `new_balance` int(10) unsigned NOT NULL,
  `details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`date_create`)
) ENGINE=InnoDB AUTO_INCREMENT=43351 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-06-11 12:31:29
