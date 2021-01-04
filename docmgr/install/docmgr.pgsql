
SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

CREATE SCHEMA addressbook;
CREATE SCHEMA auth;
CREATE SCHEMA docmgr;
CREATE SCHEMA logger;
CREATE SCHEMA notification;

SET search_path = docmgr, pg_catalog;

CREATE FUNCTION get_all_pathnames(objid integer) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$
DECLARE
	path TEXT;
	res RECORD;
	objname TEXT;
BEGIN

	SELECT INTO objname name FROM docmgr.dm_object WHERE id=objid;
	FOR res IN SELECT parent_id FROM docmgr.dm_object_parent WHERE object_id=objid ORDER BY parent_id LOOP
		IF res.parent_id<>'0' THEN
			SELECT INTO path docmgr.getobjpathname( res.parent_id,'') || '/' || objname;
		ELSE
			SELECT INTO path '/' || objname;
		END IF;
    RETURN NEXT path;
	END LOOP;
END;
$$;


CREATE FUNCTION get_all_paths(objid integer) RETURNS SETOF text
    LANGUAGE plpgsql
    AS $$
DECLARE
	path TEXT;
	res RECORD;
BEGIN
	FOR res IN SELECT parent_id FROM docmgr.dm_object_parent WHERE object_id=objid ORDER BY parent_id LOOP
	IF res.parent_id<>'0' THEN
		SELECT INTO path objid || ',' || docmgr.getobjpath( res.parent_id,'');
	ELSE
		SELECT INTO path objid || ',0';
	END IF;
  RETURN NEXT path;
	END LOOP;
END;
$$;


CREATE FUNCTION getobjfrompath(path text) RETURNS integer
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE arr text[];
DECLARE parent integer;
DECLARE i integer;

BEGIN

     arr := string_to_array(path,'/');
     parent := 0;

     FOR i IN array_lower(arr,1)+1 .. array_upper(arr,1) LOOP

		SELECT INTO parent object_id FROM docmgr.dm_view_objects WHERE parent_id=parent AND name=arr[i];

     END LOOP;

     RETURN parent;

END;
$$;


