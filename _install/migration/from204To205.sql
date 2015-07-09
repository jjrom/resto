-- Update user profiles
ALTER table usermanagement.users ADD COLUMN validatedby TEXT;
ALTER table usermanagement.users ADD COLUMN validationdate TIMESTAMP;
ALTER table usermanagement.users ADD COLUMN flags TEXT;
ALTER table usermanagement.users ADD COLUMN organizationcountry TEXT;

-- Change from visibility to groupid
ALTER table resto.features RENAME COLUMN visibility TO groupid;
ALTER table resto.features ALTER COLUMN groupid SET DEFAULT 'public'::text;
UPDATE resto.features SET groupid='public' where groupid='PUBLIC';


