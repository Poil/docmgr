<?


//create our 50 database

$sql = "

--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET SESSION AUTHORIZATION 'postgres';

--
-- TOC entry 4 (OID 2200)
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


SET SESSION AUTHORIZATION 'postgres';

SET search_path = public, pg_catalog;

--
-- TOC entry 5 (OID 3395838)
-- Name: auth_accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_accounts_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 7 (OID 3395840)
-- Name: auth_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_groups_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 9 (OID 3395842)
-- Name: dm_discussion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_discussion_id_seq
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 11 (OID 3395844)
-- Name: dm_file_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_file_history_id_seq
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 13 (OID 3395846)
-- Name: dm_object_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_object_id_seq
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 17 (OID 3395848)
-- Name: auth_accounts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_accounts (
    id integer DEFAULT nextval('\"auth_accounts_id_seq\"'::text) NOT NULL,
    login text NOT NULL,
    \"password\" text NOT NULL,
    first_name text,
    last_name text,
    email text,
    phone text
);


--
-- TOC entry 18 (OID 3395855)
-- Name: auth_grouplink; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_grouplink (
    accountid integer NOT NULL,
    groupid integer NOT NULL
);


--
-- TOC entry 19 (OID 3395857)
-- Name: auth_groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_groups (
    id integer DEFAULT nextval('\"auth_groups_id_seq\"'::text) NOT NULL,
    name text NOT NULL
);


--
-- TOC entry 20 (OID 3395863)
-- Name: dm_discussion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_discussion (
    id integer DEFAULT nextval('\"dm_discussion_id_seq\"'::text) NOT NULL,
    object_id bigint NOT NULL,
    header text,
    account_id bigint NOT NULL,
    content text,
    \"owner\" bigint NOT NULL,
    time_stamp timestamp without time zone
);


--
-- TOC entry 21 (OID 3395869)
-- Name: dm_file_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_file_history (
    id integer DEFAULT nextval('\"dm_file_history_id_seq\"'::text) NOT NULL,
    object_id bigint NOT NULL,
    size text NOT NULL,
    \"version\" bigint DEFAULT 1 NOT NULL,
    modify timestamp without time zone NOT NULL,
    object_owner bigint NOT NULL,
    notes text
);


--
-- TOC entry 22 (OID 3395876)
-- Name: dm_index; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_index (
    object_id integer,
    idxtext text
);


--
-- TOC entry 23 (OID 3395881)
-- Name: auth_accountperm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_accountperm (
    account_id integer NOT NULL,
    bitset integer DEFAULT 0 NOT NULL,
    enable boolean DEFAULT true NOT NULL
);


--
-- TOC entry 24 (OID 3395885)
-- Name: auth_groupperm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_groupperm (
    group_id integer NOT NULL,
    bitset integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 15 (OID 3395888)
-- Name: dm_object_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_object_type_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 25 (OID 3395890)
-- Name: dm_object; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object (
    id integer DEFAULT nextval('\"dm_object_id_seq\"'::text) NOT NULL,
    name text NOT NULL,
    summary text,
    object_type integer,
    create_date timestamp without time zone,
    object_owner integer,
    status smallint NOT NULL,
    status_date timestamp without time zone,
    status_owner integer,
    \"version\" integer DEFAULT 1 NOT NULL,
    \"reindex\" smallint DEFAULT 0,
    filesize text
);


--
-- TOC entry 26 (OID 3395898)
-- Name: dm_object_perm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_perm (
    object_id integer NOT NULL,
    account_id integer,
    group_id integer,
    bitset smallint
);


--
-- TOC entry 27 (OID 3395900)
-- Name: dm_object_type; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_type (
    id integer DEFAULT nextval('\"dm_object_type_id_seq\"'::text) NOT NULL,
    name text NOT NULL
);


--
-- TOC entry 28 (OID 3395906)
-- Name: dm_object_parent; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_parent (
    object_id integer,
    parent_id integer
);


--
-- TOC entry 29 (OID 3395910)
-- Name: dm_view_perm; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_perm AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object_perm.object_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM dm_object, dm_object_perm WHERE (dm_object.id = dm_object_perm.object_id);


--
-- TOC entry 30 (OID 3395913)
-- Name: dm_view_collections; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_collections AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object_parent.object_id, dm_object_parent.parent_id FROM dm_object, dm_object_parent WHERE ((dm_object.id = dm_object_parent.object_id) AND (dm_object.object_type = 1));


--
-- TOC entry 31 (OID 3395916)
-- Name: dm_view_search; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_search AS
    SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_index.idxtext, dm_object_parent.parent_id FROM ((dm_object LEFT JOIN dm_index ON ((dm_object.id = dm_index.object_id))) LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id)));


