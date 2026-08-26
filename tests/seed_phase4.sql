-- Test Fixture for Phase 4 Authentication
-- Run this manually if you want to test the fingerprint mapping using the dummy payload.

INSERT INTO users (username, display_name, role, is_active, created_at)
VALUES ('sachinadmin', 'Sachin Admin', 'ROLE_OPERATOR', 1, NOW());

-- Assuming the user ID is 1 (adjust if needed):
INSERT INTO terminals (terminal_code, terminal_name, fingerprint_device_ip, is_active, created_at)
VALUES ('TERMINAL-01', 'Main CockpitConsole Dashboard', '192.168.1.205', 1, NOW());

INSERT INTO fingerprint_user_mappings (user_id, essl_username, machine_ip, is_active, created_at)
VALUES (
    (SELECT id FROM users WHERE username = 'sachinadmin' LIMIT 1),
    'Sachinadmin',
    '192.168.1.205',
    1,
    NOW()
);
