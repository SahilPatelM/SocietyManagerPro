-- =============================================================================
-- Society Manager Pro — Supabase PostgreSQL (schema + demo seed)
-- Run in: Supabase Dashboard → SQL Editor → New query → Run
--
-- Demo login (password for all: password)
--   Admin:     mobile 9876543210  |  admin@society.local
--   Treasurer: mobile 9876543211  |  treasurer@society.local
--   Member 1:  mobile 9800000001  |  member1@society.local
-- =============================================================================

BEGIN;

-- ---------------------------------------------------------------------------
-- Clean slate (remove if you already have data you want to keep)
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS poll_votes CASCADE;
DROP TABLE IF EXISTS poll_options CASCADE;
DROP TABLE IF EXISTS polls CASCADE;
DROP TABLE IF EXISTS audit_logs CASCADE;
DROP TABLE IF EXISTS parking_allocations CASCADE;
DROP TABLE IF EXISTS parking_slots CASCADE;
DROP TABLE IF EXISTS documents CASCADE;
DROP TABLE IF EXISTS complaint_attachments CASCADE;
DROP TABLE IF EXISTS complaints CASCADE;
DROP TABLE IF EXISTS visitors CASCADE;
DROP TABLE IF EXISTS user_notifications CASCADE;
DROP TABLE IF EXISTS announcement_targets CASCADE;
DROP TABLE IF EXISTS announcements CASCADE;
DROP TABLE IF EXISTS maintenance_payments CASCADE;
DROP TABLE IF EXISTS maintenance_bills CASCADE;
DROP TABLE IF EXISTS maintenance_cycles CASCADE;
DROP TABLE IF EXISTS transaction_attachments CASCADE;
DROP TABLE IF EXISTS financial_transactions CASCADE;
DROP TABLE IF EXISTS vehicles CASCADE;
DROP TABLE IF EXISTS family_members CASCADE;
DROP TABLE IF EXISTS otp_verifications CASCADE;
DROP TABLE IF EXISTS houses CASCADE;
DROP TABLE IF EXISTS blocks CASCADE;
DROP TABLE IF EXISTS societies CASCADE;
DROP TABLE IF EXISTS role_has_permissions CASCADE;
DROP TABLE IF EXISTS model_has_roles CASCADE;
DROP TABLE IF EXISTS model_has_permissions CASCADE;
DROP TABLE IF EXISTS roles CASCADE;
DROP TABLE IF EXISTS permissions CASCADE;
DROP TABLE IF EXISTS personal_access_tokens CASCADE;
DROP TABLE IF EXISTS failed_jobs CASCADE;
DROP TABLE IF EXISTS job_batches CASCADE;
DROP TABLE IF EXISTS jobs CASCADE;
DROP TABLE IF EXISTS cache_locks CASCADE;
DROP TABLE IF EXISTS cache CASCADE;
DROP TABLE IF EXISTS sessions CASCADE;
DROP TABLE IF EXISTS password_reset_tokens CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS migrations CASCADE;

-- ---------------------------------------------------------------------------
-- Laravel core tables
-- ---------------------------------------------------------------------------

CREATE TABLE migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NULL,
    name VARCHAR(255) NOT NULL,
    house_id BIGINT NULL,
    email VARCHAR(255) NULL,
    mobile VARCHAR(15) NULL,
    alternate_mobile VARCHAR(15) NULL,
    block_wing VARCHAR(255) NULL,
    address TEXT NULL,
    profile_photo VARCHAR(255) NULL,
    email_verified_at TIMESTAMP(0) NULL,
    password VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    locale VARCHAR(5) NOT NULL DEFAULT 'en',
    fcm_token VARCHAR(255) NULL,
    emergency_contact_name VARCHAR(255) NULL,
    emergency_mobile VARCHAR(15) NULL,
    mobile_verified_at TIMESTAMP(0) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT users_email_unique UNIQUE (email),
    CONSTRAINT users_mobile_unique UNIQUE (mobile)
);

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) NULL
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE INDEX cache_expiration_index ON cache (expiration);

CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);
CREATE INDEX cache_locks_expiration_index ON cache_locks (expiration);

CREATE TABLE jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX jobs_queue_index ON jobs (queue);

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

CREATE TABLE failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP(0) NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP(0) NULL,
    expires_at TIMESTAMP(0) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);
CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index
    ON personal_access_tokens (tokenable_type, tokenable_id);
