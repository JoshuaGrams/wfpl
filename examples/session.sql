drop table if exists wfpl_sessions;
create table wfpl_sessions (
	id int unique auto_increment,
	session_key varchar(16),
	length int,
	expires int);

drop table if exists wfpl_session_data;
create table wfpl_session_data (
	id int unique auto_increment,
	session_id int,
	name varchar(100),
	value text);
