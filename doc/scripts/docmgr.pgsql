--
-- PostgreSQL database dump
--

SET SESSION AUTHORIZATION 'postgres';

--
-- TOC entry 3 (OID 2200)
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


SET search_path = public, pg_catalog;

--
-- TOC entry 4 (OID 995849)
-- Name: auth_accounts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_accounts_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 6 (OID 995851)
-- Name: auth_groups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE auth_groups_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 8 (OID 995853)
-- Name: dm_discussion_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_discussion_id_seq
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 10 (OID 995855)
-- Name: dm_file_history_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_file_history_id_seq
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 12 (OID 995857)
-- Name: dm_object_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_object_id_seq
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 16 (OID 995859)
-- Name: auth_accounts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_accounts (
    id integer DEFAULT nextval('"auth_accounts_id_seq"'::text) NOT NULL,
    login text NOT NULL,
    "password" text NOT NULL,
    first_name text,
    last_name text,
    email text,
    phone text
);


--
-- TOC entry 17 (OID 995865)
-- Name: auth_grouplink; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_grouplink (
    accountid integer NOT NULL,
    groupid integer NOT NULL
);


--
-- TOC entry 18 (OID 995867)
-- Name: auth_groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_groups (
    id integer DEFAULT nextval('"auth_groups_id_seq"'::text) NOT NULL,
    name text NOT NULL
);


--
-- TOC entry 19 (OID 995873)
-- Name: dm_discussion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_discussion (
    id integer DEFAULT nextval('"dm_discussion_id_seq"'::text) NOT NULL,
    object_id bigint NOT NULL,
    header text,
    account_id bigint NOT NULL,
    content text,
    "owner" bigint NOT NULL,
    time_stamp timestamp without time zone
);


--
-- TOC entry 20 (OID 995879)
-- Name: dm_file_history; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_file_history (
    id integer DEFAULT nextval('"dm_file_history_id_seq"'::text) NOT NULL,
    object_id bigint NOT NULL,
    size numeric DEFAULT 0,
    "version" bigint DEFAULT 1 NOT NULL,
    modify timestamp without time zone NOT NULL,
    object_owner bigint NOT NULL,
    name TEXT,
    notes text,
    md5sum text,
    custom_version text
);


--
-- TOC entry 21 (OID 995886)
-- Name: dm_index; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_index (
    object_id integer,
    idxtext text
);


--
-- TOC entry 22 (OID 995891)
-- Name: auth_accountperm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_accountperm (
    account_id integer NOT NULL,
    bitset integer DEFAULT 0 NOT NULL,
    enable boolean DEFAULT true NOT NULL,
    locked_time timestamp without time zone,
    failed_logins integer DEFAULT 0 NOT NULL,
    failed_logins_locked boolean DEFAULT false NOT NULL,
    last_success_login timestamp without time zone DEFAULT '1970-01-01 00:00:00'::timestamp without time zone NOT NULL
);


--
-- TOC entry 23 (OID 995895)
-- Name: auth_groupperm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE auth_groupperm (
    group_id integer NOT NULL,
    bitset integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 14 (OID 995898)
-- Name: dm_object_type_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE dm_object_type_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 24 (OID 995900)
-- Name: dm_object; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object (
    id integer DEFAULT nextval('"dm_object_id_seq"'::text) NOT NULL,
    name text NOT NULL,
    summary text,
    object_type text,
    create_date timestamp without time zone,
    object_owner integer,
    status smallint NOT NULL,
    status_date timestamp without time zone,
    status_owner integer,
    "version" integer DEFAULT 1 NOT NULL,
    "reindex" smallint DEFAULT 0,
    filesize numeric,
    token text
);

CREATE INDEX dm_object_object_type_idx ON dm_object USING btree (object_type);

--
-- TOC entry 25 (OID 995908)
-- Name: dm_object_perm; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_perm (
    object_id integer NOT NULL,
    account_id integer,
    group_id integer,
    bitset smallint
);


--
-- TOC entry 27 (OID 995916)
-- Name: dm_object_parent; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_parent (
    object_id integer,
    parent_id integer
);


--
-- TOC entry 33 (OID 995936)
-- Name: dm_view_collections; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_collections AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object."version", dm_object."reindex", dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM ((dm_object LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id))) WHERE (dm_object.object_type = 'collection');

