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
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS racks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
);

-- Indeks bantu
CREATE INDEX idx_items_name ON items(name);
CREATE INDEX idx_items_rack ON items(rack);
CREATE INDEX idx_logs_item ON logs(item_id);
CREATE INDEX idx_logs_action ON logs(action);