CREATE FUNCTION getobjpath(objid integer, path text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE result text;
DECLARE tempresult text;
BEGIN
     IF path = '' THEN
         result := objid::text;
     ELSE
         result := path;
     END IF;

     IF objid <> '0' THEN
		SELECT parent_id INTO tempresult FROM docmgr.dm_object_parent WHERE object_id=objid LIMIT 1;
	     result := result || ',' || tempresult::text;
		result := docmgr.getobjpath(tempresult::integer,result);

	END IF;

	RETURN result;

END;
$$;


--
-- Name: getobjpathname(integer, text); Type: FUNCTION; Schema: docmgr; Owner: postgres
--

CREATE FUNCTION getobjpathname(objid integer, path text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE result text;
DECLARE rec record;

BEGIN
     IF path = '' THEN
         result := '';
     ELSE
         result := path;
     END IF;

     IF objid <> '0' THEN
		SELECT name,parent_id INTO rec FROM docmgr.dm_view_objects WHERE id=objid LIMIT 1;

	        IF result = '' THEN
                   result := rec.name;
                ELSE 
                   result := rec.name || '/' || result;
                END IF;

		result := docmgr.getobjpathname(rec.parent_id,result);

     ELSE 
         result := '/' || result;

	END IF;

	RETURN result;

END;
$$;


--
-- Name: path_to_id(text); Type: FUNCTION; Schema: docmgr; Owner: postgres
--

CREATE FUNCTION path_to_id(path text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE arr text[];
DECLARE parent integer;
DECLARE i integer;
DECLARE parentstr text;

BEGIN

     arr := string_to_array(path,'/');
     parent := 0;
    parentstr := 0;

     FOR i IN array_lower(arr,1)+1 .. array_upper(arr,1) LOOP

	    SELECT INTO parent object_id FROM docmgr.dm_view_objects WHERE parent_id=parent AND name=arr[i];

	   SELECT INTO parentstr (parentstr || ',' || parent);
	
     END LOOP;

     RETURN parentstr;

END;
$$;


SET search_path = addressbook, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: contact; Type: TABLE; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE TABLE contact (
    id SERIAL NOT NULL,
    first_name text,
    middle_name text,
    last_name text,
    address text,
    address2 text,
    city text,
    state text,
    zip text,
    country text,
    home_phone numeric,
    home_fax numeric,
    work_phone numeric,
    work_fax numeric,
    mobile numeric,
    pager numeric,
    email text,
    prefix text,
    suffix text,
    letter_salutation text,
    envelope_salutation text,
    website text,
    company_name text,
    last_modified timestamp with time zone DEFAULT now(),
    work_ext text
);


--
-- Name: contact_account; Type: TABLE; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE TABLE contact_account (
    contact_id integer NOT NULL,
    account_id integer NOT NULL,
    account_name text
);

--
-- Name: view_contact; Type: VIEW; Schema: addressbook; Owner: postgres
--

CREATE VIEW view_contact AS
    SELECT contact.id, contact.first_name, contact.middle_name, contact.last_name, contact.address, contact.address2, contact.city, contact.state, contact.zip, contact.country, contact.home_phone, contact.home_fax, contact.work_phone, contact.work_fax, contact.mobile, contact.pager, contact.email, contact.prefix, contact.suffix, contact.letter_salutation, contact.envelope_salutation, contact.website, contact.company_name, contact.last_modified, contact.work_ext, contact_account.account_id, contact_account.account_name FROM (contact LEFT JOIN contact_account ON ((contact.id = contact_account.contact_id)));


SET search_path = auth, pg_catalog;

--
-- Name: account_config; Type: TABLE; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE TABLE auth.cookies (
    account_id integer NOT NULL,
    key text NOT NULL,
    uuid text NOT NULL,
    expires integer NOT NULL
);

CREATE UNIQUE INDEX cookies_pkey ON auth.cookies USING btree (account_id);


CREATE TABLE account_config (
    account_id integer NOT NULL,
    language text,
    home_directory integer,
    editor text,
    email_notifications boolean DEFAULT false
);


--
-- Name: account_groups; Type: TABLE; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE TABLE account_groups (
    account_id integer NOT NULL,
    group_id integer NOT NULL
);


--
-- Name: account_permissions; Type: TABLE; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE TABLE account_permissions (
    account_id integer NOT NULL,
    enable boolean DEFAULT true NOT NULL,
    locked_time timestamp with time zone,
    failed_logins integer DEFAULT 0 NOT NULL,
    failed_logins_locked boolean DEFAULT false NOT NULL,
    last_success_login timestamp with time zone DEFAULT '1970-01-01 00:00:00'::timestamp with time zone NOT NULL,
    setup boolean,
    last_activity timestamp with time zone,
    bitmask bit(32)
);


--
-- Name: accounts; Type: TABLE; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE TABLE accounts (
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

--
-- Name: group_permissions; Type: TABLE; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE TABLE group_permissions (
    group_id integer NOT NULL,
    bitmask bit(32)
);

--
-- Name: groups; Type: TABLE; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE TABLE groups (
    id SERIAL NOT NULL,
    name text NOT NULL
);

SET search_path = docmgr, pg_catalog;

--
-- Name: dm_bookmark; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_bookmark (
    id SERIAL NOT NULL,
    object_id integer NOT NULL,
    account_id integer NOT NULL,
    name text NOT NULL,
    chroot boolean DEFAULT false,
    expandable boolean DEFAULT true,
    protected boolean DEFAULT false,
		default_browse boolean NOT NULL DEFAULT false
);




--
-- Name: dm_dirlevel; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_dirlevel (
    object_id integer,
    level1 smallint,
    level2 smallint
);


--
-- Name: dm_discussion; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_discussion (
    id SERIAL NOT NULL,
    object_id bigint NOT NULL,
    header text,
    account_id bigint NOT NULL,
    content text,
    owner bigint NOT NULL,
    time_stamp timestamp with time zone
);

--
-- Name: dm_document; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_document (
    id SERIAL NOT NULL,
    object_id bigint NOT NULL,
    version bigint DEFAULT 1 NOT NULL,
    modify timestamp with time zone NOT NULL,
    object_owner bigint NOT NULL,
    notes text
);

--
-- Name: dm_file_history; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_file_history (
    id SERIAL NOT NULL,
    object_id bigint NOT NULL,
    version bigint DEFAULT 1 NOT NULL,
    modify timestamp with time zone NOT NULL,
    object_owner bigint NOT NULL,
    notes text,
    md5sum text,
    size numeric DEFAULT 0,
    name text,
    custom_version text
);

--
-- Name: dm_index; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_index (
    object_id integer NOT NULL,
    idxtext text,
    idxfti tsvector
);

--
-- Name: dm_index_queue; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_index_queue (
    id SERIAL NOT NULL,
    object_id integer,
    account_id integer,
    notify_user boolean,
    create_date timestamp with time zone
);

--
-- Name: dm_locks; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_locks (
    object_id integer NOT NULL,
    owner text,
    token text,
    timeout integer,
    created integer,
    scope integer,
    depth integer,
    uri text,
    account_id integer,
    account_name text
);

--
-- Name: dm_locktoken; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_locktoken (
    object_id integer NOT NULL,
    account_id integer NOT NULL,
    token text NOT NULL
);

--
-- Name: dm_object; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_object (
    id SERIAL NOT NULL,
    name text NOT NULL,
    summary text,
    create_date timestamp with time zone,
    object_owner integer,
    status smallint,
    status_date timestamp with time zone,
    status_owner integer,
    version integer DEFAULT 1 NOT NULL,
    reindex smallint DEFAULT 0,
    size bigint,
    object_type text,
    token text,
    last_modified timestamp with time zone,
    modified_by integer,
    hidden boolean DEFAULT false,
    protected boolean DEFAULT false
);

CREATE TABLE docmgr.object_options (
    object_id integer not null,
    disable_content_index boolean not null default false
);

CREATE UNIQUE INDEX object_options_pkey ON docmgr.object_options USING btree (object_id);

--
-- Name: dm_object_log; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_object_log (
    object_id integer,
    account_id integer,
    log_time timestamp with time zone,
    log_type text,
    log_data text,
		ip_address text
);


--
-- Name: dm_object_parent; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_object_parent (
    object_id integer,
    parent_id integer,
    account_id integer,
    share boolean DEFAULT false,
    workflow_id integer DEFAULT 0
);


--
-- Name: dm_object_perm; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_object_perm (
    object_id integer NOT NULL,
    account_id integer,
    group_id integer,
    bitset smallint,
    bitmask bit(8),
    share boolean DEFAULT false,
    workflow_id integer DEFAULT 0
);


--
-- Name: dm_object_related; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_object_related (
    object_id integer NOT NULL,
    related_id integer NOT NULL
);

--
-- Name: dm_properties; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_properties (
    object_id integer NOT NULL,
    data text
);


--
-- Name: dm_saveroute; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_saveroute (
    id SERIAL NOT NULL,
    account_id integer,
    name text
);


--
-- Name: dm_saveroute_data; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_saveroute_data (
    account_id integer,
    task_type text,
    task_notes text,
    date_due integer,
    sort_order smallint,
    save_id integer
);

--
-- Name: dm_share; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_share (
    object_id integer NOT NULL,
    account_id integer NOT NULL,
    share_account_id integer NOT NULL,
    bitmask text
);


--
-- Name: dm_url; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_url (
    object_id integer NOT NULL,
    url text NOT NULL
);

--
-- Name: dm_view_collections; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_collections AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object.hidden, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_object_perm.bitmask FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id))) WHERE (dm_object.object_type = 'collection'::text);


--
-- Name: dm_view_colsearch; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_colsearch AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object.hidden, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_object_perm.bitmask FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id))) WHERE ((dm_object.object_type = 'collection'::text) OR (dm_object.object_type = 'search'::text));


