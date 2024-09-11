-- Step 1: Create the new table
CREATE TABLE resto.catalog_test (
    id TEXT,
    description TEXT
);

-- Step 2 & 3: Recursive query and insert into new table
WITH RECURSIVE cte AS (
    -- Anchor member: Select the root nodes
    SELECT
        id AS original_id,
        id AS newid,
        id AS hashtag,
        description,
        pid
    FROM
        resto.facet
    WHERE
        pid = 'root'
    
    UNION ALL
    
    -- Recursive member: Join with child nodes
    SELECT
        child.id AS original_id,
        CONCAT(cte.newid, '/', child.id) AS newid,
        child.id as hashtag,
        child.description,
        child.pid
    FROM
        resto.facet AS child
        INNER JOIN cte ON cte.original_id = child.pid
)

-- Insert the final results into the new table
-- INSERT INTO resto.catalog_test (id, description)
SELECT
    newid,
    title,
    description,
    level,
    counters,
    owner,
    now() as created,
    links,
    visibility,
    rtype,
    hashtag
FROM
    cte;
