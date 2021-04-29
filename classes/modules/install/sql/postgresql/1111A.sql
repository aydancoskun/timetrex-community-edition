--C=Return to School is now E03=Quit / Return to school
UPDATE roe SET code_id = 'E03' WHERE code_id = 'C';
--Pad all codes so they are A00, B00, etc...
UPDATE roe SET code_id = rpad( code_id, 3, '0');
--Add status and default to Pending
ALTER TABLE roe ADD COLUMN status_id integer DEFAULT 10;
--Force existing ROEs to status=Submitted (ROE WEB)
UPDATE roe set status_id = 200;