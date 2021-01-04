
ALTER SCHEMA auth RENAME TO auth_bak;

CREATE SCHEMA auth;

CREATE TABLE auth.account_config (
    account_id integer NOT NULL,
    language text,
    home_directory integer,
    editor text,
    email_notifications boolean DEFAULT false
);

CREATE TABLE auth.account_groups (
    account_id integer NOT NULL,
    group_id integer NOT NULL
);

CREATE TABLE auth.account_permissions (
    account_id integer NOT NULL,
    enable boolean DEFAULT true NOT NULL,
    locked_time timestamp without time zone,
    failed_logins integer DEFAULT 0 NOT NULL,
    failed_logins_locked boolean DEFAULT false NOT NULL,
    last_success_login timestamp without time zone DEFAULT '1970-01-01 00:00:00'::timestamp without time zone NOT NULL,
    setup boolean,
    last_activity timestamp without time zone,
    bitmask bit(32)
);

CREATE TABLE auth.accounts (
    id SERIAL NOT NULL,
    login text NOT NULL,
    password text NOT NULL,
    digest_hash text,
    first_name text,
    last_name text,
    email text,
    home_phone text,
    work_phone text,
    fax text,
    mobile text
);

CREATE TABLE auth.group_permissions (
    group_id integer NOT NULL,
    bitmask bit(32)
);

CREATE TABLE auth.groups (
    id SERIAL NOT NULL,
    name text NOT NULL
);

CREATE UNIQUE INDEX account_config_pkey ON auth.account_config USING btree (account_id);
CREATE INDEX account_groups_account_id_idx ON auth.account_groups USING btree (account_id);
CREATE UNIQUE INDEX account_permissions_pkey ON auth.account_permissions USING btree (account_id);
CREATE UNIQUE INDEX accounts_pkey ON auth.accounts USING btree (id);
CREATE UNIQUE INDEX group_permissions_pkey ON auth.group_permissions USING btree (group_id);
CREATE UNIQUE INDEX groups_pkey ON auth.groups USING btree (id);


CREATE INDEX accounts_login_idx ON auth.accounts USING btree(login);
CREATE INDEX acccounts_first_name_idx ON auth.accounts USING  btree(lower(first_name));
CREATE INDEX acccounts_last_name_idx ON auth.accounts USING  btree(lower(last_name));
CREATE INDEX acccounts_email_idx ON auth.accounts USING  btree(lower(email));
