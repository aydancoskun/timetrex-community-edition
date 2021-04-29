DROP INDEX IF EXISTS company_generic_tag_map_id;
CREATE UNIQUE INDEX company_generic_tag_map_id ON company_generic_tag_map USING btree (id);
CREATE INDEX recurring_schedule_company_id_start_time ON recurring_schedule ( company_id, start_time );