--
-- TOC entry 35 (OID 995940)
-- Name: dm_object_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_object_log (
    object_id integer,
    account_id integer,
    log_time timestamp without time zone,
    log_type text,
    log_data text
);


--
-- TOC entry 36 (OID 995945)
-- Name: dm_bookmark; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_bookmark (
    object_id integer,
    account_id integer,
    name text
);


--
-- TOC entry 37 (OID 995952)
-- Name: dm_view_bookmarks; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_bookmarks AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object."version", dm_object."reindex", dm_bookmark.object_id, dm_bookmark.account_id FROM dm_object, dm_bookmark WHERE (dm_object.id = dm_bookmark.object_id);


--
-- TOC entry 38 (OID 995955)
-- Name: dm_workflow; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_workflow (
    id serial NOT NULL,
    object_id integer NOT NULL,
    absolute_due timestamp without time zone,
    date_complete timestamp without time zone,
    status text,
    account_id integer,
    date_create timestamp without time zone,
    email_notify boolean
);


--
-- TOC entry 39 (OID 995963)
-- Name: dm_workflow_route; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_workflow_route (
    id serial NOT NULL,
    workflow_id integer NOT NULL,
    account_id integer NOT NULL,
    task_type text,
    task_notes text,
    date_due timestamp without time zone,
    date_complete timestamp without time zone,
    status text,
    sort_order smallint,
    "comment" text
);


--
-- TOC entry 40 (OID 995969)
-- Name: dm_task; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_task (
    account_id integer,
    task_id integer,
    alert_type text,
    date_due timestamp without time zone
);


--
-- TOC entry 41 (OID 995974)
-- Name: dm_subscribe; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_subscribe (
    object_id integer,
    account_id integer,
    send_email boolean,
    event_type text,
    send_file boolean
);


--
-- TOC entry 42 (OID 995981)
-- Name: dm_alert; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_alert (
    id serial NOT NULL,
    object_id integer,
    account_id integer,
    alert_type text
);


--
-- TOC entry 43 (OID 995989)
-- Name: dm_view_alert; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_alert AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object."version", dm_object."reindex", dm_alert.id AS alert_id, dm_alert.object_id, dm_alert.account_id, dm_alert.alert_type FROM dm_object, dm_alert WHERE (dm_object.id = dm_alert.object_id);


--
-- TOC entry 44 (OID 995990)
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
-- TOC entry 46 (OID 996000)
-- Name: dm_view_keyword; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_keyword AS
    SELECT id, name, summary, object_type, create_date, object_owner, status, status_date, status_owner, filesize, dm_keyword.object_id, dm_keyword.field1, dm_keyword.field2, dm_keyword.field3, dm_keyword.field4, dm_keyword.field5, dm_keyword.field6, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset FROM (((dm_object LEFT JOIN dm_keyword ON ((dm_object.id = dm_keyword.object_id))) LEFT JOIN dm_object_parent ON ((dm_object.id = dm_object_parent.object_id))) LEFT JOIN dm_object_perm ON ((dm_object.id = dm_object_perm.object_id)));


--
-- TOC entry 47 (OID 996002)
-- Name: dm_url; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_url (
    object_id integer NOT NULL,
    url text NOT NULL
) WITHOUT OIDS;


--
-- TOC entry 48 (OID 996009)
-- Name: dm_view_workflow; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_workflow AS
    SELECT dm_workflow_route.id, dm_workflow_route.workflow_id, dm_workflow_route.account_id, dm_workflow_route.task_type, dm_workflow_route.date_due AS relative_due, dm_workflow_route.date_complete, dm_workflow_route.status, dm_workflow_route.sort_order, dm_workflow_route."comment", dm_workflow.object_id FROM (dm_workflow_route LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id)));


