CREATE INDEX schedule_company_id_start_time ON schedule(company_id,start_time);
DROP INDEX IF EXISTS request_type_id;
DROP INDEX IF EXISTS request_status_id;
DROP INDEX IF EXISTS system_log_user_id_table_name_action_id;
DROP INDEX IF EXISTS user_date_total_object_type_id;