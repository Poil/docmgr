-- Adjust this setting to control where the objects get CREATEd.
SET search_path = public;

BEGIN;

--dict conf
CREATE TABLE pg_ts_dict (
	dict_name	text not null primary key,
	dict_init	oid,
	dict_initoption	text,
	dict_lexize	oid not null,
	dict_comment	text
) with oids;

--dict interface
CREATE FUNCTION lexize(oid, text) 
	returns _text
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

CREATE FUNCTION lexize(text, text)
        returns _text
        as '$libdir/tsearch2', 'lexize_byname'
        language 'C'
        with (isstrict);

CREATE FUNCTION lexize(text)
        returns _text
        as '$libdir/tsearch2', 'lexize_bycurrent'
        language 'C'
        with (isstrict);

CREATE FUNCTION set_curdict(int)
	returns void
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

CREATE FUNCTION set_curdict(text)
	returns void
	as '$libdir/tsearch2', 'set_curdict_byname'
	language 'C'
	with (isstrict);

--built-in dictionaries
CREATE FUNCTION dex_init(text)
	returns internal
	as '$libdir/tsearch2' 
	language 'C';

CREATE FUNCTION dex_lexize(internal,internal,int4)
	returns internal
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

insert into pg_ts_dict select 
	'simple', 
	(select oid from pg_proc where proname='dex_init'),
	null,
	(select oid from pg_proc where proname='dex_lexize'),
	'Simple example of dictionary.'
;
	 
CREATE FUNCTION snb_en_init(text)
	returns internal
	as '$libdir/tsearch2' 
	language 'C';

CREATE FUNCTION snb_lexize(internal,internal,int4)
	returns internal
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

insert into pg_ts_dict select 
	'en_stem', 
	(select oid from pg_proc where proname='snb_en_init'),
	'/usr/local/pgsql/share/contrib/english.stop',
	(select oid from pg_proc where proname='snb_lexize'),
	'English Stemmer. Snowball.'
;

CREATE FUNCTION snb_ru_init(text)
	returns internal
	as '$libdir/tsearch2' 
	language 'C';

insert into pg_ts_dict select 
	'ru_stem', 
	(select oid from pg_proc where proname='snb_ru_init'),
	'/usr/local/pgsql/share/contrib/russian.stop',
	(select oid from pg_proc where proname='snb_lexize'),
	'Russian Stemmer. Snowball.'
;
	 
CREATE FUNCTION spell_init(text)
	returns internal
	as '$libdir/tsearch2' 
	language 'C';

CREATE FUNCTION spell_lexize(internal,internal,int4)
	returns internal
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

insert into pg_ts_dict select 
	'ispell_template', 
	(select oid from pg_proc where proname='spell_init'),
	null,
	(select oid from pg_proc where proname='spell_lexize'),
	'ISpell interface. Must have .dict and .aff files'
;

CREATE FUNCTION syn_init(text)
	returns internal
	as '$libdir/tsearch2' 
	language 'C';

CREATE FUNCTION syn_lexize(internal,internal,int4)
	returns internal
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

insert into pg_ts_dict select 
	'synonym', 
	(select oid from pg_proc where proname='syn_init'),
	null,
	(select oid from pg_proc where proname='syn_lexize'),
	'Example of synonym dictionary'
;

--dict conf
CREATE TABLE pg_ts_parser (
	prs_name	text not null primary key,
	prs_start	oid not null,
	prs_nexttoken	oid not null,
	prs_end		oid not null,
	prs_headline	oid not null,
	prs_lextype	oid not null,
	prs_comment	text
) with oids;

--sql-level interface
CREATE TYPE tokentype 
	as (tokid int4, alias text, descr text); 

CREATE FUNCTION token_type(int4)
	returns setof tokentype
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

CREATE FUNCTION token_type(text)
	returns setof tokentype
	as '$libdir/tsearch2', 'token_type_byname'
	language 'C'
	with (isstrict);

