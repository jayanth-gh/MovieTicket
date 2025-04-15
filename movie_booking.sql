-- Create database
CREATE DATABASE IF NOT EXISTS movie_booking;
USE movie_booking;

-- Create movies table
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    duration INT NOT NULL,
    language VARCHAR(50) NOT NULL,
    release_date DATE NOT NULL,
    poster_url VARCHAR(255)
);

-- Create shows table
CREATE TABLE IF NOT EXISTS shows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    show_date DATE NOT NULL,
    show_time TIME NOT NULL,
    total_seats INT NOT NULL,
    booked_seats INT DEFAULT 0,
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    show_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    seats_booked INT NOT NULL,
    booking_date DATETIME NOT NULL,
    FOREIGN KEY (show_id) REFERENCES shows(id)
);

-- Insert sample data
INSERT INTO movies (title, genre, duration, language, release_date) VALUES
('Inception', 'Sci-Fi', 148, 'English', '2024-04-10'),
('The Dark Knight', 'Action', 152, 'English', '2024-04-11'),
('Interstellar', 'Sci-Fi', 169, 'English', '2024-04-12');

INSERT INTO shows (movie_id, show_date, show_time, total_seats) VALUES
(1, '2024-04-10', '14:00:00', 100),
(1, '2024-04-10', '18:00:00', 100),
(2, '2024-04-11', '15:00:00', 100),
(3, '2024-04-12', '16:00:00', 100); 