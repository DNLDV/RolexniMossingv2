# RolexniMossingv2

rolex_ng_mahirap 

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Creates "admins" table
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL
);

-- Insert default admin account (example only)
INSERT INTO admins (username, password)
VALUES ('admin', 'adminpass');


ALTER TABLE users
  ADD COLUMN is_verified TINYINT(1) DEFAULT 0,
  ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL;

  ALTER TABLE users
  ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL,
  ADD COLUMN reset_expires DATETIME DEFAULT NULL;