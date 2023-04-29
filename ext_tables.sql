CREATE TABLE tx_loginlink_token
(
	user_uid int(11) DEFAULT '0' NOT NULL,
	auth_type varchar(2) DEFAULT '' NOT NULL,
	token varchar(100) DEFAULT '' NOT NULL,
	valid_until int(11) DEFAULT '0' NOT NULL
);
