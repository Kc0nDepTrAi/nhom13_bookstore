-- Upgrade the existing database without replacing books, users or orders.
-- Select the application database and back it up before running.
ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `notes` varchar(2000) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS `payment_method` varchar(10) NOT NULL DEFAULT 'cash';

CREATE TABLE IF NOT EXISTS `book_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` varchar(2000) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_reviews_reader_book` (`user_id`, `book_id`),
  KEY `book_reviews_book` (`book_id`),
  CONSTRAINT `book_reviews_rating_range` CHECK (`rating` BETWEEN 1 AND 5),
  CONSTRAINT `book_reviews_book_fk` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `book_reviews_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `book_reviews` ADD COLUMN IF NOT EXISTS `comment` varchar(2000) NOT NULL DEFAULT '' AFTER `rating`;
