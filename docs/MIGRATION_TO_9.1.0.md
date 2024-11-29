# Migration to resto 9.1.0
After upgrading to resto v9.1.0, you should perform the following SQL requests on the database
    
    --
    -- TO BE RUN FOR resto v9.1.0
    --
    ALTER TABLE resto.collection ADD COLUMN title TEXT;
    ALTER TABLE resto.collection ADD COLUMN description TEXT;

    WITH tmp as (
        SELECT collection, longname, description
        FROM dummy.osdescription
        WHERE lang='en'
    )
    UPDATE dummy.collection
    SET title=tmp.longname, description=tmp.description
    FROM tmp
    WHERE tmp.collection=id;

    DROP TABLE resto.osdescription;