CREATE FUNCTION token_type()
	returns setof tokentype
	as '$libdir/tsearch2', 'token_type_current'
	language 'C'
	with (isstrict);

CREATE FUNCTION set_curprs(int)
	returns void
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

CREATE FUNCTION set_curprs(text)
	returns void
	as '$libdir/tsearch2', 'set_curprs_byname'
	language 'C'
	with (isstrict);

CREATE TYPE tokenout 
	as (tokid int4, token text);

CREATE FUNCTION parse(oid,text)
	returns setof tokenout
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);
 
CREATE FUNCTION parse(text,text)
	returns setof tokenout
	as '$libdir/tsearch2', 'parse_byname'
	language 'C'
	with (isstrict);
 
CREATE FUNCTION parse(text)
	returns setof tokenout
	as '$libdir/tsearch2', 'parse_current'
	language 'C'
	with (isstrict);
 
--default parser
CREATE FUNCTION prsd_start(internal,int4)
	returns internal
	as '$libdir/tsearch2'
	language 'C';

CREATE FUNCTION prsd_getlexeme(internal,internal,internal)
	returns int4
	as '$libdir/tsearch2'
	language 'C';

CREATE FUNCTION prsd_end(internal)
	returns void
	as '$libdir/tsearch2'
	language 'C';

CREATE FUNCTION prsd_lextype(internal)
	returns internal
	as '$libdir/tsearch2'
	language 'C';

CREATE FUNCTION prsd_headline(internal,internal,internal)
	returns internal
	as '$libdir/tsearch2'
	language 'C';

insert into pg_ts_parser select
	'default',
	(select oid from pg_proc where proname='prsd_start'),	
	(select oid from pg_proc where proname='prsd_getlexeme'),	
	(select oid from pg_proc where proname='prsd_end'),	
	(select oid from pg_proc where proname='prsd_headline'),
	(select oid from pg_proc where proname='prsd_lextype'),
	'Parser from OpenFTS v0.34'
;	

--tsearch config

CREATE TABLE pg_ts_cfg (
	ts_name		text not null primary key,
	prs_name	text not null,
	locale		text
) with oids;

CREATE TABLE pg_ts_cfgmap (
	ts_name		text not null,
	tok_alias	text not null,
	dict_name	text[],
	primary key (ts_name,tok_alias)
) with oids;

CREATE FUNCTION set_curcfg(int)
	returns void
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

CREATE FUNCTION set_curcfg(text)
	returns void
	as '$libdir/tsearch2', 'set_curcfg_byname'
	language 'C'
	with (isstrict);

CREATE FUNCTION show_curcfg()
	returns oid
	as '$libdir/tsearch2'
	language 'C'
	with (isstrict);

insert into pg_ts_cfg values ('default', 'default','C');
insert into pg_ts_cfg values ('default_russian', 'default','ru_RU.KOI8-R');
insert into pg_ts_cfg values ('simple', 'default');

copy pg_ts_cfgmap from stdin;
default	lword	{en_stem}
default	nlword	{simple}
default	word	{simple}
default	email	{simple}
default	url	{simple}
default	host	{simple}
default	sfloat	{simple}
default	version	{simple}
default	part_hword	{simple}
default	nlpart_hword	{simple}
default	lpart_hword	{en_stem}
default	hword	{simple}
default	lhword	{en_stem}
default	nlhword	{simple}
default	uri	{simple}
default	file	{simple}
default	float	{simple}
default	int	{simple}
default	uint	{simple}
default_russian	lword	{en_stem}
default_russian	nlword	{ru_stem}
default_russian	word	{ru_stem}
default_russian	email	{simple}
default_russian	url	{simple}
default_russian	host	{simple}
default_russian	sfloat	{simple}
default_russian	version	{simple}
default_russian	part_hword	{simple}
default_russian	nlpart_hword	{ru_stem}
default_russian	lpart_hword	{en_stem}
default_russian	hword	{ru_stem}
default_russian	lhword	{en_stem}
default_russian	nlhword	{ru_stem}
default_russian	uri	{simple}
default_russian	file	{simple}
default_russian	float	{simple}
default_russian	int	{simple}
default_russian	uint	{simple}
simple	lword	{simple}
simple	nlword	{simple}
simple	word	{simple}
simple	email	{simple}
simple	url	{simple}
simple	host	{simple}
simple	sfloat	{simple}
simple	version	{simple}
simple	part_hword	{simple}
simple	nlpart_hword	{simple}
simple	lpart_hword	{simple}
simple	hword	{simple}
simple	lhword	{simple}
simple	nlhword	{simple}
simple	uri	{simple}
simple	file	{simple}
simple	float	{simple}
simple	int	{simple}
simple	uint	{simple}
\.