--
-- TOC entry 49 (OID 996012)
-- Name: dm_task_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_task_view AS
    SELECT dm_task.account_id, dm_task.task_id, dm_task.alert_type, dm_workflow.object_id, dm_workflow_route.id AS route_id, dm_workflow_route.date_due, dm_workflow_route.task_notes, dm_object.name FROM (((dm_task LEFT JOIN dm_workflow_route ON ((dm_task.task_id = dm_workflow_route.id))) LEFT JOIN dm_workflow ON ((dm_workflow_route.workflow_id = dm_workflow.id))) LEFT JOIN dm_object ON ((dm_workflow.object_id = dm_object.id)));


--
-- TOC entry 50 (OID 996016)
-- Name: dm_view_webdav; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW dm_view_webdav AS
    SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object."version", dm_object_parent.object_id, dm_object_parent.parent_id, (SELECT dm_file_history.id FROM dm_file_history WHERE (dm_file_history.object_id = dm_object.id) ORDER BY dm_file_history."version" DESC LIMIT 1) AS file_id FROM dm_object, dm_object_parent WHERE ((dm_object.id = dm_object_parent.object_id) AND ((dm_object.object_type = 'collection') OR (dm_object.object_type = 'file')));


--
-- TOC entry 51 (OID 996105)
-- Name: dm_email_anon; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE dm_email_anon (
    object_id integer,
    pin text,
    link_encoded text,
    date_expires timestamp without time zone,
    account_id integer,
    "notify" text,
    dest_email text
);


