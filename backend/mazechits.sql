-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 12, 2019 at 07:31 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mazechits`
--

-- --------------------------------------------------------

--
-- Table structure for table `auction_on_holidays`
--

CREATE TABLE `auction_on_holidays` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `holiday_date` date NOT NULL,
  `remarks` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bank_details`
--

CREATE TABLE `bank_details` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `account_number` varchar(45) NOT NULL,
  `ifsc_code` varchar(45) NOT NULL,
  `bank_code` varchar(45) NOT NULL,
  `bank_branch_name` varchar(45) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bidding_details`
--

CREATE TABLE `bidding_details` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `installment_no` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `chit_value` varchar(45) NOT NULL,
  `bidding_amount` varchar(45) NOT NULL,
  `commission_amount` varchar(45) NOT NULL,
  `cgst` varchar(45) NOT NULL,
  `sgst` varchar(45) NOT NULL,
  `igst` varchar(45) NOT NULL,
  `gst` varchar(45) NOT NULL,
  `total_deduction` varchar(45) NOT NULL,
  `pending_due_amount` varchar(45) NOT NULL,
  `total_release_amount` varchar(45) NOT NULL,
  `customer_released` varchar(45) NOT NULL,
  `pending_release` varchar(45) NOT NULL,
  `divident_amount` varchar(45) NOT NULL,
  `installment_amount` varchar(45) NOT NULL,
  `profit_amount` varchar(45) NOT NULL,
  `auction_type` varchar(45) NOT NULL,
  `payment_generated_on` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `remark` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `head_office_id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `age` int(11) NOT NULL,
  `branch_code` varchar(45) NOT NULL,
  `state_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `pincode` int(11) NOT NULL,
  `branch_wise_penalty` int(11) NOT NULL,
  `bonus` int(11) NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `phone_no` int(11) NOT NULL,
  `address` text NOT NULL,
  `prize_subscriber_penalty` int(11) NOT NULL,
  `non_prize_subscriber_penalty` int(11) NOT NULL,
  `bonus_days` int(11) NOT NULL,
  `penalty_days` int(11) NOT NULL,
  `busin_agent_target` int(11) NOT NULL,
  `br_fd_total_months` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `agent_incentive_percent` int(11) NOT NULL,
  `agent_commis_percent` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `business_agents`
--

CREATE TABLE `business_agents` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `saluation` varchar(45) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `initial` varchar(45) NOT NULL,
  `photo` longtext NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(45) NOT NULL,
  `mobile_no` int(11) NOT NULL,
  `phone_no` int(11) NOT NULL,
  `email_id` varchar(100) NOT NULL,
  `father_name` varchar(45) NOT NULL,
  `spouse_name` varchar(45) NOT NULL,
  `adhar_no` varchar(100) NOT NULL,
  `pan_no` varchar(100) NOT NULL,
  `ration_card_no` varchar(100) NOT NULL,
  `license_no` varchar(100) NOT NULL,
  `doj` date NOT NULL,
  `door_no` varchar(45) NOT NULL,
  `building_name` varchar(45) NOT NULL,
  `address_line_1` varchar(45) NOT NULL,
  `address_line_2` varchar(45) NOT NULL,
  `land_mark` varchar(45) NOT NULL,
  `state_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `pincode` int(11) NOT NULL,
  `designation_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `esi` varchar(100) NOT NULL,
  `blood_group` varchar(45) NOT NULL,
  `target_new_business` varchar(45) NOT NULL,
  `target_collection` varchar(45) NOT NULL,
  `qualification` varchar(45) NOT NULL,
  `extra_qualification` varchar(45) NOT NULL,
  `previous_company_details` varchar(45) NOT NULL,
  `experience` varchar(45) NOT NULL,
  `resignation_date` date NOT NULL,
  `reason_for_resignation` varchar(45) NOT NULL,
  `verification_on_call_by` varchar(45) NOT NULL,
  `holder_name` varchar(45) NOT NULL,
  `account_no` varchar(100) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `ifsc_code` varchar(45) NOT NULL,
  `pf` varchar(100) NOT NULL,
  `net_salary` int(11) NOT NULL,
  `monthly_income` int(11) NOT NULL,
  `hra` varchar(45) NOT NULL,
  `da` varchar(45) NOT NULL,
  `deduction` varchar(45) NOT NULL,
  `other` varchar(45) NOT NULL,
  `enrollment_fee` varchar(45) NOT NULL,
  `discount_fee` varchar(45) NOT NULL,
  `commission` varchar(45) NOT NULL,
  `incentive` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `update_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cash_settlements`
--

CREATE TABLE `cash_settlements` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `total_amount` int(11) NOT NULL,
  `amt_1` int(11) NOT NULL,
  `amt_2` int(11) NOT NULL,
  `amt_5` int(11) NOT NULL,
  `amt_10` int(11) NOT NULL,
  `amt_20` int(11) NOT NULL,
  `amt_50` int(11) NOT NULL,
  `amt_100` int(11) NOT NULL,
  `amt_200` int(11) NOT NULL,
  `amt_500` int(11) NOT NULL,
  `amt_1000` int(11) NOT NULL,
  `amt_2000` int(11) NOT NULL,
  `received_amount` int(11) NOT NULL,
  `collection_employee` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `charge_types`
--

CREATE TABLE `charge_types` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `charge_name` varchar(250) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chit_details`
--

