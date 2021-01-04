
ALTER TABLE docmgr.dm_object ADD COLUMN size bigint DEFAULT 0;

UPDATE docmgr.dm_object SET filesize='0' WHERE filesize IS NULL OR filesize='' OR filesize=' ';

UPDATE docmgr.dm_object SET size=filesize::bigint WHERE filesize::bigint > 0;

ALTER TABLE docmgr.dm_object DROP COLUMN filesize CASCADE;

CREATE TABLE docmgr.object_options (
    object_id integer not null,
    disable_content_index boolean not null default false
);

ALTER TABLE docmgr.keyword_option ADD COLUMN sort_order integer DEFAULT 0;

CREATE   UNIQUE INDEX object_options_pkey ON docmgr.object_options USING btree (object_id);

CREATE VIEW docmgr.dm_view_objects AS
SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, 
dm_object.reindex, dm_object.size, dm_object.object_type, dm_object.token, dm_object.last_modified, dm_object.modified_by, dm_object.hidden, 
dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_object_perm.bitmask, dm_dirlevel.level1, dm_dirlevel.level2
FROM docmgr.dm_object
LEFT JOIN docmgr.dm_object_parent ON dm_object.id = dm_object_parent.object_id
LEFT JOIN docmgr.dm_object_perm ON dm_object.id = dm_object_perm.object_id
LEFT JOIN docmgr.dm_dirlevel ON dm_object.id = dm_dirlevel.object_id;

CREATE VIEW docmgr.dm_view_search AS
SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_owner, dm_object.size, dm_object.last_modified, dm_index.idxfti
FROM docmgr.dm_index
LEFT JOIN docmgr.dm_object ON dm_index.object_id = dm_object.id;

CREATE VIEW docmgr.dm_view_full_search AS
SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, 
dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object.size, dm_object.object_type, dm_object.token, dm_object.last_modified, dm_object.modified_by, 
dm_index.idxfti, dm_dirlevel.level1, dm_dirlevel.level2
FROM docmgr.dm_object
LEFT JOIN docmgr.dm_index ON dm_object.id = dm_index.object_id
LEFT JOIN docmgr.dm_dirlevel ON dm_object.id = dm_dirlevel.object_id;

CREATE   TABLE docmgr.saved_searches (
    id SERIAL NOT NULL,
    name text NOT NULL,
    account_id integer NOT NULL,
    params text NOT NULL
);

CREATE   TABLE docmgr.subscriptions (
    object_id integer NOT NULL,
    account_id integer NOT NULL,
    locked boolean DEFAULT false,
    unlocked boolean DEFAULT false,
    removed boolean DEFAULT false,
    created boolean DEFAULT false,
    comment_posted boolean DEFAULT false,
    notify_email boolean DEFAULT false,
    notify_send_file boolean DEFAULT false
);

ALTER TABLE docmgr.object_link ADD COLUMN share_password TEXT;
ALTER TABLE docmgr.dm_object_log ADD COLUMN ip_address TEXT;

CREATE VIEW docmgr.view_subscriptions AS
    SELECT subscriptions.object_id, subscriptions.account_id, subscriptions.locked, subscriptions.unlocked, subscriptions.removed, subscriptions.created, subscriptions.comment_posted, subscriptions.notify_email, subscriptions.notify_send_file, dm_object.name FROM (docmgr.subscriptions LEFT JOIN docmgr.dm_object ON ((subscriptions.object_id = dm_object.id)));

CREATE   UNIQUE INDEX dm_bookmark_pkey ON docmgr.dm_bookmark USING btree (id);

CREATE   INDEX dm_document_object_id_idx ON docmgr.dm_document USING btree (object_id);

CREATE   UNIQUE INDEX dm_document_pkey ON docmgr.dm_document USING btree (id);

CREATE   INDEX dm_index_queue_object_id_idx ON docmgr.dm_index_queue USING btree (object_id);

CREATE   UNIQUE INDEX dm_index_queue_pkey ON docmgr.dm_index_queue USING btree (id);

CREATE   UNIQUE INDEX dm_properties_pkey ON docmgr.dm_properties USING btree (object_id);

CREATE   INDEX dm_saveroute_date_save_id_idx ON docmgr.dm_saveroute_data USING btree (save_id);

CREATE   UNIQUE INDEX dm_saveroute_pkey ON docmgr.dm_saveroute USING btree (id);

CREATE   INDEX dm_share_object_id_idx ON docmgr.dm_share USING btree (object_id);

CREATE   INDEX keyword_keyword_id_idx ON docmgr.keyword_collection USING btree (keyword_id);

CREATE   INDEX keyword_option_keyword_id_idx ON docmgr.keyword_option USING btree (keyword_id);

CREATE   UNIQUE INDEX keyword_option_pkey ON docmgr.keyword_option USING btree (id);

CREATE   INDEX keyword_parent_id_idx ON docmgr.keyword_collection USING btree (parent_id);

CREATE   UNIQUE INDEX keyword_pkey ON docmgr.keyword USING btree (id);

CREATE   INDEX keyword_value_keyword_id_idx ON docmgr.keyword_value USING btree (keyword_id);

CREATE   INDEX keyword_value_object_id_idx ON docmgr.keyword_value USING btree (object_id);

CREATE   INDEX saved_searches_account_id_idx ON docmgr.saved_searches USING btree (account_id);

CREATE   UNIQUE INDEX saved_searches_pkey ON docmgr.saved_searches USING btree (id);

CREATE   INDEX subscriptions_account_id_idx ON docmgr.subscriptions USING btree (account_id);

CREATE   INDEX subscriptions_object_id_idx ON docmgr.subscriptions USING btree (object_id);

DROP INDEX docmgr.dm_object_search_key;
CREATE INDEX dm_object_name_idx ON docmgr.dm_object USING btree (lower(name));
CREATE INDEX dm_object_summary_idx ON docmgr.dm_object USING btree (lower(summary));
CREATE INDEX keyword_value_keyword_value_idx ON docmgr.keyword_value USING btree (lower(keyword_value));

DROP INDEX docmgr.dm_object_parent_search_key;
CREATE INDEX dm_object_parent_object_id ON docmgr.dm_object_parent USING btree(object_id);
CREATE INDEX dm_object_parent_parent_id ON docmgr.dm_object_parent USING btree(parent_id);

CREATE TABLE auth.cookies (
account_id integer NOT NULL,
key text NOT NULL,
uuid text NOT NULL,
expires integer NOT NULL
);

CREATE UNIQUE INDEX cookies_pkey ON auth.cookies USING btree (account_id);
