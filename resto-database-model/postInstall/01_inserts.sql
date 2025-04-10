
-- [TABLE __DATABASE_COMMON_SCHEMA__.group] default groups
INSERT INTO __DATABASE_COMMON_SCHEMA__.group (id, name, description, created) VALUES (0, 'admin', 'Special group for admin', now()) ON CONFLICT (id) DO NOTHING;
INSERT INTO __DATABASE_COMMON_SCHEMA__.group (id, name, description, created) VALUES (100, 'default', 'Default group', now()) ON CONFLICT (id) DO NOTHING;

-- [TABLE __DATABASE_COMMON_SCHEMA__.right] admin rights
INSERT INTO __DATABASE_COMMON_SCHEMA__.right (groupid, rights)
VALUES (0, '{"createCollection":true, "deleteAnyCollection": true, "updateAnyCollection": true,"createCatalog":true, "createAnyCatalog":true, "deleteAnyCatalog": true, "updateAnyCatalog": true, "createAnyFeature": true, "deleteAnyFeature": true, "updateAnyFeature": true, "downloadFeature": true}') 
ON CONFLICT(groupid) DO UPDATE SET rights='{"createCollection":true, "deleteAnyCollection": true, "updateAnyCollection": true,"createCatalog":true, "createAnyCatalog":true, "deleteAnyCatalog": true, "updateAnyCatalog": true, "createAnyFeature": true, "deleteAnyFeature": true, "updateAnyFeature": true, "downloadFeature": true}';