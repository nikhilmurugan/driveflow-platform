-- SQL file to create the chat_messages table
-- Run this script on your database to set up the chat system

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_support_reply` tinyint(1) NOT NULL DEFAULT '0',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_email` (`user_email`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for testing (optional)
INSERT INTO `chat_messages` (`user_email`, `message`, `is_support_reply`, `is_read`, `created_at`)
VALUES
('test@example.com', 'Hello, I have a question about booking a car', 0, 1, NOW() - INTERVAL 2 HOUR),
('test@example.com', 'Hi there! How can I help you with your car booking?', 1, 1, NOW() - INTERVAL 1 HOUR 55 MINUTE),
('test@example.com', 'I\'m trying to book a car but I\'m not sure how the pricing works', 0, 1, NOW() - INTERVAL 1 HOUR 50 MINUTE),
('test@example.com', 'Our prices are dynamic and change based on demand, season, and how far in advance you book. You can see the current price on each car listing. Early bookings often get discounts!', 1, 0, NOW() - INTERVAL 1 HOUR 45 MINUTE);

-- Create a support_agents table (optional, if you want to expand the system later)
CREATE TABLE IF NOT EXISTS `support_agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default support agent
INSERT INTO `support_agents` (`name`, `email`, `password`, `is_online`, `created_at`)
VALUES ('Support Team', 'support@cars.com', SHA2('password123', 256), 1, NOW()); 