--tsvector type
CREATE FUNCTION tsvector_in(cstring)
RETURNS tsvector
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE FUNCTION tsvector_out(tsvector)
RETURNS cstring
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE TYPE tsvector (
        INTERNALLENGTH = -1,
        INPUT = tsvector_in,
        OUTPUT = tsvector_out,
        STORAGE = extended
);

CREATE FUNCTION length(tsvector)
RETURNS int4
AS '$libdir/tsearch2', 'tsvector_length'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE FUNCTION to_tsvector(oid, text)
RETURNS tsvector
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE FUNCTION to_tsvector(text, text)
RETURNS tsvector
AS '$libdir/tsearch2', 'to_tsvector_name'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE FUNCTION to_tsvector(text)
RETURNS tsvector
AS '$libdir/tsearch2', 'to_tsvector_current'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE FUNCTION strip(tsvector)
RETURNS tsvector
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE FUNCTION setweight(tsvector,"char")
RETURNS tsvector
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE FUNCTION concat(tsvector,tsvector)
RETURNS tsvector
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict,iscachable);

CREATE OPERATOR || (
        LEFTARG = tsvector,
        RIGHTARG = tsvector,
        PROCEDURE = concat
);

--query type
CREATE FUNCTION tsquery_in(cstring)
RETURNS tsquery
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE FUNCTION tsquery_out(tsquery)
RETURNS cstring
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE TYPE tsquery (
        INTERNALLENGTH = -1,
        INPUT = tsquery_in,
        OUTPUT = tsquery_out
);

CREATE FUNCTION querytree(tsquery)
RETURNS text
AS '$libdir/tsearch2', 'tsquerytree'
LANGUAGE 'C' with (isstrict);

CREATE FUNCTION to_tsquery(oid, text)
RETURNS tsquery
AS '$libdir/tsearch2'
LANGUAGE 'c' with (isstrict,iscachable);

CREATE FUNCTION to_tsquery(text, text)
RETURNS tsquery
AS '$libdir/tsearch2','to_tsquery_name'
LANGUAGE 'c' with (isstrict,iscachable);

CREATE FUNCTION to_tsquery(text)
RETURNS tsquery
AS '$libdir/tsearch2','to_tsquery_current'
LANGUAGE 'c' with (isstrict,iscachable);

--operations
CREATE FUNCTION exectsq(tsvector, tsquery)
RETURNS bool
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict, iscachable);
  
COMMENT ON FUNCTION exectsq(tsvector, tsquery) IS 'boolean operation with text index';

CREATE FUNCTION rexectsq(tsquery, tsvector)
RETURNS bool
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict, iscachable);

COMMENT ON FUNCTION rexectsq(tsquery, tsvector) IS 'boolean operation with text index';

CREATE OPERATOR @@ (
        LEFTARG = tsvector,
        RIGHTARG = tsquery,
        PROCEDURE = exectsq,
        COMMUTATOR = '@@',
        RESTRICT = contsel,
        JOIN = contjoinsel
);
CREATE OPERATOR @@ (
        LEFTARG = tsquery,
        RIGHTARG = tsvector,
        PROCEDURE = rexectsq,
        COMMUTATOR = '@@',
        RESTRICT = contsel,
        JOIN = contjoinsel
);

