--
-- resto v6.x to v7.x
--
ALTER TABLE __DATABASE_COMMON_SCHEMA__.right ADD COLUMN IF NOT EXISTS target TEXT;

--
-- Add resto-addon-groups to resto core
-- https://github.com/jjrom/resto/commit/fbf372d856686892b26a3bb573ad0ea2e66a156d
--
ALTER TABLE __DATABASE_COMMON_SCHEMA__.user DROP COLUMN IF EXISTS groups;