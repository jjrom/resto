-- Feature types
--INSERT INTO resto.keywords (name, value, lang, type) VALUES ('image', 'image', 'en', 'type');
--INSERT INTO resto.keywords (name, value, lang, type) VALUES ('images', 'image', 'en', 'type');
--INSERT INTO resto.keywords (name, value, lang, type) VALUES ('imagery', 'image', 'en', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('document', 'document', 'en', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('documents', 'document', 'en', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('report', 'document', 'en', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('reports', 'document', 'en', 'type');

-- Feature subtypes
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('optical', 'optical', 'en', 'subtype');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('radar', 'radar', 'en', 'subtype');

-- Location modifiers
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('coastal', 'coastal', 'en', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('coastal area', 'coastal', 'en', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('northern hemisphere', 'northern', 'en', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('southern hemisphere', 'southern', 'en', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('equatorial zone', 'equatorial', 'en', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('tropical zone', 'tropical', 'en', 'location');

-- Events
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Cyclone', 'storm', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Typhoon', 'storm', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Huricane', 'storm', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Storm', 'storm', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Earthquake', 'earthquake', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Seism', 'earthquake', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Fire', 'fire', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Flood', 'flood', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Tsunami', 'flood', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Drift', 'flood', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Inundation', 'flood', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Landslide', 'landslide', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Oil spill', 'oilspill', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Volcanic eruption', 'eruption', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Volcano eruption', 'eruption', 'en', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('Eruption', 'eruption', 'en', 'event');
