drop table if exists ~form_name~;
create table ~form_name~ (
    id int unique auto_increment<!--~column start~-->,
    ~name~ ~type~ not null default ~default~<!--~end~-->
);