--Trigger
CREATE FUNCTION tsearch2()
RETURNS trigger
AS '$libdir/tsearch2'
LANGUAGE 'C';

--Relevation
CREATE FUNCTION rank(float4[], tsvector, tsquery)
RETURNS float4
AS '$libdir/tsearch2'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank(float4[], tsvector, tsquery, int4)
RETURNS float4
AS '$libdir/tsearch2'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank(tsvector, tsquery)
RETURNS float4
AS '$libdir/tsearch2', 'rank_def'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank(tsvector, tsquery, int4)
RETURNS float4
AS '$libdir/tsearch2', 'rank_def'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank_cd(int4, tsvector, tsquery)
RETURNS float4
AS '$libdir/tsearch2'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank_cd(int4, tsvector, tsquery, int4)
RETURNS float4
AS '$libdir/tsearch2'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank_cd(tsvector, tsquery)
RETURNS float4
AS '$libdir/tsearch2', 'rank_cd_def'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION rank_cd(tsvector, tsquery, int4)
RETURNS float4
AS '$libdir/tsearch2', 'rank_cd_def'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION headline(oid, text, tsquery, text)
RETURNS text
AS '$libdir/tsearch2', 'headline'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION headline(oid, text, tsquery)
RETURNS text
AS '$libdir/tsearch2', 'headline'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION headline(text, text, tsquery, text)
RETURNS text
AS '$libdir/tsearch2', 'headline_byname'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION headline(text, text, tsquery)
RETURNS text
AS '$libdir/tsearch2', 'headline_byname'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION headline(text, tsquery, text)
RETURNS text
AS '$libdir/tsearch2', 'headline_current'
LANGUAGE 'C' WITH (isstrict, iscachable);

CREATE FUNCTION headline(text, tsquery)
RETURNS text
AS '$libdir/tsearch2', 'headline_current'
LANGUAGE 'C' WITH (isstrict, iscachable);

--GiST
--GiST key type 
CREATE FUNCTION gtsvector_in(cstring)
RETURNS gtsvector
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE FUNCTION gtsvector_out(gtsvector)
RETURNS cstring
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE TYPE gtsvector (
        INTERNALLENGTH = -1,
        INPUT = gtsvector_in,
        OUTPUT = gtsvector_out
);

-- support FUNCTIONs
CREATE FUNCTION gtsvector_consistent(gtsvector,internal,int4)
RETURNS bool
AS '$libdir/tsearch2'
LANGUAGE 'C';
  
CREATE FUNCTION gtsvector_compress(internal)
RETURNS internal
AS '$libdir/tsearch2'
LANGUAGE 'C';

CREATE FUNCTION gtsvector_decompress(internal)
RETURNS internal
AS '$libdir/tsearch2'
LANGUAGE 'C';

CREATE FUNCTION gtsvector_penalty(internal,internal,internal)
RETURNS internal
AS '$libdir/tsearch2'
LANGUAGE 'C' with (isstrict);

CREATE FUNCTION gtsvector_picksplit(internal, internal)
RETURNS internal
AS '$libdir/tsearch2'
LANGUAGE 'C';

CREATE FUNCTION gtsvector_union(bytea, internal)
RETURNS _int4
AS '$libdir/tsearch2'
LANGUAGE 'C';

CREATE FUNCTION gtsvector_same(gtsvector, gtsvector, internal)
RETURNS internal
AS '$libdir/tsearch2'
LANGUAGE 'C';

