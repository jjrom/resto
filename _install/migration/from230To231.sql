--
-- Migration script from resto v2.2.x to resto v2.3
--
--
ALTER TABLE resto.users RENAME COLUMN givenname TO firstname;
