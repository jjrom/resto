-- Feature types
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('画像', 'image', 'ja', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('データ', 'image', 'ja', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('イメージ', 'image', 'ja', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('ドキュメント', 'document', 'ja', 'type');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('文書', 'document', 'ja', 'type');

-- Feature subtypes
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('光学', 'optical', 'ja', 'subtype');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('レーダ', 'radar', 'ja', 'subtype');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('レーダー', 'radar', 'ja', 'subtype');

-- Location modifiers
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('港', 'coastal', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('沿岸', 'coastal', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('岸', 'coastal', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('北', 'northern', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('北半球', 'northern', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('南', 'southern', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('南半球', 'southern', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('赤道', 'equatorial', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('赤道付近', 'equatorial', 'ja', 'location');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('熱帯雨林', 'tropical', 'ja', 'location');

-- Events
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('サイクロン', 'storm', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('台風', 'storm', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('嵐', 'storm', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('地震', 'earthquake', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('火事', 'fire', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('火災', 'fire', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('洪水', 'flood', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('津波', 'flood', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('土砂崩れ', 'landslide', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('地すべり', 'landslide', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('地滑り', 'landslide', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('オイル流出', 'oilspill', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('油流出', 'oilspill', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('火山噴火', 'eruption', 'ja', 'event');
INSERT INTO resto.keywords (name, value, lang, type) VALUES ('噴火', 'eruption', 'ja', 'event');