--
-- TOC entry 32 (OID 3395919)
-- Name: dm_view_search_perm; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_search_perm AS
    SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_index.idxtext, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM (((dm_object LEFT JOIN dm_index ON ((dm_object.id = dm_index.object_id))) LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));


--
-- TOC entry 33 (OID 3395923)
-- Name: dm_view_objects; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_objects AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object.filesize, dm_object_parent.object_id, dm_object_parent.parent_id FROM dm_object, dm_object_parent WHERE (dm_object.id = dm_object_parent.object_id);


--
-- TOC entry 34 (OID 3395926)
-- Name: dm_view_collections_perm; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_collections_perm AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id))) WHERE (dm_object.object_type = 1);


--
-- TOC entry 35 (OID 3395929)
-- Name: dm_view_objects_perm; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_objects_perm AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_object.filesize,  dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));


--
-- TOC entry 36 (OID 3395930)
-- Name: dm_object_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_log (
    object_id integer,
    account_id integer,
    log_time timestamp without time zone,
    log_type text
);


--
-- TOC entry 37 (OID 3395935)
-- Name: dm_bookmark; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_bookmark (
    object_id integer,
    account_id integer,
    name text
);


--
-- TOC entry 38 (OID 3395939)
-- Name: dm_view_bookmarks; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_bookmarks AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_bookmark.object_id, dm_bookmark.account_id FROM dm_object, dm_bookmark WHERE (dm_object.id = dm_bookmark.object_id);


--
-- TOC entry 39 (OID 3395942)
-- Name: dm_workflow; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_workflow (
    id serial NOT NULL,
    object_id integer NOT NULL,
    absolute_due timestamp without time zone,
    date_complete timestamp without time zone,
    status text,
    account_id integer,
    date_create timestamp without time zone
);


--
-- TOC entry 40 (OID 3395950)
-- Name: dm_workflow_route; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_workflow_route (
    id serial NOT NULL,
    workflow_id integer NOT NULL,
    account_id integer NOT NULL,
    task_type text,
    date_due timestamp without time zone,
    date_complete timestamp without time zone,
    status text,
    sort_order smallint,
    \"comment\" text
);


--
-- TOC entry 41 (OID 3395956)
-- Name: dm_task; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_task (
    account_id integer,
    task_id integer,
    alert_type text,
    date_due timestamp without time zone
);


--
-- TOC entry 42 (OID 3395961)
-- Name: dm_subscribe; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_subscribe (
    object_id integer,
    account_id integer,
    send_email boolean,
    event_type text
);


--
-- TOC entry 43 (OID 3395968)
-- Name: dm_alert; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_alert (
    id serial NOT NULL,
    object_id integer,
    account_id integer,
    alert_type text
);


--
-- TOC entry 44 (OID 3395976)
-- Name: dm_view_alert; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_alert AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object.\"reindex\", dm_alert.id AS alert_id, dm_alert.object_id, dm_alert.account_id, dm_alert.alert_type FROM dm_object, dm_alert WHERE (dm_object.id = dm_alert.object_id);


--
-- TOC entry 45 (OID 3395977)
-- Name: dm_keyword; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_keyword (
    object_id integer NOT NULL,
    field1 text,
    field2 text,
    field3 text,
    field4 text,
    field5 text,
    field6 text
);


--
-- TOC entry 46 (OID 3395984)
-- Name: dm_view_keyword; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_keyword AS
    SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_keyword.object_id, dm_keyword.field1, dm_keyword.field2, dm_keyword.field3, dm_keyword.field4, dm_keyword.field5, dm_keyword.field6, dm_object_parent.parent_id FROM ((dm_object LEFT JOIN dm_keyword ON ((dm_object.id = dm_keyword.object_id))) LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id)));


--
-- TOC entry 47 (OID 3395987)
-- Name: dm_view_keyword_perm; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_keyword_perm AS
    SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_keyword.object_id, dm_keyword.field1, dm_keyword.field2, dm_keyword.field3, dm_keyword.field4, dm_keyword.field5, dm_keyword.field6, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM (((dm_object LEFT JOIN dm_keyword ON ((dm_object.id = dm_keyword.object_id))) LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));


--
-- TOC entry 48 (OID 3395989)
-- Name: dm_url; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_url (
    object_id integer NOT NULL,
    url text NOT NULL
) WITHOUT OIDS;


