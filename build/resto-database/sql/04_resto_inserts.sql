
-- [TABLE resto.right] admin rights
INSERT INTO resto.right
    (groupid, collection, download, visualize, createcollection)
SELECT 0,'*',1,1,1
WHERE
    NOT EXISTS (
        SELECT groupid, collection FROM resto.right WHERE groupid = 0 AND collection = '*'
    );

-- [TABLE resto.group] default groups
INSERT INTO resto.group (id, name, description, created) VALUES (0, 'admin', 'Special group for admin', now()) ON CONFLICT (id) DO NOTHING;
INSERT INTO resto.group (id, name, description, created) VALUES (100, 'default', 'Default group', now()) ON CONFLICT (id) DO NOTHING;
