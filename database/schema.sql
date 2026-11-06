-- Bella Vista Oasis Spa Database Schema
USE bella_vista;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin', 'manager', 'therapist', 'front_desk') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50),
    date_of_birth DATE NULL,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT 'prefer_not_to_say',
    loyalty_points INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    duration_minutes INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    max_guests INT DEFAULT 1,
    additional_guest_fee DECIMAL(10,2) DEFAULT 0.00,
    room_type VARCHAR(100) DEFAULT 'Single Suite',
    image VARCHAR(255) DEFAULT '',
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    badge VARCHAR(50) DEFAULT 'Ongoing',
    description TEXT,
    discount_text VARCHAR(100),
    valid_until DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS website_contents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    type VARCHAR(50) DEFAULT 'announcement',
    badge VARCHAR(50) DEFAULT 'Announcement',
    published_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default users (password: Admin123! and Customer123!)
INSERT INTO users (id, email, password, role, status) VALUES
(1, 'admin@bellavista.ph', '$2y$10$EjaLJUIn3GeE0eE9MTyD2.lGz4OSVOVxTecrPiX8x/L8wiuSd3Tsa', 'admin', 'active'),
(2, 'manager@bellavista.ph', '$2y$10$EjaLJUIn3GeE0eE9MTyD2.lGz4OSVOVxTecrPiX8x/L8wiuSd3Tsa', 'manager', 'active'),
(3, 'frontdesk@bellavista.ph', '$2y$10$EjaLJUIn3GeE0eE9MTyD2.lGz4OSVOVxTecrPiX8x/L8wiuSd3Tsa', 'front_desk', 'active'),
(4, 'therapist@bellavista.ph', '$2y$10$EjaLJUIn3GeE0eE9MTyD2.lGz4OSVOVxTecrPiX8x/L8wiuSd3Tsa', 'therapist', 'active'),
(5, 'guest@bellavista.ph', '$2y$10$EjaLJUIn3GeE0eE9MTyD2.lGz4OSVOVxTecrPiX8x/L8wiuSd3Tsa', 'customer', 'active')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO customers (id, user_id, first_name, last_name, phone, date_of_birth, gender, loyalty_points) VALUES
(1, 5, 'Maria', 'Santos', '+63 917 123 4567', '1992-05-18', 'female', 120)
ON DUPLICATE KEY UPDATE id=id;