--
-- Name: dm_view_full_search; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_full_search AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object.size, dm_object.object_type, dm_object.token, dm_object.last_modified, dm_object.modified_by, dm_index.idxfti, dm_dirlevel.level1, dm_dirlevel.level2 FROM ((dm_object LEFT JOIN dm_index ON ((dm_object.id = dm_index.object_id))) LEFT JOIN dm_dirlevel ON ((dm_object.id = dm_dirlevel.object_id)));


--
-- Name: dm_view_objects; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_objects AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object.size, dm_object.object_type, dm_object.token, dm_object.last_modified, dm_object.modified_by, dm_object.hidden, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_object_perm.bitmask, dm_dirlevel.level1, dm_dirlevel.level2 FROM (((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id))) LEFT JOIN dm_dirlevel ON ((dm_object.id = dm_dirlevel.object_id)));


--
-- Name: dm_view_parent; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_parent AS
    SELECT dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_parent.account_id, dm_object_parent.share, dm_object_parent.workflow_id, dm_object.name, dm_object.object_type FROM (dm_object_parent LEFT JOIN dm_object ON ((dm_object_parent.object_id = dm_object.id)));


--
-- Name: dm_view_perm; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_perm AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object.reindex, dm_object_perm.object_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_object_perm.bitmask FROM (dm_object LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));


