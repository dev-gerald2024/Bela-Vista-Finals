-- ============================================================================
-- Bella Vista Oasis Spa Management System
-- Complete Database Schema & Seed Data
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `bella_vista_spa`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `bella_vista_spa`;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- Drop existing tables to allow clean re-runs
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `employee_schedules`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `website_contents`;
DROP TABLE IF EXISTS `inquiries`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `loyalty_points`;
DROP TABLE IF EXISTS `vouchers`;
DROP TABLE IF EXISTS `promotions`;
DROP TABLE IF EXISTS `expenses`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `inventory`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `refunds`;
DROP TABLE IF EXISTS `receipts`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `payment_methods`;
DROP TABLE IF EXISTS `health_declarations`;
DROP TABLE IF EXISTS `cleaning_schedules`;
DROP TABLE IF EXISTS `room_schedules`;
DROP TABLE IF EXISTS `therapist_assignments`;
DROP TABLE IF EXISTS `appointment_guests`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `therapists`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- 1. USERS
-- ============================================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'manager', 'front_desk', 'therapist', 'customer') NOT NULL DEFAULT 'customer',
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_is_active` (`is_active`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. CUSTOMERS
-- ============================================================================
CREATE TABLE `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `date_of_birth` DATE NULL,
    `gender` ENUM('male', 'female', 'other', 'prefer_not_to_say') NULL DEFAULT 'prefer_not_to_say',
    `address` TEXT NULL,
    `emergency_contact_name` VARCHAR(150) NULL,
    `emergency_contact_phone` VARCHAR(30) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_customers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. EMPLOYEES
-- ============================================================================
CREATE TABLE `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `position` VARCHAR(100) NOT NULL,
    `department` VARCHAR(100) NOT NULL,
    `date_hired` DATE NOT NULL,
    `salary` DECIMAL(10,2) NULL DEFAULT 0.00,
    `employment_status` ENUM('active', 'inactive', 'terminated') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_employees_status` (`employment_status`),
    INDEX `idx_employees_dept` (`department`),
    CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. THERAPISTS
-- ============================================================================
CREATE TABLE `therapists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL UNIQUE,
    `specialization` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `photo_url` VARCHAR(255) NULL,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1,
    `rating_avg` DECIMAL(3,2) NOT NULL DEFAULT 5.00,
    `total_reviews` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_therapists_available` (`is_available`),
    CONSTRAINT `fk_therapists_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. SERVICES
-- ============================================================================
CREATE TABLE `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NOT NULL,
    `duration_minutes` INT NOT NULL DEFAULT 60,
    `base_price` DECIMAL(10,2) NOT NULL,
    `max_guests` INT NOT NULL DEFAULT 1,
    `additional_guest_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `room_type` ENUM('single', 'couples', 'family') NOT NULL DEFAULT 'single',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `image_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_services_category` (`category`),
    INDEX `idx_services_room_type` (`room_type`),
    INDEX `idx_services_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. ROOMS
-- ============================================================================
CREATE TABLE `rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `room_type` ENUM('single', 'couples_suite', 'family_lounge') NOT NULL,
    `capacity` INT NOT NULL DEFAULT 1,
    `floor` VARCHAR(20) NOT NULL DEFAULT '1st Floor',
    `status` ENUM('available', 'occupied', 'cleaning', 'maintenance') NOT NULL DEFAULT 'available',
    `description` TEXT NULL,
    `amenities` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_rooms_type` (`room_type`),
    INDEX `idx_rooms_status` (`status`),
    INDEX `idx_rooms_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. APPOINTMENTS
-- ============================================================================
CREATE TABLE `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `service_id` INT NOT NULL,
    `room_id` INT NOT NULL,
    `appointment_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `guest_count` INT NOT NULL DEFAULT 1,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `additional_guest_charges` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show') NOT NULL DEFAULT 'pending',
    `notes` TEXT NULL,
    `preferred_therapist_id` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_appointments_date_time` (`appointment_date`, `start_time`, `end_time`),
    INDEX `idx_appointments_status` (`status`),
    INDEX `idx_appointments_customer` (`customer_id`),
    INDEX `idx_appointments_room` (`room_id`),
    INDEX `idx_appointments_service` (`service_id`),
    CONSTRAINT `fk_appointments_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_appointments_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_appointments_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_appointments_therapist` FOREIGN KEY (`preferred_therapist_id`) REFERENCES `therapists` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. APPOINTMENT_GUESTS
