

ALTER TABLE resto.collection DROP COLUMN title;
ALTER TABLE resto.collection DROP COLUMN description;
ALTER TABLE resto.collection DROP COLUMN keywords;
ALTER TABLE resto.collection ADD COLUMN providers JSON;
ALTER TABLE resto.collection ADD COLUMN properties JSON;


UPDATE resto.collection SET licenseid='proprietary' WHERE licenseid='unlicensed';


