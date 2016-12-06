ALTER TABLE contributing_shift_policy RENAME COLUMN include_partial_shift TO include_shift_type_id;

ALTER TABLE user_preference ADD COLUMN distance_format INTEGER NOT NULL DEFAULT 10;
ALTER TABLE user_default ADD COLUMN distance_format INTEGER NOT NULL DEFAULT 10;

ALTER TABLE user_contact ADD COLUMN first_name_metaphone character varying;
ALTER TABLE user_contact ADD COLUMN last_name_metaphone character varying;

ALTER TABLE over_time_policy ADD COLUMN trigger_time_adjust_contributing_shift_policy_id integer DEFAULT 0;

ALTER TABLE holiday_policy ADD COLUMN shift_on_holiday_type_id integer DEFAULT 0;

CREATE INDEX schedule_replaced_id ON schedule USING btree (replaced_id);