--
-- TOC entry 49 (OID 3395996)
-- Name: dm_view_workflow; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_workflow AS
    SELECT dm_workflow_route.id, dm_workflow_route.workflow_id, dm_workflow_route.account_id, dm_workflow_route.task_type, dm_workflow_route.date_due AS relative_due, dm_workflow_route.date_complete, dm_workflow_route.status, dm_workflow_route.sort_order, dm_workflow_route.\"comment\", dm_workflow.object_id FROM (dm_workflow_route LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id)));


--
-- TOC entry 50 (OID 3395999)
-- Name: dm_task_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_task_view AS
    SELECT dm_task.account_id, dm_task.task_id, dm_task.alert_type, dm_workflow.object_id, dm_workflow_route.id AS route_id, dm_workflow_route.date_due, dm_object.name FROM (((dm_task LEFT JOIN dm_workflow_route ON ((dm_task.task_id = dm_workflow_route.id))) LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id))) LEFT JOIN dm_object ON ((dm_workflow.object_id = dm_object.id)));


--
-- TOC entry 51 (OID 3396003)
-- Name: dm_view_webdav; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_webdav AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.\"version\", dm_object_parent.object_id, dm_object_parent.parent_id, (SELECT dm_file_history.id FROM dm_file_history WHERE (dm_file_history.object_id = dm_object.id) ORDER BY dm_file_history.\"version\" DESC LIMIT 1) AS file_id FROM dm_object, dm_object_parent WHERE ((dm_object.id = dm_object_parent.object_id) AND ((dm_object.object_type = 1) OR (dm_object.object_type = 2)));


--
-- Data for TOC entry 77 (OID 3395848)
-- Name: auth_accounts; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_accounts VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', 'User', 'admin@nowhere.com', NULL);


--
-- Data for TOC entry 78 (OID 3395855)
-- Name: auth_grouplink; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_grouplink VALUES (1, 1);


--
-- Data for TOC entry 79 (OID 3395857)
-- Name: auth_groups; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_groups VALUES (3, 'Guests');
INSERT INTO auth_groups VALUES (1, 'Administrators');
INSERT INTO auth_groups VALUES (2, 'Users');


--
-- Data for TOC entry 80 (OID 3395863)
-- Name: dm_discussion; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 81 (OID 3395869)
-- Name: dm_file_history; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 82 (OID 3395876)
-- Name: dm_index; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 83 (OID 3395881)
-- Name: auth_accountperm; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_accountperm VALUES (1, 1, true);


--
-- Data for TOC entry 84 (OID 3395885)
-- Name: auth_groupperm; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_groupperm VALUES (1, 1);


--
-- Data for TOC entry 85 (OID 3395890)
-- Name: dm_object; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 86 (OID 3395898)
-- Name: dm_object_perm; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 87 (OID 3395900)
-- Name: dm_object_type; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO dm_object_type VALUES (1, 'Collection');
INSERT INTO dm_object_type VALUES (2, 'File');
INSERT INTO dm_object_type VALUES (3, 'URL');


--
-- Data for TOC entry 88 (OID 3395906)
-- Name: dm_object_parent; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 89 (OID 3395930)
-- Name: dm_object_log; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 90 (OID 3395935)
-- Name: dm_bookmark; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 91 (OID 3395942)
-- Name: dm_workflow; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 92 (OID 3395950)
-- Name: dm_workflow_route; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 93 (OID 3395956)
-- Name: dm_task; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 94 (OID 3395961)
-- Name: dm_subscribe; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 95 (OID 3395968)
-- Name: dm_alert; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 96 (OID 3395977)
-- Name: dm_keyword; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 97 (OID 3395989)
-- Name: dm_url; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- TOC entry 75 (OID 3396017)
-- Name: dm_keyword_field_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_keyword_field_idx ON dm_keyword USING btree (field1, field2, field3, field4, field5, field6);


--
-- TOC entry 57 (OID 3396026)
-- Name: auth_groups_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX auth_groups_id_key ON auth_groups USING btree (id);


--
-- TOC entry 55 (OID 3396027)
-- Name: auth_accounts_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX auth_accounts_id_key ON auth_accounts USING btree (id);


--
-- TOC entry 59 (OID 3396028)
-- Name: dm_discussion_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_discussion_id_key ON dm_discussion USING btree (id);


--
-- TOC entry 60 (OID 3396029)
-- Name: dm_discussion_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_discussion_object_id_key ON dm_discussion USING btree (object_id);