--
-- Name: dm_view_related; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_related AS
    SELECT dm_object_related.object_id, dm_object_related.related_id, dm_object.name, dm_object.object_type FROM (dm_object_related LEFT JOIN dm_object ON ((dm_object_related.related_id = dm_object.id)));


--
-- Name: dm_view_search; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_search AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_owner, dm_object.size, dm_object.last_modified, dm_index.idxfti FROM (dm_index LEFT JOIN dm_object ON ((dm_index.object_id = dm_object.id)));


--
-- Name: dm_view_webdav; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_webdav AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object_parent.object_id, dm_object_parent.parent_id, (SELECT dm_file_history.id FROM dm_file_history WHERE (dm_file_history.object_id = dm_object.id) ORDER BY dm_file_history.version DESC LIMIT 1) AS file_id FROM dm_object, dm_object_parent WHERE ((dm_object.id = dm_object_parent.object_id) AND ((dm_object.object_type = 'collection'::text) OR (dm_object.object_type = 'file'::text)));


--
-- Name: dm_workflow; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_workflow (
    id SERIAL NOT NULL,
    name text NOT NULL,
    absolute_due timestamp with time zone,
    date_complete timestamp with time zone,
    status text,
    account_id integer,
    date_create timestamp with time zone,
    email_notify boolean,
    expire_notify boolean
);


--
-- Name: dm_workflow_route; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_workflow_route (
    id SERIAL NOT NULL,
    workflow_id integer NOT NULL,
    account_id integer NOT NULL,
    task_type text,
    date_due timestamp with time zone,
    date_complete timestamp with time zone,
    status text,
    sort_order smallint,
    comment text,
    task_notes text
);


--
-- Name: dm_view_workflow; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_workflow AS
    SELECT dm_workflow_route.id, dm_workflow_route.workflow_id, dm_workflow_route.account_id, dm_workflow_route.task_type, dm_workflow_route.date_due AS relative_due, dm_workflow_route.date_complete, dm_workflow_route.status, dm_workflow_route.sort_order, dm_workflow_route.comment FROM (dm_workflow_route LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id)));


--
-- Name: dm_workflow_route_object; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_workflow_route_object (
    route_id integer NOT NULL,
    object_id integer NOT NULL,
    completed boolean DEFAULT false
);


--
-- Name: dm_view_workflow_route; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW dm_view_workflow_route AS
    SELECT dm_workflow_route.id, dm_workflow_route.workflow_id, dm_workflow_route.account_id, dm_workflow_route.task_type, dm_workflow_route.date_due, dm_workflow_route.date_complete, dm_workflow_route.status, dm_workflow_route.sort_order, dm_workflow_route.comment, dm_workflow_route.task_notes, dm_workflow_route_object.route_id, dm_workflow_route_object.object_id, dm_workflow_route_object.completed FROM (dm_workflow_route LEFT JOIN dm_workflow_route_object ON ((dm_workflow_route.id = dm_workflow_route_object.route_id)));


--
-- Name: dm_workflow_object; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE dm_workflow_object (
    workflow_id integer NOT NULL,
    object_id integer NOT NULL
);

--
-- Name: keyword; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE keyword (
    id SERIAL NOT NULL,
    name text NOT NULL,
    type text NOT NULL,
    required boolean DEFAULT false NOT NULL
);

