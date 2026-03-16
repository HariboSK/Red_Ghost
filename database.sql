-- Red Ghost E-shop Database Setup
-- Importujte tento súbor do phpMyAdmin v XAMPP-e

-- Vytvorenie databázy
CREATE DATABASE IF NOT EXISTS red_ghost;
USE red_ghost;

-- Tabuľka produktov
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(100),
    rating INT DEFAULT 4,
    featured BOOLEAN DEFAULT 0,
    stock INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabuľka kontaktných správ
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_status BOOLEAN DEFAULT 0
);

-- Tabuľka objednávok (pre budúcnosť)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20),
    total_price DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabuľka položiek objednávok
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Vloženie ukážkových produktov
INSERT INTO products (name, description, price, category, featured, stock) VALUES
('Pálivá omáčka', 'Chilli omáčka s výraznou pálivosťou', 10.00, 'omáčky', 1, 20),
('Kľúčenka na cestu', 'Malá kľúčenka s chilli motívom', 3.00, 'doplnky', 1, 50),
('Chilli soľ', 'Soľ s príchutňou chilli', 5.00, 'przypravy', 1, 30),
('Sadenice chilli', 'Sadenice rôznych druhov chilli', 10.00, 'sadenice', 0, 15),
('Extrakt z chilli', 'Koncentrovaný extrakt z chilli', 10.00, 'extrakty', 0, 25),
('Mleté chilli', 'Mleté chilli papričky', 7.00, 'przypravy', 0, 40),
('Sušené papričky', 'Prírodne sušené papričky', 8.00, 'susene', 0, 35),
('Chutney', 'Marhuľové chutney s chilli', 6.00, 'omáčky', 0, 20);
