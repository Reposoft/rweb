--- create the database structure in HSQLDB or MYSQL ---

CREATE TABLE issue (
	id int NOT NULL,
	name varchar(100) NOT NULL);
				
--- sample issue ---
INSERT INTO issue (id, name)
	VALUES (1, 'Try issu.se');

commit;