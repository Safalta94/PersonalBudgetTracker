CREATE DATABASE budgettrackerdb;
USE budgettrackerdb;
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('income', 'expenses') NOT NULL
);
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user'
);
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    date DATE,
    description TEXT,
    user_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE income (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    date DATE,
    description TEXT,
    user_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
INSERT INTO categories (name, type) VALUES('Salary', 'income'),('Investments', 'income'),('Food', 'expense'),('Housing', 'expense'),('Transportation', 'expense')('Salary', 'income'),('Investments', 'income'),('Food', 'expenses'),('Housing', 'expenses'),('Transportation', 'expenses')('Freelancing', 'income'),('Business Profits', 'income'),('Rental Income', 'income'),('Dividends', 'income'),('Interest Earned', 'income'),('Bonuses', 'income'),('Royalties', 'income'),('Gifts & Donations', 'income'),('Pension', 'income'),('Side Hustle', 'income'), ('Utilities', 'expenses'),('Entertainment', 'expenses'),('Healthcare', 'expenses'),('Insurance', 'expenses'),('Education', 'expenses'),('Debt Repayment', 'expenses'),('Clothing', 'expenses'),('Travel', 'expenses'),('Subscriptions', 'expenses'),('Childcare', 'expenses'),('Groceries', 'expenses'),('Dining Out', 'expenses'), ('Fitness & Gym', 'expenses'),('Personal Care', 'expenses'),('Taxes', 'expenses');
INSERT INTO users (name, email, password, role) VALUES ('Admin User', 'admin@gmail.com', '$2y$10$kld3knXC.ZBOQ6RY6ipSu.bK0H3xciWHjmLOSQDXuqWnceynvlHZ6', 'Admin');

INSERT INTO income (amount, category_id, date, description, user_id) VALUES
(5000.00, 1, '2023-01-05', 'Salary Payment January', 2),
(5200.00, 1, '2023-02-05', 'Salary Payment February', 2),
(5100.00, 1, '2023-03-05', 'Salary Payment March', 2),
(700.00, 8, '2023-04-10', 'Rental Income', 3),
(250.00, 11, '2023-06-12', 'Performance Bonus', 3),
(5300.00, 1, '2024-01-05', 'Salary Payment January', 2),
(5400.00, 1, '2024-02-05', 'Salary Payment February', 2),
(5500.00, 1, '2024-03-05', 'Salary Payment March', 2),
(750.00, 8, '2024-05-10', 'Rental Income', 3),
(260.00, 11, '2024-07-12', 'Performance Bonus', 3),
(5600.00, 1, '2025-01-05', 'Salary Payment January', 2),
(5700.00, 1, '2025-02-05', 'Salary Payment February', 2),
(5800.00, 1, '2025-03-05', 'Salary Payment March', 2),
(800.00, 8, '2025-04-10', 'Rental Income', 3),
(300.00, 11, '2025-06-12', 'Performance Bonus', 3);

INSERT INTO expenses (amount, category_id, date, description, user_id) VALUES
(1500.00, 4, '2023-01-02', 'Rent January', 2),
(1600.00, 4, '2023-02-02', 'Rent February', 2),
(1700.00, 4, '2023-03-02', 'Rent March', 2),
(200.00, 3, '2023-04-04', 'Groceries April', 2),
(50.00, 24, '2023-06-06', 'Netflix Subscription', 3),
(100.00, 18, '2023-07-08', 'Doctor Visit', 3),
(1500.00, 4, '2024-01-02', 'Rent January', 2),
(1600.00, 4, '2024-02-02', 'Rent February', 2),
(1700.00, 4, '2024-03-02', 'Rent March', 2),
(210.00, 3, '2024-04-04', 'Groceries April', 2),
(55.00, 24, '2024-06-06', 'Netflix Subscription', 3),
(110.00, 18, '2024-07-08', 'Doctor Visit', 3),
(1550.00, 4, '2025-01-02', 'Rent January', 2),
(1650.00, 4, '2025-02-02', 'Rent February', 2),
(1750.00, 4, '2025-03-02', 'Rent March', 2),
(220.00, 3, '2025-04-04', 'Groceries April', 2),
(60.00, 24, '2025-06-06', 'Netflix Subscription', 3),
(120.00, 18, '2025-07-08', 'Doctor Visit', 3);