CREATE TABLE `chit_details` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `ticket_no` int(11) NOT NULL,
  `prized_status` varchar(45) NOT NULL,
  `eligibility_status` varchar(45) NOT NULL,
  `chit_intimation_status` varchar(45) NOT NULL,
  `chit_status` varchar(45) NOT NULL,
  `payment_type` varchar(45) NOT NULL,
  `collection_area` varchar(45) NOT NULL,
  `collection_pin` varchar(45) NOT NULL,
  `collection_agent` varchar(45) NOT NULL,
  `agent_type` varchar(45) NOT NULL,
  `introducer_name` varchar(45) NOT NULL,
  `introducer_id` int(11) NOT NULL,
  `date_introducer` date NOT NULL,
  `pending_amount` int(11) NOT NULL,
  `paid_amount` int(11) NOT NULL,
  `penaulty_amount` int(11) NOT NULL,
  `bonus_amount` int(11) NOT NULL,
  `advance_amount` varchar(45) NOT NULL,
  `completed_auction` int(11) NOT NULL,
  `current_due` varchar(45) NOT NULL,
  `next_due` varchar(45) NOT NULL,
  `pending_days` varchar(45) NOT NULL,
  `level` varchar(45) NOT NULL,
  `chit_type` varchar(45) NOT NULL,
  `customer_type` varchar(45) NOT NULL,
  `pending_installment` varchar(45) NOT NULL,
  `agent_incentive` varchar(45) NOT NULL,
  `pledge_status` varchar(45) NOT NULL,
  `pledge_enrl_id` varchar(45) NOT NULL,
  `discount_due` varchar(45) NOT NULL,
  `cancel_amount` varchar(45) NOT NULL,
  `return_amount` varchar(45) NOT NULL,
  `ticket_type` varchar(45) NOT NULL,
  `enrol_type` varchar(45) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `remark` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `city_name` varchar(50) NOT NULL,
  `pincode` int(11) NOT NULL,
  `dist_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `collection_areas`
--

CREATE TABLE `collection_areas` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `collection_area` varchar(100) NOT NULL,
  `area_pincode` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `shortname` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phonecode` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `customer_code` varchar(45) NOT NULL,
  `name` varchar(100) NOT NULL,
  `photo` longtext NOT NULL,
  `dob` date NOT NULL,
  `age` int(11) NOT NULL,
  `gender` varchar(45) NOT NULL,
  `mobile_no` int(11) NOT NULL,
  `phone_no` int(11) NOT NULL,
  `email_id` varchar(45) NOT NULL,
  `adhar_no` varchar(100) NOT NULL,
  `pan_no` varchar(100) NOT NULL,
  `license_no` varchar(100) NOT NULL,
  `ration_card_no` varchar(100) NOT NULL,
  `doj` date NOT NULL,
  `father_name` varchar(100) NOT NULL,
  `spouse_name` varchar(100) NOT NULL,
  `door_no` varchar(45) NOT NULL,
  `buildling_name` varchar(45) NOT NULL,
  `address_line_1` varchar(100) NOT NULL,
  `address_line_2` varchar(45) NOT NULL,
  `land_mark` varchar(45) NOT NULL,
  `state_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `pincode` int(11) NOT NULL,
  `communication_door_no` varchar(45) NOT NULL,
  `communication_building_name` varchar(45) NOT NULL,
  `communication_address_line_1` varchar(100) NOT NULL,
  `communication_address_line_2` varchar(45) NOT NULL,
  `communication_land_mark` varchar(45) NOT NULL,
  `communication_state_id` int(11) NOT NULL,
  `communication_district_id` int(11) NOT NULL,
  `communication_city_id` int(11) NOT NULL,
  `communication_pincode` int(11) NOT NULL,
  `refered_by` varchar(45) NOT NULL,
  `pf_no` varchar(100) NOT NULL,
  `esi_no` varchar(100) NOT NULL,
  `retirement` date NOT NULL,
  `occupation` varchar(45) NOT NULL,
  `source_of_funds` varchar(45) NOT NULL,
  `income` int(11) NOT NULL,
  `relation_ship` varchar(45) NOT NULL,
  `cus_bonus` varchar(45) NOT NULL,
  `cus_penaulty_interest` varchar(45) NOT NULL,
  `cus_divident` varchar(45) NOT NULL,
  `sms` varchar(45) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `designation_name` varchar(100) NOT NULL,
  `code` varchar(45) NOT NULL,
  `remarks` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `android_id` varchar(300) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `device_logins`
--

CREATE TABLE `device_logins` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `district_name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `document_name` varchar(45) NOT NULL,
  `document_no` varchar(45) NOT NULL,
  `document_path` varchar(45) NOT NULL,
  `collect_date` date NOT NULL,
  `document_type` varchar(45) NOT NULL,
  `asset_value` varchar(45) NOT NULL,
  `return_amount` varchar(45) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `remark` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `saluation` varchar(45) NOT NULL,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `gender` varchar(45) NOT NULL,
  `dob` date NOT NULL,
  `photo` longtext NOT NULL,
  `collection_agent` varchar(45) NOT NULL,
  `phone_no` int(11) NOT NULL,
  `mobile_no` int(11) NOT NULL,
  `adhar_card` varchar(100) NOT NULL,
  `driving_license` varchar(100) NOT NULL,
  `pan_no` varchar(100) NOT NULL,
  `ration_card_no` varchar(100) NOT NULL,
  `father_name` varchar(45) NOT NULL,
  `spouse_name` varchar(45) NOT NULL,
  `door_no` varchar(45) NOT NULL,
  `building_name` text NOT NULL,
  `address_line_1` text NOT NULL,
  `address_line_2` text NOT NULL,
  `land_mark` text NOT NULL,
  `state_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `pincode` int(11) NOT NULL,
  `communication_door_no` varchar(45) NOT NULL,
  `communication_building_name` varchar(45) NOT NULL,
  `communication_address_line_1` varchar(45) NOT NULL,
  `communication_address_line_2` varchar(45) NOT NULL,
  `communication_land_mark` varchar(45) NOT NULL,
  `designation_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `esi` varchar(45) NOT NULL,
  `blood_group` varchar(45) NOT NULL,
  `qualification` varchar(45) NOT NULL,
  `previous_company_details` varchar(255) NOT NULL,
  `joining_date` date NOT NULL,
  `experience` varchar(45) NOT NULL,
  `verification_on_call_by` varchar(45) NOT NULL,
  `last_month_salary` varchar(45) NOT NULL,
  `holder_name` varchar(45) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `account_no` varchar(45) NOT NULL,
  `ifsc_code` varchar(45) NOT NULL,
  `target` varchar(45) NOT NULL,
  `salary` int(11) NOT NULL,
  `collection_area` varchar(45) NOT NULL,
  `commission` varchar(45) NOT NULL,
  `incentive` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `pf` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `field_investigations`
