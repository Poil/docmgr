
CREATE SCHEMA notification;

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


SET search_path = notification, pg_catalog;

--
-- Name: notifications_pkey; Type: INDEX; Schema: notification; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX notifications_pkey ON notifications USING btree (id);


--
-- Name: options_pkey; Type: INDEX; Schema: notification; Owner: postgres; Tablespace: 
--

CREATE UNIQUE INDEX options_pkey ON options USING btree (id);

