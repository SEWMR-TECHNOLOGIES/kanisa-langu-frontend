-- ═══════════════════════════════════════════════════════════════════════
-- Kanisa Langu — Complete PostgreSQL Schema (no ALTER statements)
-- All tables fully defined with constraints, indexes, and defaults.
-- ═══════════════════════════════════════════════════════════════════════

-- ── Reference / Lookup Tables ───────────────────────────────────────

CREATE TABLE IF NOT EXISTS regions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS districts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    region_id INT REFERENCES regions(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (name, region_id)
);

CREATE TABLE IF NOT EXISTS titles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS occupations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS banks (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS service_colors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    code VARCHAR(10),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS church_roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS praise_songs (
    id SERIAL PRIMARY KEY,
    song_number INT NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS unit_of_measure (
    id SERIAL PRIMARY KEY,
    unit VARCHAR(50) NOT NULL UNIQUE,
    meaning VARCHAR(200),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Bible Tables ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS bible_books (
    id SERIAL PRIMARY KEY,
    book_number INT NOT NULL UNIQUE,
    name_en VARCHAR(100) NOT NULL,
    name_sw VARCHAR(100) NOT NULL,
    testament VARCHAR(10) NOT NULL CHECK (testament IN ('OT', 'NT')),
    chapter_count INT NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS bible_chapters (
    id SERIAL PRIMARY KEY,
    book_id INT NOT NULL REFERENCES bible_books(id) ON DELETE CASCADE,
    chapter_number INT NOT NULL,
    verse_count INT NOT NULL DEFAULT 1,
    UNIQUE (book_id, chapter_number)
);

CREATE TABLE IF NOT EXISTS bible_verses (
    id SERIAL PRIMARY KEY,
    book_id INT NOT NULL REFERENCES bible_books(id) ON DELETE CASCADE,
    chapter_number INT NOT NULL,
    verse_number INT NOT NULL,
    text_en TEXT,
    text_sw TEXT,
    UNIQUE (book_id, chapter_number, verse_number)
);

-- ── Church Hierarchy ────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS dioceses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    address VARCHAR(255),
    email VARCHAR(150),
    phone VARCHAR(50),
    region_id INT REFERENCES regions(id) ON DELETE SET NULL,
    district_id INT REFERENCES districts(id) ON DELETE SET NULL,
    website VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS provinces (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    diocese_id INT NOT NULL REFERENCES dioceses(id) ON DELETE CASCADE,
    region_id INT REFERENCES regions(id) ON DELETE SET NULL,
    district_id INT REFERENCES districts(id) ON DELETE SET NULL,
    address VARCHAR(255),
    email VARCHAR(150),
    phone VARCHAR(50),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (name, diocese_id)
);

CREATE TABLE IF NOT EXISTS head_parishes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    diocese_id INT NOT NULL REFERENCES dioceses(id) ON DELETE CASCADE,
    province_id INT NOT NULL REFERENCES provinces(id) ON DELETE CASCADE,
    region_id INT REFERENCES regions(id) ON DELETE SET NULL,
    district_id INT REFERENCES districts(id) ON DELETE SET NULL,
    address VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50),
    website VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sub_parishes (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (name, head_parish_id)
);

CREATE TABLE IF NOT EXISTS communities (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT NOT NULL REFERENCES sub_parishes(id) ON DELETE CASCADE,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (name, sub_parish_id)
);

CREATE TABLE IF NOT EXISTS groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (name, head_parish_id)
);

-- ── Admin & Auth ────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS system_admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('super_admin', 'admin')),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS admins (
    id SERIAL PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50) NOT NULL,
    role VARCHAR(30) NOT NULL CHECK (
        role IN ('admin','bishop','secretary','chairperson','accountant','clerk','pastor','evangelist','elder')
    ),
    password VARCHAR(255) NOT NULL,
    signature_path VARCHAR(255),
    admin_level VARCHAR(20) NOT NULL CHECK (
        admin_level IN ('diocese','province','head_parish','sub_parish','community','group')
    ),
    diocese_id INT REFERENCES dioceses(id) ON DELETE CASCADE,
    province_id INT REFERENCES provinces(id) ON DELETE CASCADE,
    head_parish_id INT REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id) ON DELETE CASCADE,
    community_id INT REFERENCES communities(id) ON DELETE CASCADE,
    group_id INT REFERENCES groups(id) ON DELETE CASCADE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    first_login BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_admins_level ON admins(admin_level);
