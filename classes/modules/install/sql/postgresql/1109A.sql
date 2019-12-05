ALTER TABLE company ADD COLUMN terminated_user_disable_login_type_id integer DEFAULT 10;
ALTER TABLE company ADD COLUMN terminated_user_disable_login_after_days integer DEFAULT 180;
ALTER TABLE user_default ADD COLUMN terminated_permission_control_id uuid DEFAULT '00000000-0000-0000-0000-000000000000';

ALTER TABLE users ADD COLUMN enable_login smallint NOT NULL DEFAULT 1;
ALTER TABLE users ADD COLUMN login_expire_date date;
ALTER TABLE users ADD COLUMN terminated_permission_control_id uuid;