--

CREATE TABLE `field_investigations` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `investigation_date` date NOT NULL,
  `investigation_mode` varchar(50) NOT NULL,
  `verified_type` varchar(50) NOT NULL,
  `comments` text NOT NULL,
  `verification_file` varchar(255) NOT NULL,
  `type_of_row` varchar(100) NOT NULL,
  `reference_id` varchar(150) NOT NULL,
  `reference_type` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `emp_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `file_names`
--

CREATE TABLE `file_names` (
  `tenant_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `menu_order` int(11) DEFAULT NULL,
  `level` varchar(45) DEFAULT NULL,
  `icon` longtext,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `group_type` varchar(100) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `schemes_id` int(11) NOT NULL,
  `auction_date` date NOT NULL,
  `auction_time` time NOT NULL,
  `starting_date` date NOT NULL,
  `1st_due_date` date NOT NULL,
  `commission_percent` int(11) NOT NULL,
  `fd_number` varchar(45) NOT NULL,
  `total_fd` int(11) NOT NULL,
  `company_fd` int(11) NOT NULL,
  `Rate_of_interest` int(11) NOT NULL,
  `fd_date` date NOT NULL,
  `maturity_amount` int(11) NOT NULL,
  `fd_bank_id` int(11) NOT NULL,
  `fd_branch` varchar(150) NOT NULL,
  `pso_number` varchar(100) NOT NULL,
  `pso_date` date NOT NULL,
  `blaw_number` varchar(150) NOT NULL,
  `blaw_date` date NOT NULL,
  `cheque_no` varchar(50) NOT NULL,
  `company_chit` int(11) NOT NULL,
  `group_base_penalty` decimal(2,0) NOT NULL,
  `group_base_bonus` decimal(2,0) NOT NULL,
  `group_base_interest` decimal(2,0) DEFAULT NULL,
  `group_base_dividend_cancel_days` int(11) DEFAULT NULL,
  `group_penalty_days` int(11) DEFAULT NULL,
  `group_bonus_days` int(11) DEFAULT NULL,
  `group_base_incentive_percentage` decimal(2,0) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `head_offices`