-- CREATE the OPERATOR class
CREATE OPERATOR CLASS gist_tsvector_ops
DEFAULT FOR TYPE tsvector USING gist
AS
        OPERATOR        1       @@ (tsvector, tsquery)  RECHECK ,
        FUNCTION        1       gtsvector_consistent (gtsvector, internal, int4),
        FUNCTION        2       gtsvector_union (bytea, internal),
        FUNCTION        3       gtsvector_compress (internal),
        FUNCTION        4       gtsvector_decompress (internal),
        FUNCTION        5       gtsvector_penalty (internal, internal, internal),
        FUNCTION        6       gtsvector_picksplit (internal, internal),
        FUNCTION        7       gtsvector_same (gtsvector, gtsvector, internal),
        STORAGE         gtsvector;


--stat info
CREATE TYPE statinfo 
	as (word text, ndoc int4, nentry int4);

--REATE FUNCTION tsstat_in(cstring)
--RETURNS tsstat
--AS '$libdir/tsearch2'
--LANGUAGE 'C' with (isstrict);
--
--CREATE FUNCTION tsstat_out(tsstat)
--RETURNS cstring
--AS '$libdir/tsearch2'
--LANGUAGE 'C' with (isstrict);
--
--CREATE TYPE tsstat (
--        INTERNALLENGTH = -1,
--        INPUT = tsstat_in,
--        OUTPUT = tsstat_out,
--        STORAGE = plain
--);
--
--CREATE FUNCTION ts_accum(tsstat,tsvector)
--RETURNS tsstat
--AS '$libdir/tsearch2'
--LANGUAGE 'C' with (isstrict);
--
--CREATE FUNCTION ts_accum_finish(tsstat)
--	returns setof statinfo
--	as '$libdir/tsearch2'
--	language 'C'
--	with (isstrict);
--
--CREATE AGGREGATE stat (
--	BASETYPE=tsvector,
--	SFUNC=ts_accum,
--	STYPE=tsstat,
--	FINALFUNC = ts_accum_finish,
--	initcond = ''
--); 

CREATE FUNCTION stat(text)
	returns setof statinfo
	as '$libdir/tsearch2', 'ts_stat'
	language 'C'
	with (isstrict);

--reset - just for debuging
CREATE FUNCTION reset_tsearch()
        returns void
        as '$libdir/tsearch2'
        language 'C'
        with (isstrict);

--get cover (debug for rank_cd)
CREATE FUNCTION get_covers(tsvector,tsquery)
        returns text
        as '$libdir/tsearch2'
        language 'C'
        with (isstrict);

--debug function
create type tsdebug as (
        ts_name text,
        tok_type text,
        description text,
        token   text,
        dict_name text[],
        "tsvector" tsvector
);

create function _get_parser_from_curcfg() 
returns text as 
' select prs_name from pg_ts_cfg where oid = show_curcfg() '
language 'SQL' with(isstrict,iscachable);

create function ts_debug(text)
returns setof tsdebug as '
select 
        m.ts_name,
        t.alias as tok_type,
        t.descr as description,
        p.token,
        m.dict_name,
        strip(to_tsvector(p.token)) as tsvector
from
        parse( _get_parser_from_curcfg(), $1 ) as p,
        token_type() as t,
        pg_ts_cfgmap as m,
        pg_ts_cfg as c
where
        t.tokid=p.tokid and
        t.alias = m.tok_alias and 
        m.ts_name=c.ts_name and 
        c.oid=show_curcfg() 
' language 'SQL' with(isstrict);
END;

BEGIN;

ALTER TABLE dm_index ADD COLUMN idxfti tsvector;

DROP VIEW dm_view_search;

CREATE VIEW dm_view_search AS (
SELECT dm_object.id, dm_object.name, dm_object.summary, dm_object.object_type, dm_object.create_date, dm_object.object_owner,
dm_object.status, dm_object.status_date, dm_object.status_owner, dm_object.filesize, dm_index.idxfti,
dm_dirlevel.level1,dm_dirlevel.level2
FROM dm_object
LEFT JOIN dm_index ON dm_object.id = dm_index.object_id
LEFT JOIN dm_dirlevel ON dm_object.id = dm_dirlevel.object_id
);
                                                

END;
