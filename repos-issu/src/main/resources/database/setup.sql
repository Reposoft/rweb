--- create the database structure in HSQLDB or MYSQL ---

CREATE TABLE issue (
	id int NOT NULL,
	name varchar(100) NOT NULL);
				
CREATE TABLE hibernate_sequences (
	sequence_name varchar(100) NOT NULL, 
	sequence_next_hi_value int NOT NULL);
	
--- sample issue ---
INSERT INTO hibernate_sequences (sequence_name, sequence_next_hi_value) 
	VALUES ('Issue', 2);

INSERT INTO issue (id, name)
	VALUES (1, 'Try issu.se');

commit;