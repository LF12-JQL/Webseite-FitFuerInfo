CREATE DATABASE IF NOT EXISTS fitfuerinfo CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE fitfuerinfo;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Mitarbeiter', 'Systemverwalter') NOT NULL DEFAULT 'Mitarbeiter'
) ENGINE=InnoDB;

-- Software packages
CREATE TABLE IF NOT EXISTS software (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Rooms
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    workstations INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Room Software
CREATE TABLE IF NOT EXISTS room_software (
    room_id INT NOT NULL,
    software_id INT NOT NULL,
    PRIMARY KEY (room_id, software_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (software_id) REFERENCES software(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Room Editors
CREATE TABLE IF NOT EXISTS room_editors (
    room_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (room_id, user_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Courses
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    max_participants INT NOT NULL,
    creator_id INT NOT NULL,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Course Owners (employees who can edit)
CREATE TABLE IF NOT EXISTS course_owners (
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (course_id, user_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Course Software (required software)
CREATE TABLE IF NOT EXISTS course_software (
    course_id INT NOT NULL,
    software_id INT NOT NULL,
    PRIMARY KEY (course_id, software_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (software_id) REFERENCES software(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    course_id INT NOT NULL,
    user_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dummy Data

-- Dummy users removed to enforce setup.php on first visit

INSERT IGNORE INTO software (id, name) VALUES 
(1, 'Microsoft Office'), (2, 'Visual Studio Code'), (3, 'Wireshark'), (4, 'Adobe Photoshop');

INSERT IGNORE INTO rooms (id, name, workstations) VALUES 
(1, 'Raum A (IT-Sicherheit)', 20), 
(2, 'Raum B (Office)', 30),
(3, 'Raum C (Netzwerk)', 15),
(4, 'Raum D (Besprechung)', 10),
(5, 'Raum E (Schulung)', 20);

INSERT IGNORE INTO room_software (room_id, software_id) VALUES 
(1, 2), (1, 3), (2, 1);