--
-- Name: keyword_collection; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE keyword_collection (
    keyword_id integer NOT NULL,
    parent_id integer NOT NULL
);

--
-- Name: keyword_option; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE keyword_option (
    id SERIAL NOT NULL,
    name text NOT NULL,
    keyword_id integer NOT NULL,
    sort_order integer default 0
);

--
-- Name: keyword_value; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE keyword_value (
    object_id integer NOT NULL,
    keyword_id integer NOT NULL,
    keyword_value text,
    data_type text
);


--
-- Name: level1; Type: SEQUENCE; Schema: docmgr; Owner: postgres
--

CREATE SEQUENCE level1
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 16
    NO MINVALUE
    CACHE 1
    CYCLE;

--
-- Name: level2; Type: SEQUENCE; Schema: docmgr; Owner: postgres
--

CREATE SEQUENCE level2
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 256
    NO MINVALUE
    CACHE 1
    CYCLE;

--
-- Name: object_link; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE object_link (
    object_id integer NOT NULL,
    link text NOT NULL,
    account_id integer NOT NULL,
		share_password TEXT,
    created timestamp with time zone,
    expires timestamp with time zone
);

CREATE TABLE object_convert_keys (
    object_id integer NOT NULL,
    convert_key text NOT NULL,
   	date_created timestamp without time zone NOT NULL DEFAULT NOW()
    );    

CREATE UNIQUE INDEX object_convert_keys_pkey ON object_convert_keys USING btree(object_id,convert_key);

--
-- Name: object_view; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE object_view (
    object_id integer NOT NULL,
    account_id integer NOT NULL,
    view text DEFAULT 'list'::text
);


--
-- Name: saved_searches; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE saved_searches (
    id SERIAL NOT NULL,
    name text NOT NULL,
    account_id integer NOT NULL,
    params text NOT NULL
);