CREATE INDEX personal_access_tokens_expires_at_index ON personal_access_tokens (expires_at);

-- Spatie permissions
CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name)
);

CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name)
);

CREATE TABLE model_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions (id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);
CREATE INDEX model_has_permissions_model_id_model_type_index
    ON model_has_permissions (model_id, model_type);

CREATE TABLE model_has_roles (
    role_id BIGINT NOT NULL REFERENCES roles (id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);
CREATE INDEX model_has_roles_model_id_model_type_index
    ON model_has_roles (model_id, model_type);

CREATE TABLE role_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions (id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles (id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

-- ---------------------------------------------------------------------------
-- Society Manager schema
-- ---------------------------------------------------------------------------

CREATE TABLE societies (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NULL,
    city VARCHAR(255) NULL,
    registration_number VARCHAR(255) NULL,
    opening_balance DECIMAL(14, 2) NOT NULL DEFAULT 0,
    logo VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE blocks (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(255) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE houses (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    block_id BIGINT NULL REFERENCES blocks (id) ON DELETE SET NULL,
    house_number VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'occupied' CHECK (status IN ('occupied', 'vacant')),
    owner_user_id BIGINT NULL,
    outstanding_amount DECIMAL(14, 2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(14, 2) NOT NULL DEFAULT 0,
    last_payment_date DATE NULL,
    qr_code VARCHAR(255) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT houses_society_id_house_number_unique UNIQUE (society_id, house_number)
);

ALTER TABLE users
    ADD CONSTRAINT users_society_id_foreign FOREIGN KEY (society_id) REFERENCES societies (id) ON DELETE SET NULL,
    ADD CONSTRAINT users_house_id_foreign FOREIGN KEY (house_id) REFERENCES houses (id) ON DELETE SET NULL;

ALTER TABLE houses
    ADD CONSTRAINT houses_owner_user_id_foreign FOREIGN KEY (owner_user_id) REFERENCES users (id) ON DELETE SET NULL;

CREATE TABLE otp_verifications (
    id BIGSERIAL PRIMARY KEY,
    mobile VARCHAR(15) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP(0) NOT NULL,
    is_used BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE family_members (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    relation VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NULL,
    age SMALLINT NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE vehicles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    vehicle_type VARCHAR(255) NOT NULL,
    car_number VARCHAR(255) NULL,
    bike_number VARCHAR(255) NULL,
    vehicle_image VARCHAR(255) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE financial_transactions (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    type VARCHAR(255) NOT NULL CHECK (type IN ('income', 'expense')),
    category VARCHAR(255) NOT NULL,
    subcategory VARCHAR(255) NULL,
    amount DECIMAL(14, 2) NOT NULL,
    transaction_date DATE NOT NULL,
    payment_method VARCHAR(255) NULL,
    reference_number VARCHAR(255) NULL,
    description TEXT NULL,
    house_id BIGINT NULL REFERENCES houses (id) ON DELETE SET NULL,
    created_by BIGINT NULL REFERENCES users (id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);
CREATE INDEX financial_transactions_society_id_type_transaction_date_index
    ON financial_transactions (society_id, type, transaction_date);

CREATE TABLE transaction_attachments (
    id BIGSERIAL PRIMARY KEY,
    financial_transaction_id BIGINT NOT NULL REFERENCES financial_transactions (id) ON DELETE CASCADE,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(255) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE maintenance_cycles (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    month_year VARCHAR(7) NOT NULL,
    cycle_type VARCHAR(20) NOT NULL DEFAULT 'general',
    amount DECIMAL(14, 2) NOT NULL,
    late_fee DECIMAL(14, 2) NOT NULL DEFAULT 0,
    due_date DATE NOT NULL,
    bills_generated BOOLEAN NOT NULL DEFAULT FALSE,
    notifications_sent_at TIMESTAMP(0) NULL,
    created_by BIGINT NULL REFERENCES users (id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT maintenance_cycles_society_id_month_year_cycle_type_unique
        UNIQUE (society_id, month_year, cycle_type)
);
CREATE INDEX maintenance_cycles_society_id_index ON maintenance_cycles (society_id);

CREATE TABLE maintenance_bills (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    house_id BIGINT NOT NULL REFERENCES houses (id) ON DELETE CASCADE,
    bill_number VARCHAR(255) NOT NULL UNIQUE,
    month_year VARCHAR(7) NOT NULL,
    bill_type VARCHAR(20) NOT NULL DEFAULT 'general',
    maintenance_amount DECIMAL(14, 2) NOT NULL,
    late_fee DECIMAL(14, 2) NOT NULL DEFAULT 0,
    due_date DATE NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'partial', 'paid', 'overdue')),
    paid_amount DECIMAL(14, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE maintenance_payments (
    id BIGSERIAL PRIMARY KEY,
    maintenance_bill_id BIGINT NOT NULL REFERENCES maintenance_bills (id) ON DELETE CASCADE,
    house_id BIGINT NOT NULL REFERENCES houses (id) ON DELETE CASCADE,
    amount DECIMAL(14, 2) NOT NULL,
    payment_method VARCHAR(255) NOT NULL CHECK (payment_method IN ('cash', 'upi', 'bank_transfer')),
    receipt_number VARCHAR(255) NULL,
    receipt_path VARCHAR(255) NULL,
    payment_date DATE NOT NULL,
    received_by BIGINT NULL REFERENCES users (id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE announcements (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    created_by BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type VARCHAR(255) NOT NULL DEFAULT 'text' CHECK (type IN ('text', 'image', 'pdf', 'emergency')),
    image VARCHAR(255) NULL,
    attachment VARCHAR(255) NULL,
    is_emergency BOOLEAN NOT NULL DEFAULT FALSE,
    scheduled_at TIMESTAMP(0) NULL,
    sent_at TIMESTAMP(0) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE announcement_targets (
    id BIGSERIAL PRIMARY KEY,
    announcement_id BIGINT NOT NULL REFERENCES announcements (id) ON DELETE CASCADE,
    target_type VARCHAR(255) NOT NULL CHECK (target_type IN ('all', 'house', 'block')),
    target_id BIGINT NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE user_notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    body TEXT NULL,
    type VARCHAR(255) NOT NULL,
    data JSONB NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    delivery_status VARCHAR(255) NOT NULL DEFAULT 'pending'
        CHECK (delivery_status IN ('pending', 'sent', 'failed')),
    read_at TIMESTAMP(0) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE visitors (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    house_id BIGINT NOT NULL REFERENCES houses (id) ON DELETE CASCADE,
    visitor_name VARCHAR(255) NOT NULL,
    mobile VARCHAR(15) NULL,
    vehicle_number VARCHAR(255) NULL,
    entry_time TIMESTAMP(0) NOT NULL,
    exit_time TIMESTAMP(0) NULL,
    logged_by BIGINT NULL REFERENCES users (id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE complaints (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    house_id BIGINT NULL REFERENCES houses (id) ON DELETE SET NULL,
    complaint_number VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(255) NOT NULL
        CHECK (category IN ('water', 'electricity', 'security', 'parking', 'cleaning', 'other')),
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'in_progress', 'resolved')),
    admin_remarks TEXT NULL,
    resolved_at TIMESTAMP(0) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE complaint_attachments (
    id BIGSERIAL PRIMARY KEY,
    complaint_id BIGINT NOT NULL REFERENCES complaints (id) ON DELETE CASCADE,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(255) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE documents (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    uploaded_by BIGINT NULL REFERENCES users (id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL
        CHECK (category IN ('rules', 'meeting_minutes', 'agm', 'audit', 'receipt', 'other')),
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(255) NULL,
    file_size BIGINT NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE parking_slots (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    slot_number VARCHAR(255) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'available' CHECK (status IN ('available', 'occupied')),
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT parking_slots_society_id_slot_number_unique UNIQUE (society_id, slot_number)
);

CREATE TABLE parking_allocations (
    id BIGSERIAL PRIMARY KEY,
    parking_slot_id BIGINT NOT NULL REFERENCES parking_slots (id) ON DELETE CASCADE,
    house_id BIGINT NOT NULL REFERENCES houses (id) ON DELETE CASCADE,
    vehicle_number VARCHAR(255) NOT NULL,
    allocated_from DATE NOT NULL,
    allocated_until DATE NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NULL REFERENCES users (id) ON DELETE SET NULL,
    action VARCHAR(255) NOT NULL,
    model_type VARCHAR(255) NULL,
    model_id BIGINT NULL,
    old_values JSONB NULL,
    new_values JSONB NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE polls (
    id BIGSERIAL PRIMARY KEY,
    society_id BIGINT NOT NULL REFERENCES societies (id) ON DELETE CASCADE,
    created_by BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'active' CHECK (status IN ('active', 'closed')),
    ends_at TIMESTAMP(0) NULL,
    published_at TIMESTAMP(0) NULL,
    notifications_sent_at TIMESTAMP(0) NULL,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE poll_options (
    id BIGSERIAL PRIMARY KEY,
    poll_id BIGINT NOT NULL REFERENCES polls (id) ON DELETE CASCADE,
    label VARCHAR(255) NOT NULL,
    votes_count INTEGER NOT NULL DEFAULT 0,
    sort_order SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL
);

CREATE TABLE poll_votes (
    id BIGSERIAL PRIMARY KEY,
    poll_id BIGINT NOT NULL REFERENCES polls (id) ON DELETE CASCADE,
    poll_option_id BIGINT NOT NULL REFERENCES poll_options (id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users (id) ON DELETE CASCADE,
    created_at TIMESTAMP(0) NULL,
    updated_at TIMESTAMP(0) NULL,
    CONSTRAINT poll_votes_poll_id_user_id_unique UNIQUE (poll_id, user_id)
);

-- ---------------------------------------------------------------------------
-- Demo seed data
-- ---------------------------------------------------------------------------

-- bcrypt hash of "password"
-- $2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a

INSERT INTO roles (id, name, guard_name, created_at, updated_at) VALUES
    (1, 'super_admin',   'web', NOW(), NOW()),
    (2, 'society_admin', 'web', NOW(), NOW()),
    (3, 'treasurer',     'web', NOW(), NOW()),
    (4, 'member',        'web', NOW(), NOW());
SELECT setval('roles_id_seq', (SELECT MAX(id) FROM roles));

INSERT INTO societies (id, name, address, city, opening_balance, is_active, created_at, updated_at) VALUES
    (1, 'Shree Krishna Residency', 'Ahmedabad, Gujarat', 'Ahmedabad', 50000.00, TRUE, NOW(), NOW());
SELECT setval('societies_id_seq', (SELECT MAX(id) FROM societies));

INSERT INTO blocks (id, society_id, name, code, created_at, updated_at) VALUES
    (1, 1, 'Block A', 'A', NOW(), NOW()),
    (2, 1, 'Block B', 'B', NOW(), NOW());
SELECT setval('blocks_id_seq', (SELECT MAX(id) FROM blocks));

INSERT INTO users (id, society_id, name, email, mobile, password, locale, status, created_at, updated_at) VALUES
    (1, 1, 'Society Admin', 'admin@society.local',     '9876543210', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'en', 'active', NOW(), NOW()),
    (2, 1, 'Treasurer',     'treasurer@society.local', '9876543211', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'gu', 'active', NOW(), NOW());
SELECT setval('users_id_seq', 2);

INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
    (2, 'App\Models\User', 1),
    (3, 'App\Models\User', 2);

INSERT INTO houses (id, society_id, block_id, house_number, status, created_at, updated_at) VALUES
    (1,  1, 1, 'A-01', 'occupied', NOW(), NOW()),
    (2,  1, 1, 'A-02', 'occupied', NOW(), NOW()),
    (3,  1, 1, 'A-03', 'occupied', NOW(), NOW()),
    (4,  1, 1, 'A-04', 'occupied', NOW(), NOW()),
    (5,  1, 1, 'A-05', 'occupied', NOW(), NOW()),
    (6,  1, 2, 'B-06', 'occupied', NOW(), NOW()),
    (7,  1, 2, 'B-07', 'occupied', NOW(), NOW()),
    (8,  1, 2, 'B-08', 'occupied', NOW(), NOW()),
    (9,  1, 2, 'B-09', 'vacant',   NOW(), NOW()),
    (10, 1, 2, 'B-10', 'vacant',   NOW(), NOW());

INSERT INTO users (id, society_id, house_id, name, email, mobile, password, status, created_at, updated_at) VALUES
    (3,  1, 1,  'Member 1', 'member1@society.local', '9800000001', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (4,  1, 2,  'Member 2', 'member2@society.local', '9800000002', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (5,  1, 3,  'Member 3', 'member3@society.local', '9800000003', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (6,  1, 4,  'Member 4', 'member4@society.local', '9800000004', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (7,  1, 5,  'Member 5', 'member5@society.local', '9800000005', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (8,  1, 6,  'Member 6', 'member6@society.local', '9800000006', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (9,  1, 7,  'Member 7', 'member7@society.local', '9800000007', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW()),
    (10, 1, 8,  'Member 8', 'member8@society.local', '9800000008', '$2y$10$9b7vj532hOPIIEpfCx9p4uwtemwpcOs6exALzjPAMiPOMZ9KSs18a', 'active', NOW(), NOW());
SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT 4, 'App\Models\User', id FROM users WHERE id BETWEEN 3 AND 10;

UPDATE houses SET owner_user_id = id WHERE id BETWEEN 1 AND 8;

INSERT INTO financial_transactions (society_id, type, category, amount, transaction_date, created_by, created_at, updated_at) VALUES
    (1, 'income',  'maintenance',      25000.00, CURRENT_DATE, 1, NOW(), NOW()),
    (1, 'expense', 'security_salary',   8000.00, CURRENT_DATE, 2, NOW(), NOW());

INSERT INTO maintenance_cycles (society_id, month_year, cycle_type, amount, late_fee, due_date, bills_generated, created_by, created_at, updated_at)
VALUES (
    1,
    TO_CHAR(CURRENT_DATE, 'YYYY-MM'),
    'general',
    1500.00,
    100.00,
    (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month' - INTERVAL '1 day')::DATE,
    TRUE,
    1,
    NOW(),
    NOW()
);

INSERT INTO maintenance_bills (society_id, house_id, bill_number, month_year, bill_type, maintenance_amount, late_fee, due_date, status, paid_amount, created_at, updated_at)
SELECT
    1,
    h.id,
    'MB-' || REPLACE(TO_CHAR(CURRENT_DATE, 'YYYY-MM'), '-', '') || '-' || h.house_number,
    TO_CHAR(CURRENT_DATE, 'YYYY-MM'),
    'general',
    1500.00,
    0.00,
    (DATE_TRUNC('month', CURRENT_DATE) + INTERVAL '1 month' - INTERVAL '1 day')::DATE,
    CASE WHEN h.id <= 3 THEN 'paid' ELSE 'pending' END,
    CASE WHEN h.id <= 3 THEN 1500.00 ELSE 0.00 END,
    NOW(),
    NOW()
FROM houses h
WHERE h.society_id = 1;

UPDATE houses h SET
    outstanding_amount = CASE WHEN h.id <= 3 THEN 0.00 ELSE 1500.00 END,
    paid_amount = CASE WHEN h.id <= 3 THEN 1500.00 ELSE 0.00 END,
    last_payment_date = CASE WHEN h.id <= 3 THEN CURRENT_DATE ELSE NULL END
WHERE h.society_id = 1;

INSERT INTO maintenance_payments (maintenance_bill_id, house_id, amount, payment_method, receipt_number, payment_date, received_by, created_at, updated_at)
SELECT
    mb.id,
    mb.house_id,
    1500.00,
    CASE WHEN mb.house_id = 1 THEN 'cash' ELSE 'upi' END,
    'RCP-DEMO-' || mb.house_id,
    CURRENT_DATE,
    2,
    NOW(),
    NOW()
FROM maintenance_bills mb
WHERE mb.house_id <= 3;

INSERT INTO parking_slots (society_id, slot_number, status, created_at, updated_at) VALUES
    (1, 'P-01', 'available', NOW(), NOW()),
    (1, 'P-02', 'available', NOW(), NOW()),
    (1, 'P-03', 'available', NOW(), NOW()),
    (1, 'P-04', 'available', NOW(), NOW());

INSERT INTO announcements (society_id, created_by, title, description, type, sent_at, created_at, updated_at) VALUES
    (1, 1, 'Welcome to Society Manager Pro', 'Use this app for maintenance, complaints, visitors, and announcements.', 'text', NOW(), NOW(), NOW());

-- Mark Laravel migrations as applied
INSERT INTO migrations (migration, batch) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_06_04_092641_create_personal_access_tokens_table', 1),
    ('2026_06_04_092642_create_permission_tables', 1),
    ('2026_06_04_100000_create_society_manager_schema', 1),
    ('2026_06_04_100001_make_users_email_nullable', 1),
    ('2026_06_04_120000_create_maintenance_cycles_table', 1),
    ('2026_06_04_130000_add_maintenance_cycle_and_bill_types', 1),
    ('2026_06_04_140000_create_polls_tables', 1),
    ('2026_06_04_150000_add_parking_allocation_hours', 1),
    ('2026_06_04_160000_fix_maintenance_cycles_unique_index', 1),
    ('2026_06_04_170000_remove_parking_allocation_hours', 1);

COMMIT;
