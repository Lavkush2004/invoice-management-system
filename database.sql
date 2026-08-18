-- Invoice Billing System Database Schema
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `userid` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `logo_img` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `gst_no` varchar(50) DEFAULT NULL,
  `pan_no` varchar(50) DEFAULT NULL,
  `created_on` bigint(20) DEFAULT NULL,
  `updated_on` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `companies` (`userid`, `name`, `address`, `email`, `mobile`, `logo_img`, `website`, `gst_no`, `pan_no`, `created_on`, `updated_on`) VALUES ('1', 'Admin', NULL, '', NULL, NULL, NULL, NULL, NULL, '1786977795', NULL);
INSERT INTO `companies` (`userid`, `name`, `address`, `email`, `mobile`, `logo_img`, `website`, `gst_no`, `pan_no`, `created_on`, `updated_on`) VALUES ('3', 'Admin', '', '', '', NULL, '', '', '', '1786977716', '1787047744');
INSERT INTO `companies` (`userid`, `name`, `address`, `email`, `mobile`, `logo_img`, `website`, `gst_no`, `pan_no`, `created_on`, `updated_on`) VALUES ('4', 'kk singh', NULL, 'admin@gmail.com', 'kk singh', NULL, NULL, NULL, NULL, '1786977917', NULL);
INSERT INTO `companies` (`userid`, `name`, `address`, `email`, `mobile`, `logo_img`, `website`, `gst_no`, `pan_no`, `created_on`, `updated_on`) VALUES ('11', 'First Trade', 'Demo Address', 'vendor@example.com', '9876543210', '', '', '', '', '1786810375', '1786810375');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `cus_id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `cus_name` varchar(150) DEFAULT NULL,
  `cus_address` text DEFAULT NULL,
  `cus_email` varchar(150) DEFAULT NULL,
  `cus_mobile` varchar(20) DEFAULT NULL,
  `cus_password` varchar(255) DEFAULT NULL,
  `cus_status` tinyint(4) DEFAULT 1,
  `cus_created_on` bigint(20) DEFAULT NULL,
  `cus_updated_on` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`cus_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('1', '11', 'Jonny Deen', 'Demo Customer Address', 'customer@example.com', '9999999999', '1234', '1', '1786810375', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('2', '11', 'Test Customer', 'Address Save Check', '', '9991112223', '123456', '1', '1786811618', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('3', '11', 'Luv Rajput', 'Bhalswa Dairy, Delhi 110048', '', '09871675957', '123456', '1', '1786811650', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('4', '1', 'Test Customer', 'Test Address, City', '', '9876543210', '123456', '1', '1786824346', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('5', '1', 'Automation Customer', 'Automation Address', '', '7778889991', '123456', '1', '1786825755', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('6', NULL, 'OTP Customer', NULL, NULL, '7290938849', '123456', '1', '1786887957', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('7', NULL, 'OTP Customer', NULL, NULL, '+917290938849', '123456', '1', '1786960232', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('8', NULL, 'Luv Rajput', NULL, 'cleardom18@gmail.com', '9871675957', 'admin', '1', '1786975283', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('9', '4', 'rajeev kumar singh', 'Bhalswa Dairy, Delhi 110048', '', '09871675957', '123456', '1', '1787048363', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('10', '3', 'Luv Rajput', 'Bhalswa Dairy, Delhi 110048', '', '098786875', '123456', '1', '1787049141', NULL);
INSERT INTO `customers` (`cus_id`, `company_id`, `cus_name`, `cus_address`, `cus_email`, `cus_mobile`, `cus_password`, `cus_status`, `cus_created_on`, `cus_updated_on`) VALUES ('11', '4', 'simon', 'Bhalswa Dairy, Delhi 110048', '', '888822345', '123456', '1', '1787049351', NULL);

DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comp_id` int(11) DEFAULT NULL,
  `cust_id` int(11) DEFAULT NULL,
  `total_amt` decimal(10,2) DEFAULT NULL,
  `created_date` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('1', '11', '0', '0.00', '1786810522');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('2', '11', '1', '0.00', '1786810617');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('3', '11', '3', '283.20', '1786811673');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('4', '11', '3', '814.20', '1786823872');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('5', '11', '1', '0.00', '1786823889');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('6', '11', '2', '0.00', '1786823910');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('7', '1', '1', '100.00', '1786824265');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('8', '1', '4', '236.00', '1786824353');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('9', '11', '1', '354.00', '1786824486');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('10', '11', '2', '1911.60', '1786824558');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('11', '11', '2', '14453.10', '1786825464');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('12', '1', '4', '2708.00', '1786980493');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('13', '4', '9', '2017.80', '1787048389');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('14', '3', '10', '7882.40', '1787049193');
INSERT INTO `invoice` (`id`, `comp_id`, `cust_id`, `total_amt`, `created_date`) VALUES ('15', '4', '11', '63.72', '1787049373');

DROP TABLE IF EXISTS `invoice_details`;
CREATE TABLE `invoice_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inv_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `gst` decimal(10,2) DEFAULT NULL,
  `total_amt` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('1', '3', 'shampoo', '120.00', '2.00', '18.00', '283.20');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('2', '4', 'shampoo', '345.00', '2.00', '18.00', '814.20');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('3', '5', '', '0.00', '0.00', '0.00', '0.00');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('4', '6', '', '0.00', '0.00', '0.00', '0.00');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('5', '7', 'Test Product', '100.00', '1.00', '0.00', '100.00');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('6', '8', 'Test Product', '100.00', '2.00', '18.00', '236.00');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('7', '9', 'Product Test', '150.00', '2.00', '18.00', '354.00');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('8', '10', 'soap', '30.00', '54.00', '18.00', '1911.60');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('9', '11', 'shampoo', '3.00', '4545.00', '6.00', '14453.10');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('10', '12', 'shampoo', '677.00', '4.00', '0.00', '2708.00');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('11', '13', 'shampoo', '342.00', '5.00', '18.00', '2017.80');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('12', '14', 'shampoo', '668.00', '10.00', '18.00', '7882.40');
INSERT INTO `invoice_details` (`id`, `inv_id`, `product_name`, `price`, `quantity`, `gst`, `total_amt`) VALUES ('13', '15', 'collgate', '54.00', '1.00', '18.00', '63.72');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` tinyint(4) DEFAULT 2,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`) VALUES ('1', 'admin', 'admin@example.com', 'admin123', '2', '1', '2026-08-15 21:42:55');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`) VALUES ('2', 'cleardom18@gmail.com', 'cleardom18@gmail.com', 'admin', '1', '1', '2026-08-16 19:19:21');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`) VALUES ('3', 'lkpowerhub567@gmail.com', 'lkpowerhub567@gmail.com', 'admin', '1', '1', '2026-08-17 20:08:51');
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`) VALUES ('4', 'admin@gmail.com', 'admin@gmail.com', 'admin', '2', '1', '2026-08-17 20:15:17');

SET FOREIGN_KEY_CHECKS = 1;
