ALTER TABLE legal_entity ADD COLUMN short_name varchar(15);
ALTER TABLE legal_entity ADD COLUMN payment_services_status_id integer DEFAULT 20;
ALTER TABLE legal_entity ADD COLUMN payment_services_user_name character varying;
ALTER TABLE legal_entity ADD COLUMN payment_services_api_key character varying;