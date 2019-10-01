
-- [TABLE resto.collection]
CREATE UNIQUE INDEX IF NOT EXISTS idx_name_collection on resto.collection (normalize(name));
CREATE INDEX IF NOT EXISTS idx_lineage_collection ON resto.collection USING GIN (lineage);
CREATE INDEX IF NOT EXISTS idx_visibility_collection ON resto.collection (visibility);
CREATE INDEX IF NOT EXISTS idx_created_collection ON resto.collection (created);

-- [TABLE resto.osdescription]
ALTER TABLE resto.osdescription DROP CONSTRAINT IF EXISTS cl_collection;
ALTER TABLE ONLY resto.osdescription ADD CONSTRAINT cl_collection UNIQUE(collection, lang);

CREATE INDEX IF NOT EXISTS idx_collection_osdescription ON resto.osdescription (collection);
-- CREATE INDEX IF NOT EXISTS idx_lang_osdescription ON resto.osdescription (lang);

-- [TABLE resto.user]
CREATE INDEX IF NOT EXISTS idx_resettoken_user ON resto.user (resettoken);
CREATE INDEX IF NOT EXISTS idx_name_user ON resto.user USING gist (name gist_trgm_ops);

-- [TABLE resto.follower]
CREATE INDEX IF NOT EXISTS idx_userid_follower ON resto.follower (userid);
CREATE INDEX IF NOT EXISTS idx_followerid_follower ON resto.follower (followerid);

-- [TABLE resto.right]
CREATE INDEX IF NOT EXISTS idx_userid_right ON resto.right (userid);
CREATE INDEX IF NOT EXISTS idx_groupid_right ON resto.right (groupid);

-- [TABLE resto.group]
CREATE UNIQUE INDEX IF NOT EXISTS idx_uname_group ON resto.group (normalize(name));
CREATE INDEX idx_name_group ON resto.group USING GIN (normalize(name) gin_trgm_ops);

-- [TABLE resto.facet]
CREATE INDEX IF NOT EXISTS idx_id_facet ON resto.facet (normalize(id));
CREATE INDEX IF NOT EXISTS idx_pid_facet ON resto.facet (normalize(pid));
CREATE INDEX IF NOT EXISTS idx_type_facet ON resto.facet (type);
CREATE INDEX IF NOT EXISTS idx_collection_facet ON resto.facet (normalize(collection));

-- [TABLE resto.feature]
CREATE INDEX IF NOT EXISTS idx_startdate_feature ON resto.feature USING btree (startdate_idx DESC);
CREATE INDEX IF NOT EXISTS idx_created_feature ON resto.feature USING btree (created_idx DESC);
CREATE INDEX IF NOT EXISTS idx_owner_feature ON resto.feature USING btree (owner) WHERE owner IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_visibility_feature ON resto.feature USING btree (visibility);
CREATE INDEX IF NOT EXISTS idx_status_feature ON resto.feature USING btree (status);
CREATE INDEX IF NOT EXISTS idx_centroid_feature ON resto.feature USING GIST (centroid);
CREATE INDEX IF NOT EXISTS idx_nhashtags_feature ON resto.feature USING GIN (normalized_hashtags) WHERE normalized_hashtags IS NOT NULL;

-- [TABLE resto.geometry_part]
CREATE INDEX IF NOT EXISTS idx_id_geometry_part ON resto.geometry_part USING HASH (id);
CREATE INDEX IF NOT EXISTS idx_geom_geometry_part ON resto.geometry_part USING GIST (geom);

-- [TABLE resto.log]
CREATE INDEX IF NOT EXISTS idx_userid_log ON resto.log (userid);
CREATE INDEX IF NOT EXISTS idx_querytime_log ON resto.log (querytime);

-- [TABLE resto.feature_landcover]
CREATE INDEX IF NOT EXISTS idx_forest_m_landcover ON resto.feature_landcover USING btree (forest);
CREATE INDEX IF NOT EXISTS idx_urban_m_landcover ON resto.feature_landcover USING btree (urban);

-- [TABLE resto.feature_optical]
CREATE INDEX IF NOT EXISTS idx_cloudcover_m_optical ON resto.feature_optical USING btree (cloudcover);