--
-- Name: subscriptions; Type: TABLE; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE TABLE subscriptions (
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


--
-- Name: view_keyword_collection; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW view_keyword_collection AS
    SELECT keyword.id, keyword.name, keyword.type, keyword.required, keyword_collection.parent_id FROM (keyword LEFT JOIN keyword_collection ON ((keyword.id = keyword_collection.keyword_id)));


--
-- Name: view_subscriptions; Type: VIEW; Schema: docmgr; Owner: postgres
--

CREATE VIEW view_subscriptions AS
    SELECT subscriptions.object_id, subscriptions.account_id, subscriptions.locked, subscriptions.unlocked, subscriptions.removed, subscriptions.created, subscriptions.comment_posted, subscriptions.notify_email, subscriptions.notify_send_file, dm_object.name FROM (subscriptions LEFT JOIN dm_object ON ((subscriptions.object_id = dm_object.id)));


SET search_path = logger, pg_catalog;

--
-- Name: logs; Type: TABLE; Schema: logger; Owner: postgres; Tablespace: 
--

CREATE TABLE logs (
    id SERIAL NOT NULL,
    message text,
    level smallint,
    category text,
    log_timestamp timestamp with time zone,
    ip_address text,
    user_id integer,
    user_login text,
    data text
);

SET search_path = notification, pg_catalog;

--
-- Name: notifications; Type: TABLE; Schema: notification; Owner: postgres; Tablespace: 
--

CREATE TABLE notifications (
    id SERIAL NOT NULL,
    record_id integer NOT NULL,
    record_name text,
    option_id integer NOT NULL,
    account_id integer NOT NULL,
    date_created timestamp with time zone DEFAULT now(),
    link text,
    message text,
    attach text
);

-- Name: options; Type: TABLE; Schema: notification; Owner: postgres; Tablespace: 
--

CREATE TABLE options (
    id SERIAL NOT NULL,
    name text NOT NULL,
    define_name text NOT NULL,
    subscription_field text,
    record_type text
);

--
-- Name: view_notifications; Type: VIEW; Schema: notification; Owner: postgres
--

CREATE VIEW view_notifications AS
    SELECT notifications.id, notifications.record_id, notifications.record_name, notifications.option_id, notifications.account_id, notifications.date_created, notifications.link, notifications.message, notifications.attach, options.name, options.define_name, options.record_type FROM (notifications LEFT JOIN options ON ((notifications.option_id = options.id)));


SET search_path = public, pg_catalog;

--
-- Name: db_version; Type: TABLE; Schema: public; Owner: postgres; Tablespace: 
--

CREATE TABLE db_version (
    version integer NOT NULL
);

--
-- INSERT DATA
--

SET search_path = auth, pg_catalog;

--
-- Data for Name: account_config; Type: TABLE DATA; Schema: auth; Owner: postgres
--

INSERT INTO accounts (login, password, digest_hash, first_name, last_name, email, home_phone, work_phone, fax, mobile) 
											VALUES 
											('admin', '21232f297a57a5a743894a0e4a801fc3', '87fd274b7b6c01e48d7c2f965da8ddf7', 'Administrator', 'User', 'admin@nowhere.com', NULL, NULL, NULL, NULL);

INSERT INTO account_config (account_id, language, home_directory, editor, email_notifications) VALUES (1, 'en', NULL, NULL, false);
INSERT INTO account_groups (account_id, group_id) VALUES (1, 1);
INSERT INTO account_permissions (account_id, enable, locked_time, failed_logins, failed_logins_locked, last_success_login, setup, last_activity, bitmask) 
																VALUES 
																(1, true, NULL, 0, false, NOW(), false, NOW(), B'00000000000000000000000000000001');

INSERT INTO groups (name) VALUES ('Administrators');
INSERT INTO groups (name) VALUES ('Users');
INSERT INTO groups (name) VALUES ('Guests');

INSERT INTO group_permissions (group_id, bitmask) VALUES (1, B'00000000000000000000000000000001');


SET search_path = notification, pg_catalog;

INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object locked for editing', 'OBJ_LOCK_NOTIFICATION', 'locked', 'docmgr');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object unlocked', 'OBJ_UNLOCK_NOTIFICATION', 'unlocked', 'docmgr');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object removed', 'OBJ_REMOVE_NOTIFICATION', 'removed', 'docmgr');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object created', 'OBJ_CREATE_NOTIFICATION', 'created', 'docmgr');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Comment posted', 'OBJ_COMMENT_POST_NOTIFICATION', 'comment_posted', 'docmgr');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object shared with you', 'OBJ_SHARE_NOTIFICATION', '', 'docmgr');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object waiting for you to view', 'WORKFLOW_VIEW_NOTIFICATION', '', 'workflow');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object waiting for you to edit', 'WORKFLOW_EDIT_NOTIFICATION', '', 'workflow');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object waiting for comment', 'WORKFLOW_COMMENT_NOTIFICATION', '', 'workflow');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Object awaiting approval', 'WORKFLOW_APPROVE_NOTIFICATION', '', 'workflow');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Workflow completed', 'WORKFLOW_COMPLETED_NOTIFICATION', '', 'workflow');
INSERT INTO options (name, define_name, subscription_field, record_type) VALUES ('Workflow rejected', 'WORKFLOW_REJECTED_NOTIFICATION', '', 'workflow');


SET search_path = public, pg_catalog;

--
-- Data for Name: db_version; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO db_version (version) VALUES (2013050101);

SET search_path = docmgr, pg_catalog;

SET search_path = addressbook, pg_catalog;

--
-- Name: contact_account_account_id_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_account_account_id_idx ON contact_account USING btree (account_id);

--
-- Name: contact_account_contact_id_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_account_contact_id_idx ON contact_account USING btree (contact_id);


--
-- Name: contact_address_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_address_idx ON contact USING btree (lower(address));


--
-- Name: contact_city_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_city_idx ON contact USING btree (lower(city));


--
-- Name: contact_email_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_email_idx ON contact USING btree (lower(email));


--
-- Name: contact_first_name_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_first_name_idx ON contact USING btree (lower(first_name));


--
-- Name: contact_id_pkey; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX contact_id_pkey ON contact USING btree (id);

--
-- Name: contact_last_name_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_last_name_idx ON contact USING btree (lower(last_name));


--
-- Name: contact_middle_name_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_middle_name_idx ON contact USING btree (lower(middle_name));


--
-- Name: contact_state_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_state_idx ON contact USING btree (state);


--
-- Name: contact_zip_idx; Type: INDEX; Schema: addressbook; Owner: postgres; Tablespace: 
--