--
-- TOC entry 62 (OID 3396030)
-- Name: dm_file_history_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_file_history_id_key ON dm_file_history USING btree (id);


--
-- TOC entry 63 (OID 3396031)
-- Name: dm_file_history_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_file_history_object_id_key ON dm_file_history USING btree (object_id);


--
-- TOC entry 65 (OID 3396032)
-- Name: dm_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_object_id_key ON dm_object USING btree (id);


--
-- TOC entry 69 (OID 3396034)
-- Name: dm_object_log_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_log_object_id_key ON dm_object_log USING btree (object_id);


--
-- TOC entry 66 (OID 3396039)
-- Name: dm_object_search_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_search_key ON dm_object USING btree (name, summary);


--
-- TOC entry 68 (OID 3396040)
-- Name: dm_object_parent_search_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_parent_search_key ON dm_object_parent USING btree (object_id, parent_id);


--
-- TOC entry 67 (OID 3396041)
-- Name: dm_object_perm_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_perm_id_key ON dm_object_perm USING btree (object_id);


--
-- TOC entry 74 (OID 3396042)
-- Name: dm_subscribe_info_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_subscribe_info_key ON dm_subscribe USING btree (object_id, account_id);


--
-- TOC entry 73 (OID 3396043)
-- Name: dm_task_account_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_task_account_id_key ON dm_task USING btree (account_id);


--
-- TOC entry 76 (OID 3396044)
-- Name: dm_url_object_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_url_object_id ON dm_url USING btree (object_id);


--
-- TOC entry 70 (OID 3396045)
-- Name: dm_workflow_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_workflow_id_key ON dm_workflow USING btree (id);


--
-- TOC entry 71 (OID 3396046)
-- Name: dm_workflow_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_workflow_object_id_key ON dm_workflow USING btree (object_id);


--
-- TOC entry 72 (OID 3396047)
-- Name: dm_workflow_route_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_workflow_route_id_key ON dm_workflow_route USING btree (id);


--
-- TOC entry 56 (OID 3396018)
-- Name: auth_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_accounts
    ADD CONSTRAINT auth_accounts_pkey PRIMARY KEY (id);


--
-- TOC entry 58 (OID 3396020)
-- Name: auth_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_groups
    ADD CONSTRAINT auth_groups_pkey PRIMARY KEY (id);


--
-- TOC entry 61 (OID 3396022)
-- Name: dm_discussion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_discussion
    ADD CONSTRAINT dm_discussion_pkey PRIMARY KEY (id);


--
-- TOC entry 64 (OID 3396024)
-- Name: dm_file_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_file_history
    ADD CONSTRAINT dm_file_history_pkey PRIMARY KEY (id);


--
-- TOC entry 6 (OID 3395838)
-- Name: auth_accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_accounts_id_seq', 1, true);


--
-- TOC entry 8 (OID 3395840)
-- Name: auth_groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_groups_id_seq', 1, true);


--
-- TOC entry 10 (OID 3395842)
-- Name: dm_discussion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_discussion_id_seq', 1, true);


--
-- TOC entry 12 (OID 3395844)
-- Name: dm_file_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_file_history_id_seq', 1, true);


--
-- TOC entry 14 (OID 3395846)
-- Name: dm_object_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_object_id_seq', 1, true);


--
-- TOC entry 16 (OID 3395888)
-- Name: dm_object_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_object_type_id_seq', 1, true);


--
-- TOC entry 52 (OID 3395940)
-- Name: dm_workflow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_workflow_id_seq', 1, true);


--
-- TOC entry 53 (OID 3395948)
-- Name: dm_workflow_route_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_workflow_route_id_seq', 1, true);


--
-- TOC entry 54 (OID 3395966)
-- Name: dm_alert_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_alert_id_seq', 1, true);


--
-- TOC entry 3 (OID 2200)
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


ALTER TABLE dm_alert 
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_bookmark
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_discussion
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_file_history 
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_index
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_keyword 
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_object_log
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_object_parent
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_object_perm
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_subscribe
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_url
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_workflow
ADD FOREIGN KEY (object_id)
REFERENCES dm_object (id);

ALTER TABLE dm_workflow_route
ADD FOREIGN KEY (workflow_id)
REFERENCES dm_workflow (id);

ALTER TABLE dm_task
ADD FOREIGN KEY (task_id)
REFERENCES dm_workflow_route (id);

";

if (db_query($newconn,$sql)) echo "New database created successfully\n";
else die("Database creation failed\n");