--

CREATE TABLE `head_offices` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `head_name` varchar(100) NOT NULL,
  `head_code` varchar(45) NOT NULL,
  `mobile_no` int(10) NOT NULL,
  `phone_no` int(10) NOT NULL,
  `email_Id` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `state_id` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `enrol_id` int(11) NOT NULL,
  `approval_type` varchar(100) NOT NULL,
  `approval_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `approved_on` date NOT NULL,
  `approved_by_for_notification` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `bidding_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `employee_id` int(11) NOT NULL,
  `payment_for` varchar(45) NOT NULL,
  `payment_type` varchar(45) NOT NULL,
  `total_amount` varchar(255) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `bank_branch_name` varchar(45) NOT NULL,
  `cheque_no` varchar(45) NOT NULL,
  `cheque_date` date NOT NULL,
  `cheque_status` varchar(45) NOT NULL,
  `transaction_no` varchar(100) NOT NULL,
  `transaction_date` date NOT NULL,
  `debit_from` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payment_types`
--

CREATE TABLE `payment_types` (
  `id` int(11) NOT NULL,
  `payment_name` varchar(100) NOT NULL,
  `remarks` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `tenant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_expenses`
--

CREATE TABLE `petty_cash_expenses` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `avaliable_cash` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `expenses_date` date NOT NULL,
  `expenses_amount` int(11) NOT NULL,
  `expenses_type_id` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_expense_types`
--

CREATE TABLE `petty_cash_expense_types` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `expenses_type` varchar(150) NOT NULL,
  `remarks` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_incomes`
--

CREATE TABLE `petty_cash_incomes` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `available_cash` int(11) NOT NULL,
  `received_date` date NOT NULL,
  `amount_received` int(11) NOT NULL,
  `payment_type_id` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `enrol_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `ticket_no` varchar(50) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `employee_branch_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `receipt_no` varchar(45) NOT NULL,
  `pending_days` int(11) NOT NULL,
  `adjust_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `penalty` int(11) NOT NULL,
  `bonus` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  `date` date NOT NULL,
  `type_of_collection` varchar(45) NOT NULL,
  `payment_type_id` int(11) NOT NULL,
  `cheque_no` varchar(45) NOT NULL,
  `cheque_date` date NOT NULL,
  `cheque_clear_return_date` date NOT NULL,
  `bank_name_id` int(11) NOT NULL,
  `branch_name` varchar(150) NOT NULL,
  `transaction_no` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `tr_bill` varchar(45) NOT NULL,
  `tr_book` varchar(45) NOT NULL,
  `tr_date` date NOT NULL,
  `neft_adjust_id` int(11) NOT NULL,
  `receipt_mode` varchar(45) NOT NULL,
  `receipt_time` time NOT NULL,
  `accounts_date` date NOT NULL,
  `printed` int(11) NOT NULL,
  `remarks` text NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `relationships`
--

CREATE TABLE `relationships` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `relationship_name` varchar(150) NOT NULL,
  `gender` varchar(45) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL,
  `role_code` varchar(45) NOT NULL,
  `remarks` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `schemes`
--

CREATE TABLE `schemes` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `chit_value` int(11) NOT NULL,
  `no_of_months` int(11) NOT NULL,
  `members` int(11) NOT NULL,
  `register_fees` int(11) NOT NULL,
  `entrollment_fees` int(11) NOT NULL,
  `chit_letter` varchar(10) NOT NULL,
  `short_term` varchar(50) NOT NULL,
  `monthly_due` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `slab_bonuses`
--