CREATE INDEX contact_zip_idx ON contact USING btree (zip);


SET search_path = auth, pg_catalog;

--
-- Name: account_config_pkey; Type: INDEX; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX account_config_pkey ON account_config USING btree (account_id);


--
-- Name: account_groups_account_id_idx; Type: INDEX; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE INDEX account_groups_account_id_idx ON account_groups USING btree (account_id);


--
-- Name: account_permissions_pkey; Type: INDEX; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX account_permissions_pkey ON account_permissions USING btree (account_id);


--
-- Name: accounts_pkey; Type: INDEX; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX accounts_pkey ON accounts USING btree (id);


--
-- Name: group_permissions_pkey; Type: INDEX; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX group_permissions_pkey ON group_permissions USING btree (group_id);


--
-- Name: groups_pkey; Type: INDEX; Schema: auth; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX groups_pkey ON groups USING btree (id);


SET search_path = docmgr, pg_catalog;

--
-- Name: dm_bookmark_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_bookmark_pkey ON dm_bookmark USING btree (id);


--
-- Name: dm_dirlevel_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_dirlevel_pkey ON dm_dirlevel USING btree (object_id);


--
-- Name: dm_discussion_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_discussion_id_key ON dm_discussion USING btree (id);


--
-- Name: dm_discussion_object_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_discussion_object_id_key ON dm_discussion USING btree (object_id);


--
-- Name: dm_document_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_document_object_id_idx ON dm_document USING btree (object_id);


--
-- Name: dm_document_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_document_pkey ON dm_document USING btree (id);


--
-- Name: dm_file_history_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_file_history_id_key ON dm_file_history USING btree (id);


--
-- Name: dm_file_history_object_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_file_history_object_id_key ON dm_file_history USING btree (object_id);


--
-- Name: dm_index_queue_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_index_queue_object_id_idx ON dm_index_queue USING btree (object_id);


--
-- Name: dm_index_queue_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_index_queue_pkey ON dm_index_queue USING btree (id);


--
-- Name: dm_locks_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_locks_object_id_idx ON dm_locks USING btree (object_id);


--
-- Name: dm_locktoken_account_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_locktoken_account_id_idx ON dm_locktoken USING btree (account_id);


--
-- Name: dm_locktoken_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_locktoken_object_id_idx ON dm_locktoken USING btree (object_id);


--
-- Name: dm_object_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_object_id_key ON dm_object USING btree (id);


--
-- Name: dm_object_log_object_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_object_log_object_id_key ON dm_object_log USING btree (object_id);


--
-- Name: dm_object_object_type_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_object_object_type_idx ON dm_object USING btree (object_type);


--
-- Name: dm_object_parent_search_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_object_parent_search_key ON dm_object_parent USING btree (object_id, parent_id);


--
-- Name: dm_object_perm_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_object_perm_id_key ON dm_object_perm USING btree (object_id);


--
-- Name: dm_object_related_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_object_related_object_id_idx ON dm_object_related USING btree (object_id);


--
-- Name: dm_object_related_related_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_object_related_related_id_idx ON dm_object_related USING btree (related_id);


CREATE INDEX dm_object_name_idx ON docmgr.dm_object USING btree (lower(name));
CREATE INDEX dm_object_summary_idx ON docmgr.dm_object USING btree (lower(summary));
CREATE INDEX keyword_value_keyword_value_idx ON docmgr.keyword_value USING btree (lower(keyword_value));
CREATE INDEX dm_object_parent_object_id ON docmgr.dm_object_parent USING btree(object_id);
CREATE INDEX dm_object_parent_parent_id ON docmgr.dm_object_parent USING btree(parent_id);

CREATE UNIQUE INDEX dm_properties_pkey ON dm_properties USING btree (object_id);


--
-- Name: dm_saveroute_date_save_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_saveroute_date_save_id_idx ON dm_saveroute_data USING btree (save_id);


--
-- Name: dm_saveroute_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_saveroute_pkey ON dm_saveroute USING btree (id);


--
-- Name: dm_share_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_share_object_id_idx ON dm_share USING btree (object_id);


--
-- Name: dm_url_object_id; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_url_object_id ON dm_url USING btree (object_id);


