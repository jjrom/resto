--
-- Migration script from resto v2.2.x to resto v2.3
--
--
ALTER TABLE usermanagement.users SET SCHEMA resto;
ALTER TABLE usermanagement.groups SET SCHEMA resto;
ALTER TABLE usermanagement.rights SET SCHEMA resto;
ALTER TABLE usermanagement.signatures SET SCHEMA resto;
ALTER TABLE usermanagement.history SET SCHEMA resto;
ALTER TABLE usermanagement.cart SET SCHEMA resto;
ALTER TABLE usermanagement.orders SET SCHEMA resto;
ALTER TABLE usermanagement.sharedlinks SET SCHEMA resto;
ALTER TABLE usermanagement.revokedtokens SET SCHEMA resto;
