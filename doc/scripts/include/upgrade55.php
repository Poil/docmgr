<?php

$sql = "

CREATE TABLE dm_document (
  id serial NOT NULL,
  object_id bigint NOT NULL,
  version bigint DEFAULT 1 NOT NULL,
  modify timestamp without time zone NOT NULL,
  object_owner bigint NOT NULL,
  notes text
);

ALTER TABLE auth_settings ADD COLUMN home_directory integer;
ALTER TABLE dm_subscribe ADD COLUMN send_file boolean;
ALTER TABLE dm_workflow ADD COLUMN email_notify boolean;

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



";

if (db_query($conn,$sql)) echo "Upgrade to 0.55 completed successfully\n";
else echo "Upgrade to 0.55 failed\n";
