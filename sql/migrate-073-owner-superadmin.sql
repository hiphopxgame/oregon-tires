-- ─── Migration 073: Ensure oregontirespdx@gmail.com is superadmin ─────────
-- This is the business owner account — must always have superadmin access.

INSERT INTO oretir_admins (email, password_hash, display_name, role, language, is_active, created_at, updated_at)
VALUES ('oregontirespdx@gmail.com', '$2y$12$placeholder_hash_never_usable_directly', 'Oregon Tires', 'superadmin', 'both', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE role = 'superadmin', is_active = 1, updated_at = NOW();