-- ============================================================================
CREATE TABLE `appointment_guests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `guest_name` VARCHAR(150) NOT NULL,
    `guest_email` VARCHAR(191) NULL,
    `guest_phone` VARCHAR(30) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_guests_appointment` (`appointment_id`),
    CONSTRAINT `fk_guests_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. THERAPIST_ASSIGNMENTS
-- ============================================================================
CREATE TABLE `therapist_assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `therapist_id` INT NOT NULL,
    `assigned_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('assigned', 'completed', 'reassigned') NOT NULL DEFAULT 'assigned',
    INDEX `idx_assignments_appointment` (`appointment_id`),
    INDEX `idx_assignments_therapist` (`therapist_id`),
    CONSTRAINT `fk_assignments_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_assignments_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. ROOM_SCHEDULES
-- ============================================================================
CREATE TABLE `room_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `appointment_id` INT NULL,
    `date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `includes_cleaning` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_room_schedules_lookup` (`room_id`, `date`, `start_time`, `end_time`),
    CONSTRAINT `fk_room_schedules_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_room_schedules_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. CLEANING_SCHEDULES
-- ============================================================================
CREATE TABLE `cleaning_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `appointment_id` INT NULL,
    `scheduled_start` TIME NOT NULL,
    `scheduled_end` TIME NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('awaiting_cleaning', 'in_progress', 'completed') NOT NULL DEFAULT 'awaiting_cleaning',
    `cleaned_by` INT NULL,
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cleaning_room_date` (`room_id`, `date`),
    INDEX `idx_cleaning_status` (`status`),
    CONSTRAINT `fk_cleaning_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cleaning_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_cleaning_employee` FOREIGN KEY (`cleaned_by`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. HEALTH_DECLARATIONS
-- ============================================================================
CREATE TABLE `health_declarations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `has_medical_conditions` TINYINT(1) NOT NULL DEFAULT 0,
    `medical_conditions_detail` TEXT NULL,
    `has_injuries` TINYINT(1) NOT NULL DEFAULT 0,
    `injuries_detail` TEXT NULL,
    `is_pregnant` TINYINT(1) NOT NULL DEFAULT 0,
    `pregnancy_weeks` INT NULL DEFAULT NULL,
    `has_allergies` TINYINT(1) NOT NULL DEFAULT 0,
    `allergies_detail` TEXT NULL,
    `additional_notes` TEXT NULL,
    `declared_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_acknowledged` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_health_appt` (`appointment_id`),
    INDEX `idx_health_customer` (`customer_id`),
    CONSTRAINT `fk_health_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_health_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. PAYMENT_METHODS
-- ============================================================================
CREATE TABLE `payment_methods` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 14. PAYMENTS
-- ============================================================================
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `appointment_id` INT NOT NULL,
    `customer_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_type` ENUM('downpayment', 'full_payment', 'balance') NOT NULL DEFAULT 'full_payment',
    `payment_method_id` INT NOT NULL,
    `status` ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    `transaction_reference` VARCHAR(100) NULL,
    `paid_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_payments_appt` (`appointment_id`),
    INDEX `idx_payments_cust` (`customer_id`),
    INDEX `idx_payments_status` (`status`),
    CONSTRAINT `fk_payments_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_payments_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_payments_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 15. RECEIPTS
-- ============================================================================
CREATE TABLE `receipts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `payment_id` INT NOT NULL UNIQUE,
    `receipt_number` VARCHAR(50) NOT NULL UNIQUE,
    `issued_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `details` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_receipts_number` (`receipt_number`),
    CONSTRAINT `fk_receipts_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 16. REFUNDS
-- ============================================================================
CREATE TABLE `refunds` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `payment_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('requested', 'approved', 'processed', 'rejected') NOT NULL DEFAULT 'requested',
    `requested_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `processed_at` DATETIME NULL,
    `processed_by` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_refunds_status` (`status`),
    CONSTRAINT `fk_refunds_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_refunds_processor` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 17. NOTIFICATIONS
-- ============================================================================
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('booking', 'payment', 'reminder', 'system', 'promotion') NOT NULL DEFAULT 'system',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `link` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notifications_user_read` (`user_id`, `is_read`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 18. SUPPLIERS
-- ============================================================================
CREATE TABLE `suppliers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(150) NOT NULL,
    `contact_person` VARCHAR(100) NULL,
    `email` VARCHAR(191) NULL,
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `notes` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 19. INVENTORY
-- ============================================================================
CREATE TABLE `inventory` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NOT NULL,
    `sku` VARCHAR(50) NOT NULL UNIQUE,
    `quantity` INT NOT NULL DEFAULT 0,
    `unit` VARCHAR(20) NOT NULL DEFAULT 'pcs',
    `reorder_level` INT NOT NULL DEFAULT 10,
    `unit_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `supplier_id` INT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_inventory_sku` (`sku`),
    INDEX `idx_inventory_category` (`category`),
    CONSTRAINT `fk_inventory_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 20. STOCK_MOVEMENTS
-- ============================================================================
CREATE TABLE `stock_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inventory_id` INT NOT NULL,
    `type` ENUM('stock_in', 'stock_out', 'adjustment') NOT NULL,
    `quantity` INT NOT NULL,
    `reference_note` TEXT NULL,
    `performed_by` INT NOT NULL,
    `movement_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_movements_inventory` (`inventory_id`),
    CONSTRAINT `fk_movements_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_movements_user` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 21. EXPENSES
-- ============================================================================
CREATE TABLE `expenses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(100) NOT NULL,
    `description` TEXT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `expense_date` DATE NOT NULL,
    `recorded_by` INT NOT NULL,
    `receipt_url` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_expenses_date` (`expense_date`),
    INDEX `idx_expenses_category` (`category`),
    CONSTRAINT `fk_expenses_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 22. PROMOTIONS
-- ============================================================================
CREATE TABLE `promotions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    `discount_value` DECIMAL(10,2) NOT NULL,
    `min_booking_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `promo_code` VARCHAR(50) NOT NULL UNIQUE,
    `max_uses` INT NOT NULL DEFAULT 100,
    `current_uses` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_promotions_code` (`promo_code`),
    INDEX `idx_promotions_active` (`is_active`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 23. VOUCHERS
-- ============================================================================
CREATE TABLE `vouchers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('gift_certificate', 'discount_voucher') NOT NULL,
    `value` DECIMAL(10,2) NOT NULL,
    `balance` DECIMAL(10,2) NOT NULL,
    `issued_to` INT NULL,
    `issued_by` INT NOT NULL,
    `valid_from` DATE NOT NULL,
    `valid_until` DATE NOT NULL,
    `is_redeemed` TINYINT(1) NOT NULL DEFAULT 0,
    `redeemed_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_vouchers_code` (`code`),
    CONSTRAINT `fk_vouchers_customer` FOREIGN KEY (`issued_to`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_vouchers_issuer` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 24. LOYALTY_POINTS
-- ============================================================================
CREATE TABLE `loyalty_points` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `points` INT NOT NULL,
    `transaction_type` ENUM('earned', 'redeemed', 'expired', 'adjusted') NOT NULL,
    `reference_type` VARCHAR(50) NULL,
    `reference_id` INT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_loyalty_customer` (`customer_id`),
    CONSTRAINT `fk_loyalty_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 25. REVIEWS
-- ============================================================================
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `appointment_id` INT NULL,
    `therapist_id` INT NULL,
    `rating` INT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `comment` TEXT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `admin_response` TEXT NULL,
    `responded_at` DATETIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_reviews_customer` (`customer_id`),
    INDEX `idx_reviews_therapist` (`therapist_id`),
    INDEX `idx_reviews_rating` (`rating`),
    CONSTRAINT `fk_reviews_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_therapist` FOREIGN KEY (`therapist_id`) REFERENCES `therapists` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 26. INQUIRIES
-- ============================================================================
CREATE TABLE `inquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `status` ENUM('new', 'read', 'replied', 'closed') NOT NULL DEFAULT 'new',
    `admin_reply` TEXT NULL,
    `replied_at` DATETIME NULL,
    `replied_by` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_inquiries_status` (`status`),
    CONSTRAINT `fk_inquiries_admin` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 27. WEBSITE_CONTENTS
-- ============================================================================
CREATE TABLE `website_contents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section_key` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(200) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta_description` TEXT NULL,
    `image_url` VARCHAR(255) NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_by` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_contents_key` (`section_key`),
    CONSTRAINT `fk_contents_editor` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 28. ATTENDANCE
-- ============================================================================
CREATE TABLE `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `time_in` TIME NULL,
    `time_out` TIME NULL,
    `status` ENUM('present', 'absent', 'late', 'half_day', 'leave') NOT NULL DEFAULT 'present',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_emp_date` (`employee_id`, `date`),
    INDEX `idx_attendance_date` (`date`),
    CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 29. EMPLOYEE_SCHEDULES
-- ============================================================================
CREATE TABLE `employee_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` INT NOT NULL,
    `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    `shift_start` TIME NOT NULL,
    `shift_end` TIME NOT NULL,
    `is_day_off` TINYINT(1) NOT NULL DEFAULT 0,
    `effective_from` DATE NOT NULL,
    `effective_until` DATE NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_schedules_employee` (`employee_id`, `day_of_week`),
    CONSTRAINT `fk_schedules_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 30. AUDIT_LOGS
-- ============================================================================
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(255) NOT NULL,
    `table_name` VARCHAR(100) NULL,
    `record_id` INT NULL,
    `old_values` LONGTEXT NULL,
    `new_values` LONGTEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_table_record` (`table_name`, `record_id`),
    INDEX `idx_audit_action` (`action`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 31. SYSTEM_SETTINGS
-- ============================================================================
CREATE TABLE `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NOT NULL,
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `description` TEXT NULL,
    `updated_by` INT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_key` (`setting_key`),
    INDEX `idx_settings_group` (`setting_group`),
    CONSTRAINT `fk_settings_updater` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 32. PASSWORD_RESETS
-- ============================================================================
CREATE TABLE `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `expires_at` DATETIME NOT NULL,
    `is_used` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_password_resets_token` (`token`),
    INDEX `idx_password_resets_user` (`user_id`),
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA INSERTION
-- ============================================================================

-- ----------------------------------------------------------------------------
-- System Settings
-- ----------------------------------------------------------------------------
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('operating_hours_start', '13:00', 'operations', 'Spa opening time in 24-hour format (1:00 PM)'),
('operating_hours_end', '21:00', 'operations', 'Spa closing time in 24-hour format (9:00 PM)'),
('cleaning_gap_minutes', '30', 'operations', 'Sanitization and room preparation buffer between sessions in minutes'),
('cancellation_hours', '6', 'booking', 'Minimum cancellation lead time in hours before scheduled slot'),
('arrival_minutes_before', '15', 'booking', 'Recommended guest arrival time prior to appointment'),
('currency', 'PHP', 'financial', 'Standard operational currency code'),
('currency_symbol', '₱', 'financial', 'Display currency symbol'),
('site_name', 'Bella Vista Oasis Spa', 'general', 'Official brand name'),
('site_tagline', 'The Pinnacle of Holistic Serenity and Luxury Wellness', 'general', 'Brand slogan'),
('contact_email', 'info@bellavistaspa.com', 'contact', 'Public inquiry email address'),
('contact_phone', '+63 (02) 8876-5432', 'contact', 'Direct concierge telephone line'),
('spa_address', 'Bella Vista Oasis Luxury Resort, Tagaytay-Nasugbu Highway, Tagaytay City, Cavite, Philippines', 'contact', 'Physical destination address'),
('downpayment_percentage', '20', 'financial', 'Default booking deposit percentage');

-- ----------------------------------------------------------------------------
-- Payment Methods
-- ----------------------------------------------------------------------------
INSERT INTO `payment_methods` (`name`, `description`, `is_active`) VALUES
('Cash', 'Over-the-counter physical Philippine Peso payment upon concierge arrival', 1),
('GCash', 'Instant mobile wallet QR payment and express transfer', 1),
('Maya', 'Maya digital wallet scan-to-pay and virtual card checkout', 1),
('Credit Card', 'Visa, Mastercard, JCB, and American Express processed securely', 1),
('Debit Card', 'BancNet and international ATM debit card terminals', 1),
('Bank Transfer', 'Direct corporate wire/online transfer to BDO, BPI, or UnionBank', 1);

-- ----------------------------------------------------------------------------
-- Rooms
-- 3 single rooms, 2 couples suites, 1 family lounge
-- ----------------------------------------------------------------------------
INSERT INTO `rooms` (`name`, `room_type`, `capacity`, `floor`, `status`, `description`, `amenities`) VALUES
('Room 101 - Lotus Sanctuary', 'single', 1, '1st Floor', 'available', 'A quiet, minimalist retreat lined with fragrant cedarwood, ambient salt lamps, and acoustic isolation for ultimate personal peace.', 'Plush Hydraulic Spa Bed, Private Dressing Screen, Aroma Diffuser, Soundscape Control'),
('Room 102 - Orchid Haven', 'single', 1, '1st Floor', 'available', 'An intimate sanctuary bathed in warm champagne lighting, designed for intensive therapeutic bodywork and peaceful restoration.', 'Memory-Foam Heated Massage Table, Ensuite Shower, Essential Oil Station, Heated Towel Warmer'),
('Room 103 - Zen Blossom', 'single', 1, '1st Floor', 'available', 'An earthy haven drawing from Japanese onsen aesthetics, featuring bamboo accents and soothing waterfall acoustics.', 'Solid Oak Bodywork Table, Dimmable Chandeliers, Private Foot Bath Basin, Calming Sound System'),
('Suite 201 - Amour Harmony Suite', 'couples_suite', 2, '2nd Floor', 'available', 'An opulent couples suite featuring twin deluxe beds, a freestanding terrazzo hydrotherapy tub, and complimentary herbal refreshments.', 'Dual Heated Massage Beds, Twin Rain Showers, Hydrotherapy Tub, Ambient Fireplace, Dedicated Refreshment Bar'),
('Suite 202 - Seraphic Twin Suite', 'couples_suite', 2, '2nd Floor', 'available', 'A romantic haven offering side-by-side treatments, silk robes, soft mood lighting, and panoramic garden courtyard views.', 'Twin Memory-Foam Tables, Rose Petal Foot Basin Station, Ensuite Marble Bath, Private Balcony Lounge'),
('Lounge 301 - Imperial Oasis Lounge', 'family_lounge', 6, '3rd Floor', 'available', 'A grand private suite curated for wellness parties and family gatherings. Accommodates up to 6 guests with plush treatment recliners, hydro-lounge, and dedicated butler service.', '4 to 6 Modular Spa Treatment Recliners, Private Hydro-Jet Spa Pool, Herbal Tea Bar, Smart Entertainment Display, Twin Changing Rooms');

-- ----------------------------------------------------------------------------
-- Services
-- Single Room Massage (₱800, 60 min, 1 guest)
-- Couples Suite (₱1,800, 60 min, 2 guests)
-- Family Lounge (₱4,000, 90 min, up to 4 guests included, max 6, ₱750/extra guest)
-- Additional signature offerings
-- ----------------------------------------------------------------------------
INSERT INTO `services` (`name`, `description`, `category`, `duration_minutes`, `base_price`, `max_guests`, `additional_guest_fee`, `room_type`, `is_active`, `image_url`) VALUES
('Single Room Massage', 'Indulge in a personalized 60-minute signature therapeutic massage tailored to relieve deep chronic tension, balance energy meridians, and restore holistic vitality with pure botanic oils.', 'Signature Massage', 60, 800.00, 1, 0.00, 'single', 1, 'assets/img/services/single-massage.jpg'),
('Couples Suite Retreat', 'Share a blissful 60-minute synchronized full-body massage in an exclusive private suite. Includes soothing foot ritual, warm aromatherapy compress, and artisan herbal infusion.', 'Couples Wellness', 60, 1800.00, 2, 0.00, 'couples', 1, 'assets/img/services/couples-massage.jpg'),
('Family Lounge Gathering', 'Exclusive 90-minute wellness celebration for your group in our lavish Imperial Lounge. Includes full-body therapeutic treatments for up to 4 guests (expandable up to 6 guests at ₱750 per additional guest), private hydro-lounge access, and hot herbal elixir service.', 'Group & Family', 90, 4000.00, 6, 750.00, 'family', 1, 'assets/img/services/family-lounge.jpg'),
('Aromatherapy Deep Tissue Release', 'Deep targeted muscle manipulation combined with bespoke therapeutic organic essential oils to eliminate knotting, relieve posture strain, and restore deep mobility.', 'Therapeutic Specialty', 90, 1200.00, 1, 0.00, 'single', 1, 'assets/img/services/deep-tissue.jpg'),
('Himalayan Hot Stone Indulgence', 'Polished warm basalt stones glide smoothly along energy channels to penetrate tired muscles, induce meditative bliss, and release locked stress.', 'Thermal Healing', 75, 1400.00, 1, 0.00, 'single', 1, 'assets/img/services/hot-stone.jpg'),
('Ventosa Cupping & Traditional Hilot', 'Ancient Philippine Hilot healing combined with gentle glass cupping to extract trapped stagnation, stimulate immune response, and soothe back stiffness.', 'Traditional Holistic', 75, 1100.00, 1, 0.00, 'single', 1, 'assets/img/services/ventosa-hilot.jpg');

-- ----------------------------------------------------------------------------
-- Users & Staff
-- Default Admin: admin@bellavistaspa.com / Password: Admin@123
-- Hash: $2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi
-- Note: A legacy bcrypt hash $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi corresponds to "password".
-- Here we provide Admin@123 as the pre-configured password for admin@bellavistaspa.com!
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `phone`, `is_active`, `email_verified`) VALUES
(1, 'admin@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'admin', 'Victoria', 'Montenegro', '+63 917 100 0001', 1, 1),
(2, 'manager@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'manager', 'Julian', 'Alcantara', '+63 917 100 0002', 1, 1),
(3, 'frontdesk@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'front_desk', 'Camille', 'Santos', '+63 917 100 0003', 1, 1),
(4, 'therapist.elena@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'therapist', 'Elena', 'Reyes', '+63 917 100 0004', 1, 1),
(5, 'therapist.marco@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'therapist', 'Marco', 'Valdez', '+63 917 100 0005', 1, 1),
(6, 'therapist.sofia@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'therapist', 'Sofia', 'Mendoza', '+63 917 100 0006', 1, 1),
(7, 'customer@bellavistaspa.com', '$2y$10$DBg6HIG0YyS.l1/pW8kaCeG3esSGsZxpQ/cR6gVnJvKRedTc3F4xi', 'customer', 'Maria', 'Clara', '+63 918 222 3344', 1, 1);

-- ----------------------------------------------------------------------------
-- Employees
-- ----------------------------------------------------------------------------
INSERT INTO `employees` (`id`, `user_id`, `position`, `department`, `date_hired`, `salary`, `employment_status`) VALUES
(1, 1, 'General Manager & Director', 'Executive Management', '2023-01-15', 85000.00, 'active'),
(2, 2, 'Operations Manager', 'Spa Operations', '2023-03-01', 55000.00, 'active'),
(3, 3, 'Lead Concierge', 'Guest Relations', '2023-06-10', 28000.00, 'active'),
(4, 4, 'Senior Holistic Therapist', 'Therapy & Wellness', '2023-02-01', 32000.00, 'active'),
(5, 5, 'Master Bodywork Specialist', 'Therapy & Wellness', '2023-04-15', 34000.00, 'active'),
(6, 6, 'Aromatherapist & Reflexologist', 'Therapy & Wellness', '2023-07-01', 30000.00, 'active');

-- ----------------------------------------------------------------------------
-- Therapists
-- ----------------------------------------------------------------------------
INSERT INTO `therapists` (`id`, `employee_id`, `specialization`, `bio`, `photo_url`, `is_available`, `rating_avg`, `total_reviews`) VALUES
(1, 4, 'Swedish Massage, Deep Tissue & Hilot Healing', 'Certified master therapist with over 9 years of luxury resort spa experience. Specializes in relieving postural knots and custom aromatherapy blends.', 'assets/img/therapists/elena.jpg', 1, 4.95, 38),
(2, 5, 'Himalayan Hot Stone & Sports Neuromuscular Therapy', 'Accredited physical therapy practitioner specializing in deep neuromuscular release, athletic recovery, and therapeutic heat alignment.', 'assets/img/therapists/marco.jpg', 1, 4.90, 29),
(3, 6, 'Reflexology, Lymphatic Drainage & Prenatal Care', 'Trained internationally in organic botanic wellness, focusing on gentle fluid circulation, prenatal soothing techniques, and eastern acupressure.', 'assets/img/therapists/sofia.jpg', 1, 4.88, 24);

-- ----------------------------------------------------------------------------
-- Customers
-- ----------------------------------------------------------------------------
INSERT INTO `customers` (`id`, `user_id`, `date_of_birth`, `gender`, `address`, `emergency_contact_name`, `emergency_contact_phone`, `notes`) VALUES
(1, 7, '1992-05-18', 'female', 'Unit 12B, Horizon Towers, Bonifacio Global City, Taguig', 'Crisostomo Ibarra', '+63 917 888 9900', 'Prefers medium-to-firm pressure and organic lavender-infused massage oils.');

-- ----------------------------------------------------------------------------
-- Suppliers
-- ----------------------------------------------------------------------------
INSERT INTO `suppliers` (`company_name`, `contact_person`, `email`, `phone`, `address`, `notes`, `is_active`) VALUES
('Botanica Pure Organics Corp.', 'Therese Laurel', 'orders@botanicapure.ph', '+63 (02) 8654-1122', 'San Juan City, Metro Manila', 'Certified organic virgin coconut oil, lavender, eucalyptus, and lemongrass essences', 1),
('Aura Spa Linens & Robes Co.', 'Ramon Bautista', 'sales@auralinens.com', '+63 (02) 8991-4433', 'Valenzuela City, Metro Manila', 'Supplier of 800 GSM plush Egyptian cotton towels, bath sheets, and luxury silk robes', 1),
('Zenith Thermal Wellness Gear', 'Arlene Tan', 'support@zenithwellness.ph', '+63 (02) 8332-9988', 'Makati City, Metro Manila', 'Basalt hot massage stones, digital heating units, and sanitizing UV chambers', 1);

-- ----------------------------------------------------------------------------
-- Inventory
-- ----------------------------------------------------------------------------
INSERT INTO `inventory` (`name`, `description`, `category`, `sku`, `quantity`, `unit`, `reorder_level`, `unit_cost`, `supplier_id`, `is_active`) VALUES
('Organic Lavender Massage Oil 1L', 'Cold-pressed virgin coconut base infused with French organic lavender essential oil', 'Massage Oils', 'OIL-LAV-001', 45, 'bottles', 10, 650.00, 1, 1),
('Eucalyptus Mint Tension Blend 1L', 'Invigorating muscle therapy blend of eucalyptus globulus and cooling peppermint', 'Massage Oils', 'OIL-EUC-002', 32, 'bottles', 8, 680.00, 1, 1),
('Lemongrass Ginger Warming Oil 1L', 'Traditional warming blend formulated for Hilot and deep joint relief', 'Massage Oils', 'OIL-LEM-003', 28, 'bottles', 8, 620.00, 1, 1),
('Polished Basalt Hot Stone Set (24 pcs)', 'Volcanic basalt stones sculpted for thermal stone therapy', 'Spa Equipment', 'EQP-STN-024', 8, 'sets', 2, 3500.00, 3, 1),
('Egyptian Cotton Bath Towel 800 GSM', 'Ultra-absorbent luxury charcoal-grey bath towels', 'Linens', 'LIN-TWL-800', 120, 'pcs', 30, 480.00, 2, 1),
('Silk Velvet Lounge Robe (Unisex)', 'Embroidered Bella Vista gold-crested luxury spa robe', 'Linens', 'LIN-ROB-GLD', 65, 'pcs', 15, 1250.00, 2, 1);

-- ----------------------------------------------------------------------------
-- Promotions
-- ----------------------------------------------------------------------------
INSERT INTO `promotions` (`title`, `description`, `discount_type`, `discount_value`, `min_booking_amount`, `start_date`, `end_date`, `promo_code`, `max_uses`, `current_uses`, `is_active`) VALUES
('Grand Oasis Welcome', 'Enjoy a 15% luxury discount on your inaugural holistic spa session', 'percentage', 15.00, 800.00, '2024-01-01', '2026-12-31', 'WELCOME15', 500, 14, 1),
('Couples Sanctuary Special', 'Receive ₱300 off any signature Couples Suite wellness booking', 'fixed', 300.00, 1800.00, '2024-02-01', '2026-12-31', 'COUPLES300', 200, 22, 1),
('Family Serenity Package', 'Enjoy ₱500 off our premier Imperial Family Lounge booking', 'fixed', 500.00, 4000.00, '2024-01-01', '2026-12-31', 'FAMILY500', 100, 8, 1);

-- ----------------------------------------------------------------------------
-- Website Content Sections
-- ----------------------------------------------------------------------------
INSERT INTO `website_contents` (`section_key`, `title`, `content`, `meta_description`, `image_url`, `is_published`) VALUES
('hero', 'Sanctuary of Refined Serenity and Restorative Luxury', 'Enter an exquisite world of transcendent wellness. At Bella Vista Oasis Spa, ancestral healing traditions harmonize with bespoke modern luxury to rejuvenate your body, calm your spirit, and awaken peace in our serene mountain oasis.', 'Experience unparalleled relaxation and luxury wellness treatments at Bella Vista Oasis Spa.', 'assets/img/hero-spa.jpg', 1),
('about', 'The Art of Holistic Restoration', 'Perched in the tranquil hills of Tagaytay, Bella Vista Oasis Spa was conceived as an intimate sanctuary for discerning individuals seeking true physical rejuvenation and mental clarity. Every touch, fragrance, and harmonic frequency is carefully orchestrated by certified holistic master therapists to elevate personal well-being.', 'Discover the artisanal philosophy and healing ethos behind Bella Vista Oasis Spa.', 'assets/img/about-spa.jpg', 1),
('mission', 'Our Guiding Mission', 'To deliver transcendent, restorative wellness journeys through uncompromising clinical sanitation, masterful personalized therapies, and bespoke hospitality that honours mind, body, and human spirit.', 'Our mission to redefine luxury wellness with intentional care.', NULL, 1),
('vision', 'Our Vision', 'To stand as the premier haven of holistic luxury and restorative therapy in Southeast Asia, celebrated for artisan healing rituals, ethical practices, and exceptional guest rejuvenation.', 'The visionary future of Bella Vista Oasis Spa.', NULL, 1),
('history', 'A Heritage Born of Mountain Tranquility', 'Founded in 2022 by wellness artisans inspired by the ancient geothermal springs of the Orient and the soothing breezes of Tagaytay, Bella Vista Oasis Spa opened its doors with three private treatment sanctuaries. Today, the estate features six bespoke suites including our renowned Imperial Family Lounge and couples hydrotherapy sanctuaries, remaining dedicated to exclusivity, quiet reflection, and transformative treatments.', 'The founding legacy of Bella Vista Oasis Spa.', 'assets/img/history-spa.jpg', 1),
('privacy_policy', 'Guest Privacy & Medical Data Protection', '<h2>Privacy Policy</h2><p>At Bella Vista Oasis Spa, your privacy and health confidentiality are sacred. This policy outlines how we safeguard personal identification, appointment schedules, and confidential health disclosures.</p><h3>1. Information Collected</h3><p>We gather essential information needed to personalize and safely deliver your treatments, including your full name, contact information, emergency contacts, and confidential health declarations (including allergies, injuries, pregnancy status, and chronic ailments).</p><h3>2. How We Safeguard Health Declarations</h3><p>Health declarations are securely encrypted and reviewed exclusively by licensed therapists assigned to your treatment to guarantee contraindication safety. We never sell, lease, or share confidential medical records with third-party advertising entities.</p><h3>3. Data Retention & Your Rights</h3><p>You maintain the right to inspect, correct, or request deletion of personal records in compliance with the Philippine Data Privacy Act of 2012.</p>', 'Comprehensive privacy policy and confidential health data protections.', NULL, 1),
('terms_conditions', 'Terms of Booking, Arrival & Spa Etiquette', '<h2>Terms & Conditions</h2><p>Welcome to Bella Vista Oasis Spa. By confirming an appointment with us, you agree to the following terms designed to preserve peace, safety, and operational excellence.</p><h3>1. Operating Hours</h3><p>Our doors are open daily from 1:00 PM to 9:00 PM (13:00 to 21:00). All treatments must be concluded within standard operating hours.</p><h3>2. Arrival Protocol</h3><p>We kindly request guests to arrive at least fifteen (15) minutes prior to scheduled appointments to indulge in our welcome herbal tea ceremony, complete any health declaration updates, and change comfortably into spa attire. Late arrivals will receive treatments for the remaining scheduled duration only, with full fees applicable.</p><h3>3. Room Sanitization Gap</h3><p>A mandatory 30-minute cleaning and medical-grade sanitization window is enforced between every room booking to ensure total hygiene.</p><h3>4. Cancellation & Rescheduling</h3><p>Appointments may be cancelled or rescheduled up to six (6) hours prior to the scheduled slot without penalty. Cancellations within six hours or no-shows forfeit deposits.</p><h3>5. Tranquility Etiquette</h3><p>To preserve our peaceful atmosphere, mobile phones must be silenced upon entering the sanctuary. Smoking, vaping, and disruptive conduct are strictly prohibited.</p>', 'Terms of booking, cancellation deadlines, and sanctuary etiquette.', NULL, 1);

-- ----------------------------------------------------------------------------
-- Sample Inquiries, Reviews, and Attendance for Demonstration
-- ----------------------------------------------------------------------------
INSERT INTO `reviews` (`customer_id`, `therapist_id`, `rating`, `comment`, `is_published`, `admin_response`, `responded_at`) VALUES
(1, 1, 5, 'The Single Room Massage with Elena was absolute heaven! The aromatherapy blend combined with her precise pressure completely untangled my neck stiffness. The ambiance is breathtaking.', 1, 'Thank you so much Maria! We are thrilled to hear Elena took such gentle care of you. We look forward to welcoming you back to your sanctuary soon.', NOW());

INSERT INTO `employee_schedules` (`employee_id`, `day_of_week`, `shift_start`, `shift_end`, `is_day_off`, `effective_from`) VALUES
(4, 'Monday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(4, 'Tuesday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(4, 'Wednesday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(4, 'Thursday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(4, 'Friday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(4, 'Saturday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(4, 'Sunday', '00:00:00', '00:00:00', 1, '2024-01-01'),
(5, 'Monday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(5, 'Tuesday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(5, 'Wednesday', '00:00:00', '00:00:00', 1, '2024-01-01'),
(5, 'Thursday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(5, 'Friday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(5, 'Saturday', '12:30:00', '21:30:00', 0, '2024-01-01'),
(5, 'Sunday', '12:30:00', '21:30:00', 0, '2024-01-01');

-- Sample audit log
INSERT INTO `audit_logs` (`user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`) VALUES
(1, 'SYSTEM_INITIALIZATION', 'system_settings', 1, NULL, '{"status":"Database created and seeded successfully with luxury schema."}', '127.0.0.1', 'Bella Vista Core Backend Installer');
