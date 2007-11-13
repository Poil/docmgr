<?php

$sql = "

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

ALTER TABLE dm_file_history ADD COLUMN tmpsize text;
UPDATE dm_file_history SET tmpsize=size;
ALTER TABLE dm_file_history DROP COLUMN size;
ALTER TABLE dm_file_history ADD COLUMN size numeric;
ALTER TABLE dm_file_history ALTER COLUMN size SET DEFAULT 0;
UPDATE dm_file_history SET size='0';
UPDATE dm_file_history SET size=tmpsize::numeric WHERE tmpsize IS NOT NULL;
ALTER TABLE dm_file_history DROP COLUMN tmpsize;

";

if (db_query($conn,$sql)) echo "Upgrade to 0.53 complete.  If you use tsearch2, please run reindex.php when finished\n";
else die("Database upgrade to 0.53 failed\n");
