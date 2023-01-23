--
-- resto v6.x to v7.x SQL migration script
--

ALTER TABLE resto.right ADD COLUMN IF NOT EXISTS target TEXT;

