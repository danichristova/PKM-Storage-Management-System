-- Create database schema
-- Jalankan ini di phpMyAdmin pada database yang telah dibuat (DB_NAME pada config.php).

CREATE TABLE IF NOT EXISTS items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by VARCHAR(100) NOT NULL,
  name VARCHAR(200) NOT NULL,
  type VARCHAR(100) NOT NULL,
  rack ENUM('1','2','3') NOT NULL,
  qty INT NOT NULL DEFAULT 0,
  photo_path VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  action ENUM('input','borrow','return','take') NOT NULL,
  actor VARCHAR(100) NOT NULL, -- nama penginput / peminjam / pengambil / pengembali
  qty INT NOT NULL,
  verified_by VARCHAR(100),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS racks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
);

CREATE TABLE pins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pin_code VARCHAR(20) NOT NULL,
    description VARCHAR(100) DEFAULT NULL, -- opsional, biar tahu PIN untuk siapa
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,      -- simpan hash di sini
  role ENUM('admin','superadmin') NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) UNIQUE,
  value TEXT
);

INSERT INTO settings (name, value) VALUES ('notification_email', 'default@gmail.com');



-- Indeks bantu
CREATE INDEX idx_items_name ON items(name);
CREATE INDEX idx_items_rack ON items(rack);
CREATE INDEX idx_logs_item ON logs(item_id);
CREATE INDEX idx_logs_action ON logs(action);
