-- جدول کاربران
CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (email)
);

-- جدول سایت‌ها
CREATE TABLE IF NOT EXISTS sites (
    id CHAR(36) NOT NULL,
    url VARCHAR(2083) NOT NULL,
    owner_id CHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول دسترسی‌های اشتراکی
CREATE TABLE IF NOT EXISTS site_access (
    site_id CHAR(36) NOT NULL,
    shared_with_email VARCHAR(255) NOT NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
);

-- توکن بازیابی رمز عبور
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token CHAR(64) NOT NULL,
    user_id CHAR(36) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (token),
    KEY (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول کامنت‌ها
CREATE TABLE IF NOT EXISTS comments (
    id CHAR(36) NOT NULL,
    site_id CHAR(36) NOT NULL,
    url VARCHAR(2083) NOT NULL,
    selector TEXT NOT NULL,
    device_type VARCHAR(10) NOT NULL,
    content TEXT NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    author_id CHAR(36) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
);