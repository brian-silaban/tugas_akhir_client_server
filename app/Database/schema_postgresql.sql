-- ============================================================================
-- IndWalk: Explore Indonesia Together
-- Native PostgreSQL Schema (7 Tabel Inti)
-- Catatan: Anda bisa menggunakan `php spark migrate` ATAU mengeksekusi file SQL
-- ini secara langsung di PostgreSQL (pgAdmin / DBeaver / Supabase / psql).
-- ============================================================================

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'member' CHECK (role IN ('admin', 'member')),
    avatar VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Categories
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabel Destinations
CREATE TABLE IF NOT EXISTS destinations (
    id SERIAL PRIMARY KEY,
    category_id INTEGER NOT NULL REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    city VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    address TEXT NULL,
    latitude DECIMAL(10, 7) NULL,
    longitude DECIMAL(10, 7) NULL,
    ticket_price INTEGER NOT NULL DEFAULT 0,
    open_time TIME NULL,
    close_time TIME NULL,
    description TEXT NOT NULL,
    facilities TEXT NULL,
    thumbnail VARCHAR(255) NOT NULL,
    rating_avg DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    review_count INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_destinations_city ON destinations(city);
CREATE INDEX IF NOT EXISTS idx_destinations_category_id ON destinations(category_id);

-- 4. Tabel Destination Galleries
CREATE TABLE IF NOT EXISTS destination_galleries (
    id SERIAL PRIMARY KEY,
    destination_id INTEGER NOT NULL REFERENCES destinations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    image_url VARCHAR(255) NOT NULL,
    caption VARCHAR(180) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_destination_galleries_destination_id ON destination_galleries(destination_id);

-- 5. Tabel Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    booking_code VARCHAR(40) NOT NULL UNIQUE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    destination_id INTEGER NOT NULL REFERENCES destinations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    visit_date DATE NOT NULL,
    visitor_count INTEGER NOT NULL DEFAULT 1,
    total_price INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'cancelled')),
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_bookings_user_id ON bookings(user_id);
CREATE INDEX IF NOT EXISTS idx_bookings_destination_id ON bookings(destination_id);

-- 6. Tabel Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    destination_id INTEGER NOT NULL REFERENCES destinations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    rating SMALLINT NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_reviews_user_id ON reviews(user_id);
CREATE INDEX IF NOT EXISTS idx_reviews_destination_id ON reviews(destination_id);

-- 7. Tabel Wishlists
CREATE TABLE IF NOT EXISTS wishlists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
    destination_id INTEGER NOT NULL REFERENCES destinations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_wishlists_user_destination UNIQUE (user_id, destination_id)
);
