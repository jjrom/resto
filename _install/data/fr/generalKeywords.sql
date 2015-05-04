-- Feature types
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('image', 'image', 'fr', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('images', 'image', 'fr', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('imagerie', 'image', 'fr', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('document', 'document', 'fr', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('documents', 'document', 'fr', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('rapport', 'document', 'fr', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('rapports', 'document', 'fr', 'type');

-- Feature subtypes
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('optique', 'optical', 'fr', 'subtype');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('radar', 'radar', 'fr', 'subtype');

-- Location modifiers
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('côtier', 'coastal', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('côtière', 'coastal', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('littoral', 'coastal', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('littorale', 'coastal', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('hémisphere nord', 'northern', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('hémisphere sud', 'southern', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('zone equatoriale', 'equatorial', 'fr', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('zone tropicale', 'tropical', 'fr', 'location');

-- Events
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Cyclone', 'storm', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Typhon', 'storm', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Tempête', 'storm', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Tremblement de terre', 'earthquake', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Séisme', 'earthquake', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Feu', 'fire', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Innondation', 'flood', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Tsunami', 'flood', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Glissement de terrain', 'landslide', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Marée noire', 'oilspill', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Eruption volcanique', 'eruption', 'fr', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Eruption', 'eruption', 'fr', 'event');
