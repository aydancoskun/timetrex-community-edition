ALTER TABLE exception_policy ALTER COLUMN demerit SET DATA TYPE numeric(10,4);
ALTER TABLE premium_policy ADD COLUMN min_max_time_type_id integer NOT NULL DEFAULT 10;