-- Seed Services
INSERT INTO services (name, category, description, duration_minutes, price, max_guests, additional_guest_fee, room_type, is_featured, is_active) VALUES
('Single Room Massage', 'Massage Therapy', 'Immerse in holistic rejuvenation tailored specifically for your body. Our certified therapists utilize cold-pressed virgin coconut and therapeutic botanical oils to soothe muscle tension, enhance circulation, and calm your senses.', 60, 800.00, 1, 0.00, 'Single Private Suite', 1, 1),
('Couples Suite Indulgence', 'Couples Rituals', 'An intimate sanctuary designed for two. Experience synchronized aromatherapy full-body massages side-by-side in our private couples suite, complemented with warm foot rituals, organic floral tea, and luxury aromatics.', 90, 1800.00, 2, 850.00, 'Couples Deluxe Suite', 1, 1),
('Family Lounge & Wellness Escape', 'Family & Group Packages', 'A shared luxury experience for families or small groups of up to 4 guests. Includes personalized massages, herbal steam therapy, reflexology, and artisanal refreshments in our expansive royal suite lounge.', 120, 4000.00, 4, 900.00, 'Royal Family Lounge', 1, 1),
('Deep Tissue Healing Massage', 'Massage Therapy', 'Targeted pressure and slow, firm strokes focused on deep muscle layers and connective tissues to release chronic tension, improve posture, and alleviate physical fatigue.', 75, 1200.00, 1, 0.00, 'Single Private Suite', 0, 1),
('Bella Vista Signature Hot Stone Ritual', 'Signature Packages', 'Smooth basalt river stones heated to restorative temperatures are placed on vital energy centers. Warm stones glide smoothly with rich aromatic balms to dissolve stress and deeply relax tissues.', 90, 1600.00, 2, 750.00, 'Couples Deluxe Suite', 1, 1),
('Philippine Traditional Hilot', 'Massage Therapy', 'An ancient Filipino healing art utilizing warm virgin coconut oil and heated banana leaves applied along energy meridians to detect energy blockages (pilay) and restore natural vitality.', 60, 950.00, 1, 0.00, 'Single Private Suite', 0, 1),
('Golden Radiance Botanical Facial', 'Facial & Body', 'Pure botanical extracts, antioxidant peptides, and gentle facial acupressure revitalise fatigued skin, restore moisture balance, and impart an opulent, youthful luminescence.', 60, 1350.00, 1, 0.00, 'Aesthetics Studio', 0, 1),
('Himalayan Salt Scrub & Vichy Shower', 'Facial & Body', 'Gentle full-body exfoliation using mineral-rich Himalayan pink crystals and lavender essences, followed by a warm cascading rainfall shower that leaves skin satin-soft.', 60, 1400.00, 1, 0.00, 'Hydrotherapy Room', 0, 1),
('Royal Herbal Steam & Jacuzzi Soak', 'Water & Steam', 'Immerse in private hydro-massage waters infused with lemongrass, chamomile, and eucalyptus, followed by our therapeutic cedar herbal steam room.', 45, 900.00, 2, 400.00, 'VIP Sanctuary', 0, 1)
ON DUPLICATE KEY UPDATE name=name;

-- Seed Promotions
INSERT INTO promotions (title, badge, description, discount_text, valid_until, is_active) VALUES
('Sunset Serenity Happy Hour', 'Ongoing', 'Enjoy a complimentary 15-minute botanical scalp treatment with any 90-minute massage booked between 1:00 PM and 4:00 PM on weekdays.', '15% Off scalps & oils', '2026-12-31', 1),
('Couples Sanctuary Weekend Retreat', 'New', 'Indulge in our signature Couples Suite package and receive two complimentary Golden Radiance mini-facials and a bottle of sparkling artisanal cider.', 'Save ₱600 on suite bundle', '2026-11-30', 1),
('First-Time Oasis Welcoming', 'Ongoing', 'New guests receive an immediate ₱200 discount on their first full-body ritual plus 50 complimentary loyalty points credited upon arrival.', '₱200 Welcome Gift', '2026-12-31', 1),
('Pre-Holiday Glow Package', 'Ending Soon', 'Our seasonal curated trio: Himalayan salt polish, signature hot stone massage, and golden radiance facial at an exclusive bundled privilege rate.', 'Save 25% bundled', '2026-11-20', 1);

-- Seed Website Contents / Announcements
INSERT INTO website_contents (section_key, title, content, type, badge, published_date, is_active) VALUES
('announcement_holiday_schedule', 'Holiday Season Operating Advisory', 'Bella Vista Oasis Spa will remain open throughout the upcoming holiday season with special extended operating hours from 1:00 PM to 10:00 PM on select dates. Advance booking is highly encouraged.', 'announcement', 'Important', '2026-11-01', 1),
('announcement_new_sanctuary', 'Unveiling Our New VIP Hydrotherapy Suite', 'We are thrilled to unveil our latest architectural addition: a dedicated private hydrotherapy sanctuary featuring custom cedar steam chambers and deep mineral thermal tubs.', 'announcement', 'New', '2026-10-25', 1),
('announcement_loyalty_boost', 'Double Points Weekend for Oasis Club Members', 'Every booking completed on Saturdays and Sundays throughout this month earns double loyalty credits redeemable toward luxury add-ons and complimentary treatments.', 'announcement', 'Ongoing', '2026-10-15', 1);
