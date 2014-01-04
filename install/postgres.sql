CREATE TABLE config (
    id bigserial primary key,
    name varchar(255) NOT NULL,
    value varchar(255) NOT NULL
);

CREATE TABLE entries (
    id bigserial primary key,
    title varchar(255) NOT NULL,
    url varchar(255) NOT NULL,
    is_read boolean DEFAULT false,
    is_fav boolean DEFAULT false,
    content TEXT,
    user_id integer NOT NULL
);

CREATE TABLE users (
    id bigserial primary key,
    username varchar(255) NOT NULL,
    password varchar(255) NOT NULL,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL
);

CREATE TABLE users_config (
    id bigserial primary key,
    user_id integer NOT NULL,
    name varchar(255) NOT NULL,
    value varchar(255) NOT NULL
);

CREATE TABLE tags (
  id bigserial primary key,
  value varchar(255) NOT NULL
);

CREATE TABLE tags_entries (
  id bigserial primary key,
  entry_id integer NOT NULL,
  tag_id integer NOT NULL
)