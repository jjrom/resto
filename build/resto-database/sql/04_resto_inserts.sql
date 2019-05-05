
-- [TABLE resto.right] admin rights
INSERT INTO resto.right
    (groupid, collection, download, visualize, createcollection)
SELECT 0,'*',1,1,1
WHERE
    NOT EXISTS (
        SELECT groupid, collection FROM resto.right WHERE groupid = 0 AND collection = '*'
    );

-- [TABLE resto.right] default licenses
INSERT INTO resto.license (licenseid, viewservice, hastobesigned, description) VALUES ('unlicensed', 'public', 'never', '{"en":{"shortName":"No license"}}') ON CONFLICT (licenseid) DO NOTHING;
INSERT INTO resto.license (licenseid, viewservice, hastobesigned, grantedflags, description) VALUES ('unlicensedwithregistration', 'public', 'never', 'REGISTERED', '{"en":{"shortName":"No license with mandatory registration"}}') ON CONFLICT (licenseid) DO NOTHING;

-- [TABLE resto.group] default groups
INSERT INTO resto.group (id, name, description, created) VALUES (0, 'admin', 'Special group for admin', now()) ON CONFLICT (id) DO NOTHING;
INSERT INTO resto.group (id, name, description, created) VALUES (100, 'default', 'Default group', now()) ON CONFLICT (id) DO NOTHING;