CREATE INDEX IF NOT EXISTS idx_admins_diocese ON admins(diocese_id);
CREATE INDEX IF NOT EXISTS idx_admins_hp ON admins(head_parish_id);

CREATE TABLE IF NOT EXISTS admin_logins (
    id SERIAL PRIMARY KEY,
    admin_id INT REFERENCES admins(id) ON DELETE CASCADE,
    system_admin_id INT REFERENCES system_admins(id) ON DELETE CASCADE,
    login_time TIMESTAMPTZ DEFAULT NOW(),
    ip_address VARCHAR(45),
    user_agent VARCHAR(500)
);

CREATE TABLE IF NOT EXISTS password_reset_codes (
    id SERIAL PRIMARY KEY,
    admin_id INT REFERENCES admins(id) ON DELETE CASCADE,
    system_admin_id INT REFERENCES system_admins(id) ON DELETE CASCADE,
    reset_code VARCHAR(255) NOT NULL,
    expires_at TIMESTAMPTZ NOT NULL,
    used BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Church Members ──────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS church_members (
    id SERIAL PRIMARY KEY,
    title_id INT REFERENCES titles(id) ON DELETE SET NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender VARCHAR(10) NOT NULL CHECK (gender IN ('Male', 'Female')),
    member_type VARCHAR(20) NOT NULL CHECK (member_type IN ('Mgeni', 'Mwenyeji')),
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT NOT NULL REFERENCES sub_parishes(id) ON DELETE CASCADE,
    community_id INT NOT NULL REFERENCES communities(id) ON DELETE CASCADE,
    envelope_number VARCHAR(50) UNIQUE,
    status VARCHAR(20) NOT NULL DEFAULT 'Active' CHECK (status IN ('Active', 'Inactive', 'Excluded')),
    occupation_id INT REFERENCES occupations(id) ON DELETE SET NULL,
    phone VARCHAR(20) UNIQUE,
    email VARCHAR(255) UNIQUE,
    avatar_url VARCHAR(500),
    password VARCHAR(255),
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_members_hp ON church_members(head_parish_id);
CREATE INDEX IF NOT EXISTS idx_members_sp ON church_members(sub_parish_id);
CREATE INDEX IF NOT EXISTS idx_members_comm ON church_members(community_id);
CREATE INDEX IF NOT EXISTS idx_members_envelope ON church_members(envelope_number);
CREATE INDEX IF NOT EXISTS idx_members_phone ON church_members(phone);

CREATE TABLE IF NOT EXISTS member_exclusions (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    reason TEXT NOT NULL,
    excluded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    excluded_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS member_exclusion_reasons (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    reason TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS church_leaders (
    id SERIAL PRIMARY KEY,
    title_id INT REFERENCES titles(id) ON DELETE SET NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    gender VARCHAR(10) NOT NULL CHECK (gender IN ('Male', 'Female')),
    leader_type VARCHAR(20) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    role_id INT NOT NULL REFERENCES church_roles(id) ON DELETE RESTRICT,
    appointment_date DATE NOT NULL,
    end_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS church_choirs (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Financial ───────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS bank_accounts (
    id SERIAL PRIMARY KEY,
    account_name VARCHAR(150) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    bank_id INT NOT NULL REFERENCES banks(id) ON DELETE RESTRICT,
    balance NUMERIC(15,2) NOT NULL DEFAULT 0,
    entity_type VARCHAR(20) NOT NULL CHECK (entity_type IN ('diocese','province','head_parish')),
    entity_id INT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS revenue_streams (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    account_id INT NOT NULL REFERENCES bank_accounts(id) ON DELETE RESTRICT,
    entity_type VARCHAR(20) NOT NULL,
    entity_id INT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS revenues (
    id SERIAL PRIMARY KEY,
    management_level VARCHAR(20) NOT NULL,
    revenue_stream_id INT NOT NULL REFERENCES revenue_streams(id) ON DELETE RESTRICT,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id) ON DELETE SET NULL,
    community_id INT REFERENCES communities(id) ON DELETE SET NULL,
    group_id INT REFERENCES groups(id) ON DELETE SET NULL,
    service_number INT,
    amount NUMERIC(15,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'Cash',
    description TEXT,
    revenue_date DATE NOT NULL,
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    recorded_from VARCHAR(10) DEFAULT 'web',
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    is_posted_to_bank BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_revenues_hp ON revenues(head_parish_id);
CREATE INDEX IF NOT EXISTS idx_revenues_date ON revenues(revenue_date);

CREATE TABLE IF NOT EXISTS expense_groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    management_level VARCHAR(20) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS expense_names (
    id SERIAL PRIMARY KEY,
    expense_group_id INT NOT NULL REFERENCES expense_groups(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    management_level VARCHAR(20) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS expenses (
    id SERIAL PRIMARY KEY,
    management_level VARCHAR(20) NOT NULL,
    expense_name_id INT NOT NULL REFERENCES expense_names(id) ON DELETE RESTRICT,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id) ON DELETE SET NULL,
    community_id INT REFERENCES communities(id) ON DELETE SET NULL,
    group_id INT REFERENCES groups(id) ON DELETE SET NULL,
    amount NUMERIC(15,2) NOT NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'Cash',
    description TEXT,
    expense_date DATE NOT NULL,
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_expenses_hp ON expenses(head_parish_id);

CREATE TABLE IF NOT EXISTS expense_requests (
    id SERIAL PRIMARY KEY,
    management_level VARCHAR(20) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id),
    community_id INT REFERENCES communities(id),
    group_id INT REFERENCES groups(id),
    requested_by INT REFERENCES admins(id) ON DELETE SET NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
    total_amount NUMERIC(15,2) NOT NULL DEFAULT 0,
    notes TEXT,
    responded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    responded_at TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS expense_request_items (
    id SERIAL PRIMARY KEY,
    request_id INT NOT NULL REFERENCES expense_requests(id) ON DELETE CASCADE,
    expense_name_id INT NOT NULL REFERENCES expense_names(id) ON DELETE RESTRICT,
    amount NUMERIC(15,2) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS annual_revenue_targets (
    id SERIAL PRIMARY KEY,
    revenue_stream_id INT NOT NULL REFERENCES revenue_streams(id) ON DELETE CASCADE,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    year INT NOT NULL,
    target_amount NUMERIC(15,2) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (revenue_stream_id, head_parish_id, year)
);

CREATE TABLE IF NOT EXISTS annual_expense_budgets (
    id SERIAL PRIMARY KEY,
    expense_name_id INT NOT NULL REFERENCES expense_names(id) ON DELETE CASCADE,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    year INT NOT NULL,
    budget_amount NUMERIC(15,2) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (expense_name_id, head_parish_id, year)
);

-- ── Harambee ────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS harambees (
    id SERIAL PRIMARY KEY,
    management_level VARCHAR(20) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id) ON DELETE SET NULL,
    community_id INT REFERENCES communities(id) ON DELETE SET NULL,
    group_id INT REFERENCES groups(id) ON DELETE SET NULL,
    account_id INT NOT NULL REFERENCES bank_accounts(id) ON DELETE RESTRICT,
    name VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    amount NUMERIC(15,2) NOT NULL CHECK (amount > 0),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    CHECK (to_date > from_date)
);

CREATE TABLE IF NOT EXISTS harambee_groups (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    target NUMERIC(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS harambee_group_members (
    id SERIAL PRIMARY KEY,
    harambee_group_id INT NOT NULL REFERENCES harambee_groups(id) ON DELETE CASCADE,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    responsibility VARCHAR(50),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS harambee_targets (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id),
    community_id INT REFERENCES communities(id),
    target_type VARCHAR(30) NOT NULL DEFAULT 'individual',
    target NUMERIC(15,2) NOT NULL,
    committee_responsibility VARCHAR(50),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (harambee_id, member_id)
);

CREATE TABLE IF NOT EXISTS harambee_contributions (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    amount NUMERIC(15,2) NOT NULL,
    contribution_date DATE NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id),
    community_id INT REFERENCES communities(id),
    payment_method VARCHAR(30) NOT NULL DEFAULT 'Cash',
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    local_timestamp TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_harambee_contrib_harambee ON harambee_contributions(harambee_id);
CREATE INDEX IF NOT EXISTS idx_harambee_contrib_member ON harambee_contributions(member_id);

CREATE TABLE IF NOT EXISTS harambee_classes (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    class_name VARCHAR(100) NOT NULL,
    min_amount NUMERIC(15,2) NOT NULL DEFAULT 0,
    max_amount NUMERIC(15,2),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS harambee_distributions (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    amount NUMERIC(15,2) NOT NULL,
    distribution_date DATE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS harambee_exclusions (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    reason TEXT NOT NULL,
    excluded_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS harambee_exclusion_reasons (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    reason TEXT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS delayed_harambee_notifications (
    id SERIAL PRIMARY KEY,
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    target VARCHAR(20) NOT NULL,
    contribution_date DATE NOT NULL,
    amount NUMERIC(15,2) NOT NULL,
    contributing_member_name VARCHAR(100),
    mr_and_mrs_name VARCHAR(150),
    is_mr_and_mrs BOOLEAN NOT NULL DEFAULT FALSE,
    is_sent BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS harambee_letter_statuses (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    status VARCHAR(10) NOT NULL DEFAULT 'No' CHECK (status IN ('Yes', 'No')),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (member_id, head_parish_id)
);

CREATE TABLE IF NOT EXISTS harambee_expenses (
    id SERIAL PRIMARY KEY,
    target VARCHAR(20) NOT NULL CHECK (target IN ('head_parish','sub_parish','community','group')),
    harambee_id INT NOT NULL REFERENCES harambees(id) ON DELETE CASCADE,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    expense_name_id INT NOT NULL REFERENCES expense_names(id) ON DELETE RESTRICT,
    amount NUMERIC(15,2) NOT NULL CHECK (amount > 0),
    description TEXT NOT NULL,
    expense_date DATE NOT NULL,
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Envelope ────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS envelope_targets (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    target NUMERIC(15,2) NOT NULL,
    from_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_envelope_targets_member ON envelope_targets(member_id);

CREATE TABLE IF NOT EXISTS envelope_contributions (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    amount NUMERIC(15,2) NOT NULL,
    contribution_date DATE NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id) ON DELETE SET NULL,
    community_id INT REFERENCES communities(id) ON DELETE SET NULL,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'Cash',
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    local_timestamp TIMESTAMPTZ,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_envelope_contrib_member ON envelope_contributions(member_id);

-- ── Sunday Services ─────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS sunday_services (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    service_date DATE NOT NULL,
    service_color_id INT REFERENCES service_colors(id),
    large_liturgy_page_number INT,
    small_liturgy_page_number INT,
    large_antiphony_page_number INT,
    small_antiphony_page_number INT,
    large_praise_page_number INT,
    small_praise_page_number INT,
    base_scripture_text TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS head_parish_service_times (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS head_parish_services_count (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE UNIQUE,
    services_count INT NOT NULL DEFAULT 1,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_scriptures (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    book VARCHAR(100),
    chapter INT,
    verse_from INT,
    verse_to INT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_songs (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    song_id INT REFERENCES praise_songs(id),
    song_title VARCHAR(255),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_choirs (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    choir_id INT NOT NULL REFERENCES church_choirs(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_offerings (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    revenue_stream_id INT NOT NULL REFERENCES revenue_streams(id),
    amount NUMERIC(15,2) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_leaders (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    leader_name VARCHAR(200),
    role VARCHAR(50),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_elders (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    elder_name VARCHAR(200),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sunday_service_preachers (
    id SERIAL PRIMARY KEY,
    service_id INT NOT NULL REFERENCES sunday_services(id) ON DELETE CASCADE,
    service_number INT NOT NULL,
    preacher_name VARCHAR(200),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Payments ────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS payments (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    payment_gateway VARCHAR(30) NOT NULL DEFAULT 'SELCOM',
    merchant_request_id VARCHAR(100),
    checkout_request_id VARCHAR(100),
    transaction_reference VARCHAR(100),
    amount NUMERIC(15,2) NOT NULL,
    payment_reason VARCHAR(50) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'Pending' CHECK (payment_status IN ('Pending','Completed','Failed','Cancelled')),
    harambee_id INT REFERENCES harambees(id) ON DELETE SET NULL,
    service_id INT REFERENCES sunday_services(id) ON DELETE SET NULL,
    revenue_stream_id INT REFERENCES revenue_streams(id) ON DELETE SET NULL,
    payment_date DATE NOT NULL,
    service_date DATE,
    target VARCHAR(20) DEFAULT 'head-parish',
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX IF NOT EXISTS idx_payments_member ON payments(member_id);

CREATE TABLE IF NOT EXISTS payment_gateway_wallets (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    wallet_name VARCHAR(100) NOT NULL,
    wallet_number VARCHAR(50) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Operations ──────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS attendance (
    id SERIAL PRIMARY KEY,
    management_level VARCHAR(20) NOT NULL,
    event_title VARCHAR(200) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    sub_parish_id INT REFERENCES sub_parishes(id),
    community_id INT REFERENCES communities(id),
    group_id INT REFERENCES groups(id),
    service_number INT,
    male_attendance INT NOT NULL DEFAULT 0,
    female_attendance INT NOT NULL DEFAULT 0,
    children_attendance INT NOT NULL DEFAULT 0,
    attendance_date DATE NOT NULL,
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS attendance_benchmarks (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    benchmark INT NOT NULL DEFAULT 0,
    year INT NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (head_parish_id, year)
);

CREATE TABLE IF NOT EXISTS meetings (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    meeting_date DATE NOT NULL,
    meeting_time TIME NOT NULL,
    meeting_place VARCHAR(200) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS meeting_agendas (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL REFERENCES meetings(id) ON DELETE CASCADE,
    agenda_item TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS meeting_minutes (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL REFERENCES meetings(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS meeting_notes (
    id SERIAL PRIMARY KEY,
    meeting_id INT NOT NULL REFERENCES meetings(id) ON DELETE CASCADE,
    note TEXT NOT NULL,
    recorded_by INT REFERENCES admins(id) ON DELETE SET NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS church_events (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS assets (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    generates_revenue BOOLEAN NOT NULL DEFAULT FALSE,
    status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS asset_revenues (
    id SERIAL PRIMARY KEY,
    asset_id INT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    amount NUMERIC(15,2) NOT NULL,
    revenue_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS asset_expenses (
    id SERIAL PRIMARY KEY,
    asset_id INT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    amount NUMERIC(15,2) NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS asset_status_log (
    id SERIAL PRIMARY KEY,
    asset_id INT NOT NULL REFERENCES assets(id) ON DELETE CASCADE,
    status VARCHAR(30) NOT NULL,
    changed_at TIMESTAMPTZ DEFAULT NOW(),
    notes TEXT
);

-- ── Banking ─────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS bank_postings (
    id SERIAL PRIMARY KEY,
    account_id INT NOT NULL REFERENCES bank_accounts(id) ON DELETE RESTRICT,
    amount NUMERIC(15,2) NOT NULL,
    posting_type VARCHAR(10) NOT NULL CHECK (posting_type IN ('credit','debit')),
    reference_type VARCHAR(30),
    reference_id INT,
    description TEXT,
    posted_by INT REFERENCES admins(id) ON DELETE SET NULL,
    posted_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS bank_closing_balances (
    id SERIAL PRIMARY KEY,
    account_id INT NOT NULL REFERENCES bank_accounts(id) ON DELETE CASCADE,
    closing_balance NUMERIC(15,2) NOT NULL,
    balance_date DATE NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Misc ────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS feedback (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    submitted_by_admin_id INT REFERENCES admins(id) ON DELETE SET NULL,
    feedback_type VARCHAR(50) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS fcm_tokens (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    token VARCHAR(500) NOT NULL,
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW(),
    UNIQUE (member_id, head_parish_id)
);

CREATE TABLE IF NOT EXISTS app_versions (
    id SERIAL PRIMARY KEY,
    platform VARCHAR(20) NOT NULL,
    version VARCHAR(20) NOT NULL,
    force_update BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS head_parish_debits (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    description TEXT NOT NULL,
    amount NUMERIC(15,2) NOT NULL CHECK (amount > 0),
    date_debited DATE NOT NULL,
    return_before_date DATE NOT NULL,
    purpose TEXT NOT NULL,
    is_paid BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS member_otp_codes (
    id SERIAL PRIMARY KEY,
    member_id INT NOT NULL REFERENCES church_members(id) ON DELETE CASCADE,
    otp_code VARCHAR(10) NOT NULL,
    purpose VARCHAR(30) NOT NULL CHECK (purpose IN ('registration', 'password_reset')),
    expires_at TIMESTAMPTZ NOT NULL,
    used BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

-- ── Config ──────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS sms_api_config (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE UNIQUE,
    account_name VARCHAR(100) NOT NULL,
    api_username VARCHAR(100) NOT NULL,
    api_password VARCHAR(500) NOT NULL,
    api_token VARCHAR(500),
    sender_id VARCHAR(50),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS revenue_groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS revenue_group_stream_map (
    id SERIAL PRIMARY KEY,
    revenue_group_id INT NOT NULL REFERENCES revenue_groups(id) ON DELETE CASCADE,
    revenue_stream_id INT NOT NULL REFERENCES revenue_streams(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS program_revenue_map (
    id SERIAL PRIMARY KEY,
    head_parish_id INT NOT NULL REFERENCES head_parishes(id) ON DELETE CASCADE,
    program_name VARCHAR(100) NOT NULL,
    revenue_stream_id INT NOT NULL REFERENCES revenue_streams(id) ON DELETE CASCADE
);