--
-- Data for TOC entry 77 (OID 995859)
-- Name: auth_accounts; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_accounts VALUES (1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', 'User', 'admin@nowhere.com', NULL);


--
-- Data for TOC entry 78 (OID 995865)
-- Name: auth_grouplink; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_grouplink VALUES (1, 1);


--
-- Data for TOC entry 79 (OID 995867)
-- Name: auth_groups; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_groups VALUES (3, 'Guests');
INSERT INTO auth_groups VALUES (1, 'Administrators');
INSERT INTO auth_groups VALUES (2, 'Users');


--
-- Data for TOC entry 80 (OID 995873)
-- Name: dm_discussion; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 81 (OID 995879)
-- Name: dm_file_history; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 82 (OID 995886)
-- Name: dm_index; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 83 (OID 995891)
-- Name: auth_accountperm; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_accountperm VALUES (1, 1, true);


--
-- Data for TOC entry 84 (OID 995895)
-- Name: auth_groupperm; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO auth_groupperm VALUES (1, 1);


--
-- Data for TOC entry 85 (OID 995900)
-- Name: dm_object; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 86 (OID 995908)
-- Name: dm_object_perm; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 88 (OID 995916)
-- Name: dm_object_parent; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 89 (OID 995940)
-- Name: dm_object_log; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 90 (OID 995945)
-- Name: dm_bookmark; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 91 (OID 995955)
-- Name: dm_workflow; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 92 (OID 995963)
-- Name: dm_workflow_route; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 93 (OID 995969)
-- Name: dm_task; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 94 (OID 995974)
-- Name: dm_subscribe; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 95 (OID 995981)
-- Name: dm_alert; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 96 (OID 995990)
-- Name: dm_keyword; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 97 (OID 996002)
-- Name: dm_url; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for TOC entry 98 (OID 996105)
-- Name: dm_email_anon; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- TOC entry 75 (OID 996027)
-- Name: dm_keyword_field_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_keyword_field_idx ON dm_keyword USING btree (field1, field2, field3, field4, field5, field6);


--
-- TOC entry 57 (OID 996028)
-- Name: auth_groups_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX auth_groups_id_key ON auth_groups USING btree (id);


--
-- TOC entry 55 (OID 996029)
-- Name: auth_accounts_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX auth_accounts_id_key ON auth_accounts USING btree (id);


--
-- TOC entry 59 (OID 996030)
-- Name: dm_discussion_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_discussion_id_key ON dm_discussion USING btree (id);


--
-- TOC entry 60 (OID 996031)
-- Name: dm_discussion_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_discussion_object_id_key ON dm_discussion USING btree (object_id);


--
-- TOC entry 62 (OID 996032)
-- Name: dm_file_history_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_file_history_id_key ON dm_file_history USING btree (id);


--
-- TOC entry 63 (OID 996033)
-- Name: dm_file_history_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_file_history_object_id_key ON dm_file_history USING btree (object_id);


--
-- TOC entry 65 (OID 996034)
-- Name: dm_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_object_id_key ON dm_object USING btree (id);


--
-- TOC entry 69 (OID 996035)
-- Name: dm_object_log_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_log_object_id_key ON dm_object_log USING btree (object_id);


--
-- TOC entry 66 (OID 996036)
-- Name: dm_object_search_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_search_key ON dm_object USING btree (name, summary);


--
-- TOC entry 68 (OID 996037)
-- Name: dm_object_parent_search_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_parent_search_key ON dm_object_parent USING btree (object_id, parent_id);


--
-- TOC entry 67 (OID 996038)
-- Name: dm_object_perm_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_object_perm_id_key ON dm_object_perm USING btree (object_id);


--
-- TOC entry 74 (OID 996039)
-- Name: dm_subscribe_info_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_subscribe_info_key ON dm_subscribe USING btree (object_id, account_id);


--
-- TOC entry 73 (OID 996040)
-- Name: dm_task_account_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_task_account_id_key ON dm_task USING btree (account_id);


--
-- TOC entry 76 (OID 996041)
-- Name: dm_url_object_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_url_object_id ON dm_url USING btree (object_id);


--
-- TOC entry 70 (OID 996042)
-- Name: dm_workflow_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_workflow_id_key ON dm_workflow USING btree (id);


--
-- TOC entry 71 (OID 996043)
-- Name: dm_workflow_object_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX dm_workflow_object_id_key ON dm_workflow USING btree (object_id);


--
-- TOC entry 72 (OID 996044)
-- Name: dm_workflow_route_id_key; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX dm_workflow_route_id_key ON dm_workflow_route USING btree (id);


--
-- TOC entry 56 (OID 996045)
-- Name: auth_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_accounts
    ADD CONSTRAINT auth_accounts_pkey PRIMARY KEY (id);


--
-- TOC entry 58 (OID 996047)
-- Name: auth_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY auth_groups
    ADD CONSTRAINT auth_groups_pkey PRIMARY KEY (id);


--
-- TOC entry 61 (OID 996049)
-- Name: dm_discussion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_discussion
    ADD CONSTRAINT dm_discussion_pkey PRIMARY KEY (id);


--
-- TOC entry 64 (OID 996051)
-- Name: dm_file_history_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_file_history
    ADD CONSTRAINT dm_file_history_pkey PRIMARY KEY (id);


--
-- TOC entry 109 (OID 996053)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_alert
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 104 (OID 996057)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_bookmark
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 99 (OID 996061)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_discussion
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 100 (OID 996065)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_file_history
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 101 (OID 996069)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_index
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 110 (OID 996073)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_keyword
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 103 (OID 996077)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_object_parent
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 102 (OID 996081)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_object_perm
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 108 (OID 996085)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_subscribe
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 111 (OID 996089)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_url
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 105 (OID 996093)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_workflow
    ADD CONSTRAINT "$1" FOREIGN KEY (object_id) REFERENCES dm_object(id);


--
-- TOC entry 106 (OID 996097)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_workflow_route
    ADD CONSTRAINT "$1" FOREIGN KEY (workflow_id) REFERENCES dm_workflow(id);


--
-- TOC entry 107 (OID 996101)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY dm_task
    ADD CONSTRAINT "$1" FOREIGN KEY (task_id) REFERENCES dm_workflow_route(id);


--
-- TOC entry 5 (OID 995849)
-- Name: auth_accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_accounts_id_seq', 1, true);


--
-- TOC entry 7 (OID 995851)
-- Name: auth_groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('auth_groups_id_seq', 3, true);


--
-- TOC entry 9 (OID 995853)
-- Name: dm_discussion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_discussion_id_seq', 1, true);


--
-- TOC entry 11 (OID 995855)
-- Name: dm_file_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_file_history_id_seq', 1, true);


--
-- TOC entry 13 (OID 995857)
-- Name: dm_object_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_object_id_seq', 1, true);


--
-- TOC entry 52 (OID 995953)
-- Name: dm_workflow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_workflow_id_seq', 1, true);


--
-- TOC entry 53 (OID 995961)
-- Name: dm_workflow_route_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_workflow_route_id_seq', 1, true);


--
-- TOC entry 54 (OID 995979)
-- Name: dm_alert_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('dm_alert_id_seq', 1, true);


--
-- TOC entry 2 (OID 2200)
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


CREATE TABLE dm_savesearch (
  object_id integer,
  search_string text,
  search_option text,
  date_option text,
  date1 text,
  date2 text,
  show_objects text,
  mod_option text,
  meta_option text,
  col_filter text,
  col_filter_id text,
  account_filter text,
  account_filter_id text,
  search_type text
);

CREATE INDEX dm_savesearch_idx ON dm_savesearch USING btree (object_id);

CREATE TABLE dm_index_queue (
	id SERIAL NOT NULL,
	object_id integer,
	account_id integer,
	notify_user boolean,
	create_date timestamp without time zone
);

CREATE TABLE auth_settings (
	account_id integer NOT NULL,
	home_directory integer,
	language text
);

CREATE VIEW dm_view_perm AS
 	SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, 
	dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, 
	dm_object."reindex", dm_object_perm.object_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset
   	FROM dm_object
   	LEFT JOIN dm_object_perm ON dm_object.id = dm_object_perm.object_id;

CREATE TABLE dm_document (
  id serial NOT NULL,
  object_id bigint NOT NULL,
  version bigint DEFAULT 1 NOT NULL,
  modify timestamp without time zone NOT NULL,
  object_owner bigint NOT NULL,
  notes text
);

CREATE TABLE dm_saveroute (
  id SERIAL,
  account_id integer,
  name text
);

CREATE TABLE dm_saveroute_data (
  account_id integer,
  task_type text,
  task_notes text,
  date_due integer,
  sort_order smallint,
  save_id integer
);

CREATE SEQUENCE "level1" INCREMENT BY 1 MINVALUE 1 MAXVALUE 16 START WITH 1 CYCLE;
CREATE SEQUENCE "level2" INCREMENT BY 1 MINVALUE 1 MAXVALUE 256 START WITH 1 CYCLE;

CREATE TABLE dm_dirlevel (
	object_id integer,
	level1 smallint,
	level2 smallint
);

CREATE INDEX dm_dirlevel_object_id_idx ON dm_dirlevel USING btree (object_id);	

CREATE VIEW dm_view_search AS (
	SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.filesize, dm_index.idxtext,
	dm_dirlevel.level1,dm_dirlevel.level2
	FROM dm_object
	LEFT JOIN dm_index ON dm_object.id = dm_index.object_id
	LEFT JOIN dm_dirlevel ON dm_object.id = dm_dirlevel.object_id
);

CREATE VIEW dm_view_objects AS
 	SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner, dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.version, dm_object."reindex", dm_object.filesize, dm_object.token, dm_object_parent.object_id, dm_object_parent.parent_id, dm_object_perm.account_id, dm_object_perm.group_id, dm_object_perm.bitset, dm_dirlevel.level1, dm_dirlevel.level2
   	FROM dm_object
   	LEFT JOIN dm_object_parent ON dm_object.id = dm_object_parent.object_id
   	LEFT JOIN dm_object_perm ON dm_object.id = dm_object_perm.object_id
   	LEFT JOIN dm_dirlevel ON dm_object.id = dm_dirlevel.object_id;
	
CREATE TABLE dm_object_related (
	object_id integer NOT NULL,
	related_id integer NOT NULL
);

CREATE INDEX dm_object_related_object_id_idx ON dm_object_related USING btree (object_id);
CREATE INDEX dm_object_related_related_id_idx ON dm_object_related USING btree (related_id);
	
CREATE VIEW dm_view_related AS 
	SELECT dm_object_related.*,dm_object.name,dm_object.object_type FROM dm_object_related
	LEFT JOIN dm_object ON dm_object_related.related_id = dm_object.id;

CREATE TABLE db_version (version FLOAT NOT NULL);
INSERT INTO db_version (version) VALUES (0.57);

CREATE TABLE dm_thumb_queue (
        id SERIAL NOT NULL,
        object_id integer,
        account_id integer,
        notify_user boolean,
        create_date timestamp without time zone
);
