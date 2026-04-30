-- MySQL Workbench Forward Engineering
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema red_ghost
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `red_ghost` DEFAULT CHARACTER SET utf8mb4 ;
USE `red_ghost` ;

-- -----------------------------------------------------
-- Table `red_ghost`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL DEFAULT NULL,
  `email` VARCHAR(50) NULL DEFAULT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `loyalty_points` INT(11) NULL DEFAULT 0,
  `role` ENUM('customer', 'admin') NULL DEFAULT 'customer',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email` (`email` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`discount_code`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`discount_code` (
  `id_discount_code` INT(11) NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `discount_type` ENUM('percent', 'fixed') NOT NULL,
  `value` DECIMAL(10,2) NOT NULL,
  `min_order_value` DECIMAL(10,2) NULL DEFAULT NULL,
  `valid_from` DATETIME NULL DEFAULT NULL,
  `valid_to` DATETIME NULL DEFAULT NULL,
  `is_active` TINYINT(1) NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_discount_code`),
  UNIQUE INDEX `uq_discount_code` (`code` ASC) 
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`order`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`order` (
  `id_order` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(255) NULL DEFAULT NULL,
  `customer_email` VARCHAR(255) NULL DEFAULT NULL,
  `customer_phone` VARCHAR(20) NULL DEFAULT NULL,
  `total_price` DECIMAL(10,2) NULL DEFAULT NULL,
  `status` ENUM('pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `user_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_order`),
  INDEX `idx_order_user` (`user_id` ASC),
  INDEX `idx_order_status` (`status` ASC),
  CONSTRAINT `order_ibfk_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `red_ghost`.`user` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`discount_code_redemption`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`discount_code_redemption` (
  `id_redemption` INT NOT NULL AUTO_INCREMENT,
  `id_discount_code` INT NOT NULL,
  `id_user` INT NOT NULL,
  `id_order` INT NULL,
  `used_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id_redemption`),
  UNIQUE (`id_user`, `id_discount_code`),
  INDEX (`id_user`),
  INDEX (`id_discount_code`),
  CONSTRAINT `fk_redemption_user`
    FOREIGN KEY (`id_user`)
    REFERENCES `red_ghost`.`user` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_redemption_code`
    FOREIGN KEY (`id_discount_code`)
    REFERENCES `red_ghost`.`discount_code` (`id_discount_code`)
    ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`address`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`address` (
  `id_address` INT(11) NOT NULL AUTO_INCREMENT,
  `street` VARCHAR(100) NULL DEFAULT NULL,
  `city` VARCHAR(60) NULL DEFAULT NULL,
  `zip` VARCHAR(30) NULL DEFAULT NULL,
  `country` VARCHAR(60) NULL DEFAULT NULL,
  `is_default` TINYINT(4) NULL DEFAULT 0,
  `id_user` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_address`),
  INDEX `idx_address_user` (`id_user` ASC),
  CONSTRAINT `address_ibfk_1`
    FOREIGN KEY (`id_user`)
    REFERENCES `red_ghost`.`user` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`category` (
  `id_category` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NULL DEFAULT NULL,
  PRIMARY KEY (`id_category`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`contact_messages`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`contact_messages` (
  `id_contact_msg` INT(11) NOT NULL AUTO_INCREMENT,
  `sender_name` VARCHAR(255) NULL DEFAULT NULL,
  `sender_email` VARCHAR(80) NULL DEFAULT NULL,
  `subject` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('new', 'read', 'replied', 'closed') NULL DEFAULT 'new',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `id_user` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_contact_msg`),
  INDEX `idx_contact_user` (`id_user` ASC),
  INDEX `idx_contact_status` (`status` ASC),
  CONSTRAINT `contact_messages_ibfk_1`
    FOREIGN KEY (`id_user`)
    REFERENCES `red_ghost`.`user` (`id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`contact_replies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`contact_replies` (
  `id_replies` INT(11) NOT NULL AUTO_INCREMENT,
  `sender_type` ENUM('user', 'admin') NULL DEFAULT NULL,
  `message_text` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `id_message` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_replies`),
  INDEX `idx_reply_message` (`id_message` ASC),
  CONSTRAINT `contact_replies_ibfk_1`
    FOREIGN KEY (`id_message`)
    REFERENCES `red_ghost`.`contact_messages` (`id_contact_msg`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`order_address`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`order_address` (
  `id_order_address` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('billing', 'shipping') NULL DEFAULT NULL,
  `street` VARCHAR(100) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT NULL,
  `zip` VARCHAR(30) NULL DEFAULT NULL,
  `country` VARCHAR(60) NULL DEFAULT NULL,
  `id_order` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_order_address`),
  INDEX `id_order` (`id_order` ASC),
  CONSTRAINT `order_address_ibfk_1`
    FOREIGN KEY (`id_order`)
    REFERENCES `red_ghost`.`order` (`id_order`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`product`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`product` (
  `id_product` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `price` DECIMAL(10,2) NULL DEFAULT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `rating` INT(11) NULL DEFAULT 0,
  `featured` TINYINT(4) NULL DEFAULT 0,
  `stock` INT(11) NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_product`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`order_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`order_item` (
  `id_order_item` INT(11) NOT NULL AUTO_INCREMENT,
  `quantity` INT(11) NULL DEFAULT NULL,
  `price` DECIMAL(10,2) NULL DEFAULT NULL,
  `id_order` INT(11) NULL DEFAULT NULL,
  `id_product` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_order_item`),
  INDEX `idx_order_item_order` (`id_order` ASC),
  INDEX `idx_order_item_product` (`id_product` ASC),
  CONSTRAINT `order_item_ibfk_1`
    FOREIGN KEY (`id_order`)
    REFERENCES `red_ghost`.`order` (`id_order`),
  CONSTRAINT `order_item_ibfk_2`
    FOREIGN KEY (`id_product`)
    REFERENCES `red_ghost`.`product` (`id_product`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`order_status_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`order_status_history` (
  `id_status` INT(11) NOT NULL AUTO_INCREMENT,
  `status` VARCHAR(50) NULL DEFAULT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `id_order` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_status`),
  INDEX `id_order` (`id_order` ASC),
  CONSTRAINT `order_status_history_ibfk_1`
    FOREIGN KEY (`id_order`)
    REFERENCES `red_ghost`.`order` (`id_order`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`payment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`payment` (
  `id_payment` INT(11) NOT NULL AUTO_INCREMENT,
  `payment_method` VARCHAR(50) NULL DEFAULT NULL,
  `amount` DECIMAL(10,2) NULL DEFAULT NULL,
  `status` ENUM('pending', 'paid', 'failed', 'cancelled', 'refunded') NULL DEFAULT 'pending',
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `id_order` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id_payment`),
  INDEX `idx_payment_order` (`id_order` ASC),
  INDEX `idx_payment_status` (`status` ASC),
  CONSTRAINT `payment_ibfk_1`
    FOREIGN KEY (`id_order`)
    REFERENCES `red_ghost`.`order` (`id_order`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Table `red_ghost`.`product_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `red_ghost`.`product_category` (
  `id_product` INT(11) NOT NULL,
  `id_category` INT(11) NOT NULL,
  PRIMARY KEY (`id_product`, `id_category`),
  INDEX `id_category` (`id_category` ASC),
  CONSTRAINT `product_category_ibfk_1`
    FOREIGN KEY (`id_product`)
    REFERENCES `red_ghost`.`product` (`id_product`),
  CONSTRAINT `product_category_ibfk_2`
    FOREIGN KEY (`id_category`)
    REFERENCES `red_ghost`.`category` (`id_category`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4;

-- -----------------------------------------------------
-- Deferred foreign keys
-- -----------------------------------------------------
ALTER TABLE `red_ghost`.`discount_code_redemption`
  ADD CONSTRAINT `fk_redemption_order`
  FOREIGN KEY (`id_order`)
  REFERENCES `red_ghost`.`order` (`id_order`);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;