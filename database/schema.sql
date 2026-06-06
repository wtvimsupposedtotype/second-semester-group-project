-- 1. Users Table (Admin vs Cashier)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') DEFAULT 'cashier',
    -- 'pending' cashiers cannot log in until an admin approves them.
    status ENUM('pending', 'approved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- If your users table ALREADY EXISTS in phpMyAdmin, run these two lines once
-- (in the phpMyAdmin "SQL" tab) instead of re-creating the table:
--   ALTER TABLE users ADD COLUMN status ENUM('pending','approved') DEFAULT 'pending';
--   UPDATE users SET status = 'approved';   -- keeps your current admin/cashier able to log in

-- 2. Products Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    sku VARCHAR(50) UNIQUE,
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,        -- selling price (what the customer pays)
    cost_price DECIMAL(10,2) DEFAULT 0,  -- what it costs you (for profit calc)
    quantity INT NOT NULL,
    low_stock_threshold INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Sales Table
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total_amount DECIMAL(10,2),          -- grand total the customer paid (incl. tax)
    tax_amount DECIMAL(10,2) DEFAULT 0,  -- tax portion; net revenue = total_amount - tax_amount
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 4. Audit Logs (The "Audit Trail")
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 5. Sale Items (the individual line items inside each sale)
CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_each DECIMAL(10,2) NOT NULL,   -- selling price at the moment of sale
    cost_each DECIMAL(10,2) DEFAULT 0,   -- cost at the moment of sale (profit = price - cost)
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- 6. Settings (simple key/value store for app-wide configuration)
CREATE TABLE settings (
    setting_key   VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
);
INSERT INTO settings (setting_key, setting_value) VALUES
    ('store_name',        'SIBS Ltd'),
    ('currency',          'LKR'),
    ('address',           'Colombo, Sri Lanka'),
    ('tax_rate',          '0'),
    ('low_stock_default', '10');