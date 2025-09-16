-- Database Schema untuk RuangClient
CREATE DATABASE IF NOT EXISTS ruangclient_db;
USE ruangclient_db;

-- Table untuk admin sistem (Yuda)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    midtrans_server_key VARCHAR(255),
    midtrans_client_key VARCHAR(255),
    subscription_price DECIMAL(10,2) DEFAULT 48000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table untuk pemilik usaha (user yang berlangganan)
CREATE TABLE business_owners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    business_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    subscription_status ENUM('active', 'expired', 'pending') DEFAULT 'pending',
    subscription_expires_at DATETIME,
    midtrans_server_key VARCHAR(255),
    midtrans_client_key VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table untuk link sosial media (linktree)
CREATE TABLE social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_owner_id INT,
    platform VARCHAR(50) NOT NULL,
    url VARCHAR(500) NOT NULL,
    icon VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_owner_id) REFERENCES business_owners(id) ON DELETE CASCADE
);

-- Table untuk jenis layanan
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_owner_id INT,
    service_name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'durasi dalam menit',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_owner_id) REFERENCES business_owners(id) ON DELETE CASCADE
);

-- Table untuk client/pelanggan
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_owner_id INT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    whatsapp VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_owner_id) REFERENCES business_owners(id) ON DELETE CASCADE
);

-- Table untuk booking
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_owner_id INT,
    client_id INT,
    service_id INT,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'down_payment', 'paid') DEFAULT 'unpaid',
    payment_amount DECIMAL(10,2) DEFAULT 0,
    notes TEXT,
    midtrans_order_id VARCHAR(255),
    midtrans_transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_owner_id) REFERENCES business_owners(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Table untuk pembayaran langganan
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_owner_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'expired') DEFAULT 'pending',
    payment_date DATETIME,
    expires_at DATETIME,
    midtrans_order_id VARCHAR(255),
    midtrans_transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_owner_id) REFERENCES business_owners(id) ON DELETE CASCADE
);

-- Table untuk payment transactions
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_owner_id INT,
    booking_id INT,
    amount DECIMAL(10,2) NOT NULL,
    transaction_type ENUM('booking', 'subscription') NOT NULL,
    payment_method VARCHAR(100),
    status ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    midtrans_order_id VARCHAR(255),
    midtrans_transaction_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_owner_id) REFERENCES business_owners(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO admins (username, email, password, name) VALUES 
('yuda_admin', 'yuda@ruangclient.com', MD5('admin123'), 'Yuda');

-- Insert sample data
INSERT INTO business_owners (username, email, password, business_name, owner_name, phone) VALUES 
('demo_business', 'demo@business.com', MD5('demo123'), 'Demo Business', 'Demo Owner', '08123456789');

INSERT INTO services (business_owner_id, service_name, description, price, duration) VALUES 
(1, 'Konsultasi Bisnis', 'Konsultasi pengembangan bisnis', 150000, 60),
(1, 'Design Logo', 'Pembuatan design logo perusahaan', 300000, 120);

INSERT INTO social_links (business_owner_id, platform, url, icon) VALUES 
(1, 'WhatsApp', 'https://wa.me/628123456789', 'fab fa-whatsapp'),
(1, 'Instagram', 'https://instagram.com/demo', 'fab fa-instagram'),
(1, 'Facebook', 'https://facebook.com/demo', 'fab fa-facebook');