--
-- Name: dm_workflow_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_workflow_id_key ON dm_workflow USING btree (id);


--
-- Name: dm_workflow_object_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_workflow_object_object_id_idx ON dm_workflow_object USING btree (object_id);


--
-- Name: dm_workflow_object_workflow_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_workflow_object_workflow_id_idx ON dm_workflow_object USING btree (workflow_id);


--
-- Name: dm_workflow_route_id_key; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX dm_workflow_route_id_key ON dm_workflow_route USING btree (id);


--
-- Name: dm_workflow_route_object_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_workflow_route_object_object_id_idx ON dm_workflow_route_object USING btree (object_id);


--
-- Name: dm_workflow_route_object_route_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX dm_workflow_route_object_route_id_idx ON dm_workflow_route_object USING btree (route_id);


--
-- Name: idxfti_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX idxfti_idx ON dm_index USING gin (idxfti);


--
-- Name: keyword_keyword_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX keyword_keyword_id_idx ON keyword_collection USING btree (keyword_id);


--
-- Name: keyword_option_keyword_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX keyword_option_keyword_id_idx ON keyword_option USING btree (keyword_id);


--
-- Name: keyword_option_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX keyword_option_pkey ON keyword_option USING btree (id);


--
-- Name: keyword_parent_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX keyword_parent_id_idx ON keyword_collection USING btree (parent_id);


--
-- Name: keyword_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX keyword_pkey ON keyword USING btree (id);


--
-- Name: keyword_value_keyword_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX keyword_value_keyword_id_idx ON keyword_value USING btree (keyword_id);


--
-- Name: keyword_value_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX keyword_value_object_id_idx ON keyword_value USING btree (object_id);


--
-- Name: object_link_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX object_link_idx ON object_link USING btree (link);


--
-- Name: object_view_object_account_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX object_view_object_account_id_idx ON object_view USING btree (account_id);


--
-- Name: object_view_object_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX object_view_object_object_id_idx ON object_view USING btree (object_id);


--
-- Name: saved_searches_account_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX saved_searches_account_id_idx ON saved_searches USING btree (account_id);


--
-- Name: saved_searches_pkey; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX saved_searches_pkey ON saved_searches USING btree (id);


--
-- Name: subscriptions_account_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX subscriptions_account_id_idx ON subscriptions USING btree (account_id);


--
-- Name: subscriptions_object_id_idx; Type: INDEX; Schema: docmgr; Owner: postgres; Tablespace: 
--

CREATE INDEX subscriptions_object_id_idx ON subscriptions USING btree (object_id);


SET search_path = logger, pg_catalog;

--
-- Name: log_level_idx; Type: INDEX; Schema: logger; Owner: postgres; Tablespace: 
--

CREATE INDEX log_level_idx ON logs USING btree (level);


--
-- Name: logs_category_idx; Type: INDEX; Schema: logger; Owner: postgres; Tablespace: 
--

CREATE INDEX logs_category_idx ON logs USING btree (category);


--
-- Name: logs_pkey; Type: INDEX; Schema: logger; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX logs_pkey ON logs USING btree (id);


SET search_path = notification, pg_catalog;

--
-- Name: notifications_pkey; Type: INDEX; Schema: notification; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX notifications_pkey ON notifications USING btree (id);


--
-- Name: options_pkey; Type: INDEX; Schema: notification; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX options_pkey ON options USING btree (id);


SET search_path = docmgr, pg_catalog;

--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_discussion
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_file_history
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_index
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_object_parent
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_object_perm
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_url
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: docmgr; Owner: postgres
--

ALTER TABLE ONLY dm_workflow_route
    ADD CONSTRAINT "$1" FOREIGN KEY (workflow_id) REFERENCES dm_workflow(id);


CREATE INDEX accounts_login_idx ON auth.accounts USING btree(login);
CREATE INDEX acccounts_first_name_idx ON auth.accounts USING  btree(lower(first_name));
CREATE INDEX acccounts_last_name_idx ON auth.accounts USING  btree(lower(last_name));
CREATE INDEX acccounts_email_idx ON auth.accounts USING  btree(lower(email));

--
-- PostgreSQL database dump complete
--