CREATE TABLE `slab_bonuses` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `scheme_id` int(11) NOT NULL,
  `level1_from` int(11) NOT NULL,
  `level1_to` int(11) NOT NULL,
  `level2_from` int(11) NOT NULL,
  `level2_to` int(11) NOT NULL,
  `level3_from` int(11) NOT NULL,
  `level3_to` int(11) NOT NULL,
  `level4_from` int(11) NOT NULL,
  `level4_to` int(11) NOT NULL,
  `level5_from` int(11) NOT NULL,
  `level5_to` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `level_1_percentage` decimal(2,0) NOT NULL,
  `level_2_percentage` decimal(2,0) NOT NULL,
  `level_3_percentage` decimal(2,0) NOT NULL,
  `level_4_percentage` decimal(2,0) NOT NULL,
  `level_5_percentage` decimal(2,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `slab_dues`
--

CREATE TABLE `slab_dues` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `schemes_id` int(11) NOT NULL,
  `due_1` int(11) NOT NULL,
  `due_2` int(11) NOT NULL,
  `due_3` int(11) NOT NULL,
  `due_4` int(11) NOT NULL,
  `due_5` int(11) NOT NULL,
  `due_6` int(11) NOT NULL,
  `due_7` int(11) NOT NULL,
  `due_8` int(11) NOT NULL,
  `due_9` int(11) NOT NULL,
  `due_10` int(11) NOT NULL,
  `due_11` int(11) NOT NULL,
  `due_12` int(11) NOT NULL,
  `due_13` int(11) NOT NULL,
  `due_14` int(11) NOT NULL,
  `due_15` int(11) NOT NULL,
  `due_16` int(11) NOT NULL,
  `due_17` int(11) NOT NULL,
  `due_18` int(11) NOT NULL,
  `due_19` int(11) NOT NULL,
  `due_20` int(11) NOT NULL,
  `due_21` int(11) NOT NULL,
  `due_22` int(11) NOT NULL,
  `due_23` int(11) NOT NULL,
  `due_24` int(11) NOT NULL,
  `due_25` int(11) NOT NULL,
  `due_26` int(11) NOT NULL,
  `due_27` int(11) NOT NULL,
  `due_28` int(11) NOT NULL,
  `due_29` int(11) NOT NULL,
  `due_30` int(11) NOT NULL,
  `due_31` int(11) NOT NULL,
  `due_32` int(11) NOT NULL,
  `due_33` int(11) NOT NULL,
  `due_34` int(11) NOT NULL,
  `due_35` int(11) NOT NULL,
  `due_36` int(11) NOT NULL,
  `due_37` int(11) NOT NULL,
  `due_38` int(11) NOT NULL,
  `due_39` int(11) NOT NULL,
  `due_40` int(11) NOT NULL,
  `due_41` int(11) NOT NULL,
  `due_42` int(11) NOT NULL,
  `due_43` int(11) NOT NULL,
  `due_44` int(11) NOT NULL,
  `due_45` int(11) NOT NULL,
  `due_46` int(11) NOT NULL,
  `due_47` int(11) NOT NULL,
  `due_48` int(11) NOT NULL,
  `due_49` int(11) NOT NULL,
  `due_50` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `slab_interests`
--

CREATE TABLE `slab_interests` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `scheme_id` int(11) NOT NULL,
  `level1_from` int(11) NOT NULL,
  `level1_to` int(11) NOT NULL,
  `level2_from` int(11) NOT NULL,
  `level2_to` int(11) NOT NULL,
  `level3_from` int(11) NOT NULL,
  `level3_to` int(11) NOT NULL,
  `level4_from` int(11) NOT NULL,
  `level4_to` int(11) NOT NULL,
  `level5_from` int(11) NOT NULL,
  `level5_to` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `level_1_percentage` decimal(2,0) DEFAULT NULL,
  `level_2_percentage` decimal(2,0) DEFAULT NULL,
  `level_3_percentage` decimal(2,0) DEFAULT NULL,
  `level_4_percentage` decimal(2,0) DEFAULT NULL,
  `level_5_percentage` decimal(2,0) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `slab_penalties`
--

CREATE TABLE `slab_penalties` (
  `tenant_id` int(11) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `scheme_id` int(11) DEFAULT NULL,
  `level_1_from` int(11) DEFAULT NULL,
  `level_1_to` int(11) DEFAULT NULL,
  `level_2_from` int(11) DEFAULT NULL,
  `level_2_to` int(11) DEFAULT NULL,
  `level_3_From` int(11) DEFAULT NULL,
  `level_3_to` int(11) DEFAULT NULL,
  `level_4_from` int(11) DEFAULT NULL,
  `level_4_to` int(11) DEFAULT NULL,
  `level_5_from` int(11) DEFAULT NULL,
  `level_5_to` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `level_1_percentage` decimal(2,0) DEFAULT NULL,
  `level_2_percentage` decimal(2,0) DEFAULT NULL,
  `level_3_percentage` decimal(2,0) DEFAULT NULL,
  `level_4_percentage` decimal(2,0) DEFAULT NULL,
  `level_5_percentage` decimal(2,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `chit_fund_name` varchar(100) DEFAULT NULL,
  `company_logo` longtext,
  `receipts_logo` longtext,
  `header_name` varchar(100) DEFAULT NULL,
  `admin_user_id` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `gst_no` varchar(100) DEFAULT NULL,
  `authorized_person_name` varchar(100) DEFAULT NULL,
  `phone_no` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `tenant_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `email_id` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permissions_sidebar` varchar(300) NOT NULL,
  `permissions_add` varchar(300) NOT NULL,
  `permissions_edit` varchar(300) NOT NULL,
  `permissions_delete` varchar(300) NOT NULL,
  `user_type` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auction_on_holidays`
--
ALTER TABLE `auction_on_holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `bidding_details`
--
ALTER TABLE `bidding_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `group_id_idx` (`group_id`),
  ADD KEY `customer_id_idx` (`customer_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `enrollment_id_idx` (`enrollment_id`),
  ADD KEY `teanant_idx` (`tenant_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `head_office_id_idx` (`head_office_id`),
  ADD KEY `state_id_idx` (`state_id`),
  ADD KEY `district_id_idx` (`district_id`),
  ADD KEY `city_id_idx` (`city_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `business_agents`
--
ALTER TABLE `business_agents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `state_id_idx` (`state_id`),
  ADD KEY `district_id_idx` (`district_id`),
  ADD KEY `city_id_idx` (`city_id`),
  ADD KEY `designation_id_idx` (`designation_id`),
  ADD KEY `role_id_idx` (`role_id`),
  ADD KEY `bank_id_idx` (`bank_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`update_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `cash_settlements`
--
ALTER TABLE `cash_settlements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `employee_id_idx` (`employee_id`),
  ADD KEY `collection_employee_idx` (`collection_employee`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `charge_types`
--
ALTER TABLE `charge_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `chit_details`
--
ALTER TABLE `chit_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `customer_id_idx` (`customer_id`),
  ADD KEY `group_id_idx` (`group_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dist_id_idx` (`dist_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `collection_areas`
--
ALTER TABLE `collection_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `state_id_idx` (`state_id`),
  ADD KEY `district_id_idx` (`district_id`),
  ADD KEY `city_id_idx` (`city_id`),
  ADD KEY `communication_state_id_idx` (`communication_state_id`),
  ADD KEY `communication_district_id_idx` (`communication_district_id`),
  ADD KEY `communication_city_id_idx` (`communication_city_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `device_logins`
--
ALTER TABLE `device_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id_idx` (`device_id`),
  ADD KEY `user_id_idx` (`user_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_code_id_idx` (`state_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `customer_id_idx` (`customer_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `state_id_idx` (`state_id`),
  ADD KEY `district_id_idx` (`district_id`),
  ADD KEY `city_id_idx` (`city_id`),
  ADD KEY `role_id_idx` (`role_id`),
  ADD KEY `designation_id_idx` (`designation_id`),
  ADD KEY `bank_id_idx` (`bank_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `field_investigations`
--
ALTER TABLE `field_investigations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `cust_id_idx` (`cust_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `emp_id_idx` (`emp_id`),
  ADD KEY `tenant_id_idx` (`tenant_id`);

--
-- Indexes for table `file_names`
--
ALTER TABLE `file_names`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schemes_id_idx` (`schemes_id`),
  ADD KEY `fd_bank_id_idx` (`fd_bank_id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `head_offices`
--
ALTER TABLE `head_offices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_id_idx` (`state_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `cust_id_idx` (`cust_id`),
  ADD KEY `enrol_id_idx` (`enrol_id`),
  ADD KEY `employee_id_idx` (`employee_id`),
  ADD KEY `approved_by_for_notification_idx` (`approved_by_for_notification`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `group_id_idx` (`group_id`),
  ADD KEY `bidding_id_idx` (`bidding_id`),
  ADD KEY `customer_id_idx` (`customer_id`),
  ADD KEY `enrollment_id_idx` (`enrollment_id`),
  ADD KEY `employee_id_idx` (`employee_id`),
  ADD KEY `bank_id_idx` (`bank_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `payment_types`
--
ALTER TABLE `payment_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `created_by_idx` (`created_by`);

--
-- Indexes for table `petty_cash_expenses`
--
ALTER TABLE `petty_cash_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `emp_id_idx` (`emp_id`),
  ADD KEY `expenses_type_id_idx` (`expenses_type_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `petty_cash_expense_types`
--
ALTER TABLE `petty_cash_expense_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `petty_cash_incomes`
--
ALTER TABLE `petty_cash_incomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `payment_type_id_idx` (`payment_type_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `cust_id_idx` (`cust_id`),
  ADD KEY `group_id_idx` (`group_id`),
  ADD KEY `employee_id_idx` (`employee_id`),
  ADD KEY `enrol_id_idx` (`enrol_id`),
  ADD KEY `auction_id_idx` (`auction_id`),
  ADD KEY `adjust_id_idx` (`adjust_id`),
  ADD KEY `employee_branch_id_idx` (`employee_branch_id`),
  ADD KEY `payment_type_idx` (`payment_type_id`),
  ADD KEY `bank_name_idx` (`bank_name_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `relationships`
--
ALTER TABLE `relationships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `schemes`
--
ALTER TABLE `schemes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `slab_bonuses`
--
ALTER TABLE `slab_bonuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `schemes_id_idx` (`scheme_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `slab_dues`
--
ALTER TABLE `slab_dues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `schemes_id_idx` (`schemes_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `slab_interests`
--
ALTER TABLE `slab_interests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `schemes_id_idx` (`scheme_id`),
  ADD KEY `updated_by_idx` (`updated_by`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `tenant_idx` (`tenant_id`);

--
-- Indexes for table `slab_penalties`
--
ALTER TABLE `slab_penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id_idx` (`branch_id`),
  ADD KEY `scheme_id_idx` (`scheme_id`),
  ADD KEY `user_idx` (`created_by`),
  ADD KEY `user_idx1` (`updated_by`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id_idx` (`country_id`),
  ADD KEY `created_by_idx` (`created_by`),
  ADD KEY `updated_by_idx` (`updated_by`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id_idx` (`employee_id`),
  ADD KEY `tenant_idx` (`tenant_id`),
  ADD KEY `branch_id_idx` (`branch_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auction_on_holidays`
--
ALTER TABLE `auction_on_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_details`
--
ALTER TABLE `bank_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bidding_details`
--
ALTER TABLE `bidding_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `business_agents`
--
ALTER TABLE `business_agents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_settlements`
--
ALTER TABLE `cash_settlements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `charge_types`
--
ALTER TABLE `charge_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chit_details`
--
ALTER TABLE `chit_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_logins`
--
ALTER TABLE `device_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_investigations`
--
ALTER TABLE `field_investigations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file_names`
--
ALTER TABLE `file_names`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_types`
--
ALTER TABLE `payment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_expenses`
--
ALTER TABLE `petty_cash_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_expense_types`
--
ALTER TABLE `petty_cash_expense_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `petty_cash_incomes`
--
ALTER TABLE `petty_cash_incomes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relationships`
--
ALTER TABLE `relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schemes`
--
ALTER TABLE `schemes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slab_bonuses`
--
ALTER TABLE `slab_bonuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slab_dues`
--
ALTER TABLE `slab_dues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slab_interests`
--
ALTER TABLE `slab_interests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slab_penalties`
--
ALTER TABLE `slab_penalties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `auction_on_holidays`
--
ALTER TABLE `auction_on_holidays`
  ADD CONSTRAINT `auction_on_holidays_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `auction_on_holidays_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `auction_on_holidays_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `auction_on_holidays_ibfk_4` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD CONSTRAINT `bank_details_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bank_details_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bank_details_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bank_details_ibfk_4` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `bidding_details`
--
ALTER TABLE `bidding_details`
  ADD CONSTRAINT `bidding_details_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bidding_details_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bidding_details_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bidding_details_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bidding_details_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bidding_details_ibfk_6` FOREIGN KEY (`enrollment_id`) REFERENCES `chit_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `bidding_details_ibfk_7` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`head_office_id`) REFERENCES `head_offices` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `branches_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `branches_ibfk_4` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `branches_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `branches_ibfk_6` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `branches_ibfk_7` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `business_agents`
--
ALTER TABLE `business_agents`
  ADD CONSTRAINT `business_agents_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_10` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_4` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_5` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_6` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_7` FOREIGN KEY (`bank_id`) REFERENCES `bank_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `business_agents_ibfk_9` FOREIGN KEY (`update_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `cash_settlements`
--
ALTER TABLE `cash_settlements`
  ADD CONSTRAINT `cash_settlements_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cash_settlements_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cash_settlements_ibfk_3` FOREIGN KEY (`collection_employee`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cash_settlements_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cash_settlements_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cash_settlements_ibfk_6` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `charge_types`
--
ALTER TABLE `charge_types`
  ADD CONSTRAINT `charge_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `charge_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `charge_types_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `chit_details`
--
ALTER TABLE `chit_details`
  ADD CONSTRAINT `chit_details_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `chit_details_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `chit_details_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `chit_details_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `chit_details_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `chit_details_ibfk_6` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`dist_id`) REFERENCES `districts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cities_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cities_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `collection_areas`
--
ALTER TABLE `collection_areas`
  ADD CONSTRAINT `collection_areas_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `collection_areas_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `collection_areas_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `collection_areas_ibfk_4` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `countries`
--
ALTER TABLE `countries`
  ADD CONSTRAINT `countries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `countries_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_10` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_4` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_5` FOREIGN KEY (`communication_state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_6` FOREIGN KEY (`communication_district_id`) REFERENCES `districts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_7` FOREIGN KEY (`communication_city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `customers_ibfk_9` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `designations_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `designations_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `devices_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `devices_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `device_logins`
--
ALTER TABLE `device_logins`
  ADD CONSTRAINT `device_logins_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `device_logins_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `device_logins_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `device_logins_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `device_logins_ibfk_5` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `districts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `districts_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `documents_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `documents_ibfk_5` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_10` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_5` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_6` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_7` FOREIGN KEY (`bank_id`) REFERENCES `bank_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `employees_ibfk_9` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `field_investigations`
--
ALTER TABLE `field_investigations`
  ADD CONSTRAINT `field_investigations_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `field_investigations_ibfk_2` FOREIGN KEY (`cust_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `field_investigations_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `field_investigations_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `field_investigations_ibfk_5` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `field_investigations_ibfk_6` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `file_names`
--
ALTER TABLE `file_names`
  ADD CONSTRAINT `file_names_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_ibfk_1` FOREIGN KEY (`schemes_id`) REFERENCES `schemes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `groups_ibfk_2` FOREIGN KEY (`fd_bank_id`) REFERENCES `bank_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `groups_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `groups_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `groups_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `groups_ibfk_6` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `head_offices`
--
ALTER TABLE `head_offices`
  ADD CONSTRAINT `head_offices_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `head_offices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `head_offices_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `head_offices_ibfk_4` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`cust_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`enrol_id`) REFERENCES `chit_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_4` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_5` FOREIGN KEY (`approved_by_for_notification`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_6` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_7` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `notifications_ibfk_8` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_10` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`bidding_id`) REFERENCES `bidding_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_5` FOREIGN KEY (`enrollment_id`) REFERENCES `chit_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_6` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_7` FOREIGN KEY (`bank_id`) REFERENCES `bank_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_8` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payments_ibfk_9` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `payment_types`
--
ALTER TABLE `payment_types`
  ADD CONSTRAINT `payment_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `payment_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `petty_cash_expenses`
--
ALTER TABLE `petty_cash_expenses`
  ADD CONSTRAINT `petty_cash_expenses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_expenses_ibfk_2` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_expenses_ibfk_3` FOREIGN KEY (`expenses_type_id`) REFERENCES `petty_cash_expense_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_expenses_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_expenses_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `petty_cash_expense_types`
--
ALTER TABLE `petty_cash_expense_types`
  ADD CONSTRAINT `petty_cash_expense_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_expense_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `petty_cash_incomes`
--
ALTER TABLE `petty_cash_incomes`
  ADD CONSTRAINT `petty_cash_incomes_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_incomes_ibfk_2` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_incomes_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `petty_cash_incomes_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_10` FOREIGN KEY (`bank_name_id`) REFERENCES `bank_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_11` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_12` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_13` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`cust_id`) REFERENCES `customers` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_4` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_5` FOREIGN KEY (`enrol_id`) REFERENCES `chit_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_6` FOREIGN KEY (`auction_id`) REFERENCES `bidding_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_7` FOREIGN KEY (`adjust_id`) REFERENCES `chit_details` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_8` FOREIGN KEY (`employee_branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `receipts_ibfk_9` FOREIGN KEY (`payment_type_id`) REFERENCES `payment_types` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `relationships`
--
ALTER TABLE `relationships`
  ADD CONSTRAINT `relationships_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `relationships_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `roles_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `roles_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `schemes`
--
ALTER TABLE `schemes`
  ADD CONSTRAINT `schemes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `schemes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `schemes_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `slab_bonuses`
--
ALTER TABLE `slab_bonuses`
  ADD CONSTRAINT `slab_bonuses_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_bonuses_ibfk_2` FOREIGN KEY (`scheme_id`) REFERENCES `schemes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_bonuses_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_bonuses_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_bonuses_ibfk_5` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `slab_dues`
--
ALTER TABLE `slab_dues`
  ADD CONSTRAINT `slab_dues_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_dues_ibfk_2` FOREIGN KEY (`schemes_id`) REFERENCES `schemes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_dues_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_dues_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_dues_ibfk_5` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `slab_interests`
--
ALTER TABLE `slab_interests`
  ADD CONSTRAINT `slab_interests_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_interests_ibfk_2` FOREIGN KEY (`scheme_id`) REFERENCES `schemes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_interests_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_interests_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_interests_ibfk_5` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `slab_penalties`
--
ALTER TABLE `slab_penalties`
  ADD CONSTRAINT `slab_penalties_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_penalties_ibfk_2` FOREIGN KEY (`scheme_id`) REFERENCES `schemes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_penalties_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_penalties_ibfk_4` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `slab_penalties_ibfk_5` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `states_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `states_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
