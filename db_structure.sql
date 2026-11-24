-- Sunrise Cove Villa booking system. Import in phpMyAdmin or: mysql -u root < db_structure.sql
CREATE DATABASE IF NOT EXISTS villa_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE villa_booking;
DROP TABLE IF EXISTS bookings; DROP TABLE IF EXISTS rooms; DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  contact_number VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,            -- password_hash() output
  role ENUM('guest','admin') NOT NULL DEFAULT 'guest',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_name VARCHAR(100) NOT NULL,
  room_type ENUM('Standard','Deluxe','Family','Suite') NOT NULL,
  description TEXT,
  facilities VARCHAR(255),
  price_per_night DECIMAL(10,2) NOT NULL CHECK (price_per_night > 0),
  max_guests TINYINT NOT NULL CHECK (max_guests > 0),
  room_status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  image VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE bookings (
  booking_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  guest_name VARCHAR(100) NOT NULL,
  guest_email VARCHAR(150) NOT NULL,
  guest_contact VARCHAR(20) NOT NULL,
  guests TINYINT NOT NULL DEFAULT 1,
  notes VARCHAR(300) DEFAULT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  booking_status ENUM('Pending','Confirmed','Cancelled') NOT NULL DEFAULT 'Pending',
  booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_b_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_b_room FOREIGN KEY (room_id) REFERENCES rooms(room_id),
  CONSTRAINT chk_dates CHECK (check_out > check_in),
  INDEX idx_room_dates (room_id, check_in, check_out)
) ENGINE=InnoDB;

-- Sample data (fictional). Admin: admin@villa.test / Admin@123   Guest: guest@villa.test / Guest@123
INSERT INTO users (name,email,contact_number,password,role) VALUES
('Villa Admin','admin@villa.test','+94 77 000 0001','$2y$10$W6mwTNKpZZ4oqwrARMgf3utJ7pywyVUO8yomCK6SVbXbSc7xezFYu','admin'),
('Nimal Perera','guest@villa.test','+94 71 000 0002','$2y$10$fBw4TKpbjnNpN.pIzkIsFuew85H4/g08fALs6Ofj0sYFouKKeqvR.','guest');

INSERT INTO rooms (room_name,room_type,description,facilities,price_per_night,max_guests,room_status) VALUES
('Garden Room','Standard','A quiet ground-floor room opening onto the tropical garden.','Fan,Free Wi-Fi,Breakfast,Garden view',12000,2,'Active'),
('Ocean Breeze Room','Deluxe','First-floor room with a balcony facing the sea and a king bed.','Air conditioning,Free Wi-Fi,Breakfast,Sea view,Balcony',18000,2,'Active'),
('Family Loft','Family','Two-bedroom loft with a small kitchenette, suited to families.','Air conditioning,Free Wi-Fi,Breakfast,Kitchenette,Pool access',26000,5,'Active'),
('Sunrise Suite','Suite','Top-floor suite with a private terrace and sunrise views over the bay.','Air conditioning,Free Wi-Fi,Breakfast,Sea view,Pool access,Terrace',34000,3,'Active'),
('Old Cabana','Standard','Retired beach cabana, closed for repairs.','Fan',9000,2,'Inactive');

INSERT INTO bookings (user_id,room_id,guest_name,guest_email,guest_contact,guests,check_in,check_out,total_amount,booking_status) VALUES
(2,2,'Nimal Perera','guest@villa.test','+94 71 000 0002',2,'2027-02-10','2027-02-13',64800,'Confirmed'),
(2,3,'Nimal Perera','guest@villa.test','+94 71 000 0002',4,'2027-05-05','2027-05-08',78000,'Pending'),
(2,1,'Nimal Perera','guest@villa.test','+94 71 000 0002',1,'2027-06-01','2027-06-03',24000,'Cancelled');
