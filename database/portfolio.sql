-- =====================================================
-- Geo | Portfolio  -  database structure + sample data
-- Import with phpMyAdmin (Import tab) into an EMPTY database.
-- NOTE: no administrator account is stored here on purpose.
--       Create it with admin/setup.php (see README).
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admins, login_attempts, settings, profile, skills, projects,
                     education, experience, achievements, messages;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Administrators ----------
CREATE TABLE admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    email         VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,            -- password_hash() output only
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME     NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_username (username),
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Brute-force protection ----------
CREATE TABLE login_attempts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip_address   VARCHAR(45)  NOT NULL,
    username     VARCHAR(150) NOT NULL,
    attempted_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_attempt_ip (ip_address, attempted_at),
    KEY idx_attempt_user (username, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Website settings (key/value) ----------
CREATE TABLE settings (
    setting_key   VARCHAR(50) NOT NULL,
    setting_value TEXT        NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Personal information (always one row, id = 1) ----------
CREATE TABLE profile (
    id             TINYINT UNSIGNED NOT NULL,
    full_name      VARCHAR(100) NOT NULL DEFAULT '',
    display_name   VARCHAR(60)  NOT NULL DEFAULT '',
    role_text      VARCHAR(200) NOT NULL DEFAULT '',
    intro          TEXT         NOT NULL,
    about_heading  VARCHAR(150) NOT NULL DEFAULT '',
    about_text1    TEXT         NOT NULL,
    about_text2    TEXT         NOT NULL,
    profile_image  VARCHAR(255) NULL,
    image_alt      VARCHAR(200) NOT NULL DEFAULT '',
    study_label    VARCHAR(60)  NOT NULL DEFAULT '',
    study_value    VARCHAR(100) NOT NULL DEFAULT '',
    fact_education VARCHAR(120) NOT NULL DEFAULT '',
    fact_focus     VARCHAR(120) NOT NULL DEFAULT '',
    fact_interests VARCHAR(120) NOT NULL DEFAULT '',
    email          VARCHAR(150) NOT NULL DEFAULT '',
    facebook_url   VARCHAR(255) NOT NULL DEFAULT '',
    phone          VARCHAR(40)  NOT NULL DEFAULT '',
    location       VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Skills ----------
CREATE TABLE skills (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category    VARCHAR(60)  NOT NULL,
    name        VARCHAR(80)  NOT NULL,
    description VARCHAR(255) NOT NULL DEFAULT '',
    level       TINYINT UNSIGNED NOT NULL DEFAULT 50,   -- 0 to 100
    sort_order  INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY idx_skill_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Projects ----------
CREATE TABLE projects (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(120) NOT NULL,
    category    VARCHAR(60)  NOT NULL DEFAULT '',
    description TEXT         NOT NULL,
    tags        VARCHAR(255) NOT NULL DEFAULT '',       -- comma separated
    image       VARCHAR(255) NULL,                      -- uploads/xxxx.jpg
    placeholder VARCHAR(20)  NOT NULL DEFAULT 'dashboard',
    project_url VARCHAR(255) NOT NULL DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_project_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- Education / Experience / Achievements (same shape) ----------
CREATE TABLE education (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(150) NOT NULL,
    organization VARCHAR(150) NOT NULL DEFAULT '',
    kicker       VARCHAR(60)  NOT NULL DEFAULT '',
    period       VARCHAR(60)  NOT NULL DEFAULT '',
    description  TEXT         NOT NULL,
    icon         VARCHAR(20)  NOT NULL DEFAULT 'cap',
    image        VARCHAR(255) NULL,
    sort_order   INT NOT NULL DEFAULT 0,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE experience LIKE education;
CREATE TABLE achievements LIKE education;

-- ---------- Contact messages ----------
CREATE TABLE messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL,
    subject    VARCHAR(150)  NOT NULL,
    message    TEXT          NOT NULL,
    ip_address VARCHAR(45)   NOT NULL DEFAULT '',
    is_read    TINYINT(1)    NOT NULL DEFAULT 0,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_msg_read (is_read, created_at),
    KEY idx_msg_ip (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- SAMPLE DATA (taken from the midterm portfolio)
-- =====================================================

INSERT INTO settings (setting_key, setting_value) VALUES
('site_title',       'Geo | Personal Portfolio'),
('meta_description', 'Portfolio of Geo, a BS Information Technology student focused on web development, programming, databases, and networking.'),
('logo_text',        'Geo'),
('logo_image',       ''),
('footer_text',      'Geo'),
('accent_light',     '#17694d'),
('accent_dark',      '#7fdcb7'),
('default_theme',    'system');

INSERT INTO profile (id, full_name, display_name, role_text, intro, about_heading, about_text1, about_text2,
                     profile_image, image_alt, study_label, study_value,
                     fact_education, fact_focus, fact_interests, email, facebook_url, phone, location)
VALUES (1, 'Geo Montianto', 'Geo', 'BSIT Student',
 'I''m an Information Technology student who enjoys creating websites, learning programming, working with databases, and exploring computer networking.',
 'I''m an aspiring IT professional.',
 'I''m currently studying for a Bachelor of Science in Information Technology. I''m interested in web development, programming, databases, networking, and cybersecurity.',
 'I learn best through practical projects. My goal is to keep improving my technical skills and eventually work as an IT professional.',
 'assets/images/GEO.jpg', 'Portrait of Geo standing on a grass field in a floral shirt',
 'Currently studying', 'BS Information Technology',
 'BS Information Technology', 'Web development', 'Programming and networking',
 'Geomontianto@gmail.com', 'https://www.facebook.com/george.montianto', '', '');

INSERT INTO skills (category, name, description, level, sort_order) VALUES
('Web', 'HTML5', 'Structured, semantic web pages.', 90, 10),
('Web', 'CSS3', 'Responsive, modern interfaces.', 85, 20),
('Web', 'JavaScript', 'Interaction and functionality for websites.', 75, 30),
('Programming and data', 'Java', 'Basic applications and programming logic.', 70, 40),
('Programming and data', 'Python', 'Programming fundamentals and problem solving.', 60, 50),
('Programming and data', 'MySQL', 'Databases and SQL queries.', 70, 60),
('Networking and tools', 'Networking', 'IP addressing, VLANs, routing, and topology.', 70, 70),
('Networking and tools', 'Git and GitHub', 'Managing code and publishing projects online.', 65, 80);

INSERT INTO projects (title, category, description, tags, image, placeholder, project_url, sort_order) VALUES
('OJT Monitoring System', 'Web development', 'A web-based system for monitoring OJT activities, records, and schedules.', 'HTML, CSS, JavaScript, MySQL', NULL, 'dashboard', '', 10),
('Online Voting System', 'Java', 'A system concept for managing voters, candidates, votes, and election results.', 'Java, NetBeans, MySQL', NULL, 'ballot', '', 20),
('Personal Portfolio', 'Web development', 'A responsive personal portfolio built with HTML, CSS, JavaScript, PHP, and MySQL.', 'HTML, CSS, JavaScript, PHP, MySQL', NULL, 'portfolio', '', 30),
('Equipment Borrowing System', 'System development', 'A system concept for recording equipment, borrowers, return dates, and availability.', 'HTML, CSS, JavaScript, Database', NULL, 'grid', '', 40);

INSERT INTO education (title, organization, kicker, period, description, icon, image, sort_order) VALUES
('BS Information Technology', '', 'Current', '', 'Building knowledge in programming, web development, databases, and networking.', 'cap', NULL, 10);

INSERT INTO experience (title, organization, kicker, period, description, icon, image, sort_order) VALUES
('Web Development Practice', '', 'Training', '', 'Practicing HTML, CSS, JavaScript, and responsive design.', 'code', NULL, 10),
('Cisco Packet Tracer', '', 'Networking', '', 'Practicing routers, switches, VLANs, IP addressing, routing, and network topologies.', 'code', 'assets/images/cisco.webp', 20);

INSERT INTO achievements (title, organization, kicker, period, description, icon, image, sort_order) VALUES
('Sample achievement (edit or delete me)', '', 'Certificate', '', 'Replace this sample with a real certificate or award from the admin dashboard.', 'award', NULL, 10);
