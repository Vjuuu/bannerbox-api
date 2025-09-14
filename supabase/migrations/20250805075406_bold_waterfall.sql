-- CodeIgniter 3 API Backend Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS codeigniter_api;
USE codeigniter_api;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    subscription_type ENUM('basic', 'pro', 'premium') DEFAULT 'basic',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_subscription (subscription_type)
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT NULL,
    color VARCHAR(7) DEFAULT '#000000',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_active (is_active)
);

-- Posters table
CREATE TABLE IF NOT EXISTS posters (
    poster_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    image_url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500) DEFAULT NULL,
    category_id INT NOT NULL,
    tags TEXT DEFAULT NULL,
    is_premium TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    download_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_premium (is_premium),
    INDEX idx_featured (is_featured),
    INDEX idx_created (created_at),
    INDEX idx_downloads (download_count),
    INDEX idx_views (view_count),
    FULLTEXT INDEX idx_search (title, description, tags)
);

-- Insert sample categories
INSERT INTO categories (name, description, icon, color, is_active, created_at) VALUES
('Business', 'Professional business templates and designs', 'briefcase', '#2563eb', 1, NOW()),
('Education', 'Educational posters and academic materials', 'graduation-cap', '#059669', 1, NOW()),
('Health', 'Healthcare and medical related designs', 'heart', '#dc2626', 1, NOW()),
('Technology', 'Tech-related posters and modern designs', 'cpu', '#7c3aed', 1, NOW()),
('Events', 'Event announcements and party invitations', 'calendar', '#ea580c', 1, NOW()),
('Marketing', 'Promotional and advertising materials', 'megaphone', '#0891b2', 1, NOW()),
('Food & Beverage', 'Restaurant and food-related designs', 'utensils', '#65a30d', 1, NOW()),
('Sports', 'Sports and fitness related posters', 'dumbbell', '#dc2626', 1, NOW());

-- Insert sample users
INSERT INTO users (username, email, password, full_name, subscription_type, is_active, created_at) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'premium', 1, NOW()),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'pro', 1, NOW()),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', 'basic', 1, NOW());

-- Insert sample posters
INSERT INTO posters (title, description, image_url, thumbnail_url, category_id, tags, is_premium, is_featured, download_count, view_count, created_at) VALUES
('Modern Business Presentation', 'Professional business presentation template with clean design', 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg', 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?w=300', 1, 'business,presentation,professional,modern', 0, 1, 150, 500, NOW()),
('Educational Infographic', 'Colorful educational infographic template for schools', 'https://images.pexels.com/photos/256490/pexels-photo-256490.jpeg', 'https://images.pexels.com/photos/256490/pexels-photo-256490.jpeg?w=300', 2, 'education,infographic,school,learning', 0, 1, 200, 800, NOW()),
('Health Awareness Poster', 'Health and wellness awareness campaign poster', 'https://images.pexels.com/photos/40568/medical-appointment-doctor-healthcare-40568.jpeg', 'https://images.pexels.com/photos/40568/medical-appointment-doctor-healthcare-40568.jpeg?w=300', 3, 'health,wellness,medical,awareness', 1, 0, 75, 300, NOW()),
('Tech Conference Banner', 'Modern technology conference banner design', 'https://images.pexels.com/photos/325185/pexels-photo-325185.jpeg', 'https://images.pexels.com/photos/325185/pexels-photo-325185.jpeg?w=300', 4, 'technology,conference,modern,digital', 1, 1, 120, 450, NOW()),
('Music Festival Poster', 'Vibrant music festival event poster', 'https://images.pexels.com/photos/1190297/pexels-photo-1190297.jpeg', 'https://images.pexels.com/photos/1190297/pexels-photo-1190297.jpeg?w=300', 5, 'music,festival,event,colorful', 0, 0, 300, 1200, NOW()),
('Digital Marketing Ad', 'Social media marketing advertisement template', 'https://images.pexels.com/photos/267389/pexels-photo-267389.jpeg', 'https://images.pexels.com/photos/267389/pexels-photo-267389.jpeg?w=300', 6, 'marketing,social media,advertising,digital', 0, 1, 180, 650, NOW()),
('Restaurant Menu Design', 'Elegant restaurant menu design template', 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg', 'https://images.pexels.com/photos/262978/pexels-photo-262978.jpeg?w=300', 7, 'restaurant,menu,food,elegant', 1, 0, 90, 380, NOW()),
('Fitness Motivation Poster', 'Inspirational fitness and workout poster', 'https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg', 'https://images.pexels.com/photos/841130/pexels-photo-841130.jpeg?w=300', 8, 'fitness,motivation,workout,sports', 0, 1, 250, 900, NOW());

-- Create indexes for better performance
CREATE INDEX idx_posters_trending ON posters (created_at, view_count, download_count);
CREATE INDEX idx_users_subscription_active ON users (subscription_type, is_active);
CREATE INDEX idx_categories_active_name ON categories (is_active, name);