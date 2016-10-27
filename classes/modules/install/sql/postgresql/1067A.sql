DROP TABLE IF EXISTS user_date_total_old;
DROP TABLE IF EXISTS punch_control_old;
DROP TABLE IF EXISTS schedule_old;
DROP TABLE IF EXISTS exception_old;
DROP TABLE IF EXISTS request_old;
DROP SEQUENCE IF EXISTS user_date_total_old_id_seq;
DROP SEQUENCE IF EXISTS punch_control_old_id_seq;
DROP SEQUENCE IF EXISTS schedule_old_id_seq;
DROP SEQUENCE IF EXISTS exception_old_id_seq;
DROP SEQUENCE IF EXISTS request_old_id_seq;

DROP TABLE authentication;
CREATE TABLE authentication (
		session_id character varying(40) NOT NULL,
        object_id integer NOT NULL,
        created_date integer NOT NULL,
        updated_date integer,
		type_id smallint NOT NULL,
        ip_address character varying(45)
);
CREATE UNIQUE INDEX authenication_session_id ON authentication(session_id);

ALTER TABLE recurring_schedule_control ADD COLUMN display_weeks smallint DEFAULT 4;

DROP INDEX recurring_schedule_id;
DROP INDEX recurring_schedule_user_id;
--Some tables have duplicate recurring_schedule_user records, so this will just help clear those out before trying to create the unique index;
DELETE FROM recurring_schedule_user a WHERE a.ctid <> (SELECT min(b.ctid) FROM recurring_schedule_user b WHERE  a.id = b.id);
CREATE UNIQUE INDEX recurring_schedule_user_id ON recurring_schedule_user(id);
CREATE INDEX recurring_schedule_user_user_id ON recurring_schedule_user(user_id);

CREATE TABLE recurring_schedule (
	id serial NOT NULL,
	company_id integer NOT NULL,
	user_id integer NOT NULL,
	recurring_schedule_control_id integer NOT NULL,
	date_stamp date NOT NULL,
	status_id smallint DEFAULT 10 NOT NULL,
	start_time timestamp with time zone NOT NULL,
	end_time timestamp with time zone NOT NULL,
	schedule_policy_id integer DEFAULT 0 NOT NULL,
	absence_policy_id integer DEFAULT 0 NOT NULL,
	branch_id integer DEFAULT 0 NOT NULL,
	department_id integer DEFAULT 0 NOT NULL,
	job_id integer DEFAULT 0 NOT NULL,
	job_item_id integer DEFAULT 0 NOT NULL,
	total_time integer DEFAULT 0 NOT NULL,
	recurring_schedule_template_control_id integer DEFAULT 0 NOT NULL,
	auto_fill smallint DEFAULT 0 NOT NULL,
	created_date integer,
	created_by integer,
	updated_date integer,
	updated_by integer,
	deleted_date integer,
	deleted_by integer,
	deleted smallint DEFAULT 0 NOT NULL,
	other_id1 character varying,
	other_id2 character varying,
	other_id3 character varying,
	other_id4 character varying,
	other_id5 character varying,
	note character varying
);
CREATE UNIQUE INDEX recurring_schedule_id ON recurring_schedule(id);
CREATE INDEX recurring_schedule_company_id ON recurring_schedule(company_id);
CREATE INDEX recurring_schedule_recurring_schedule_control_id_b ON recurring_schedule(recurring_schedule_control_id);
CREATE INDEX recurring_schedule_user_id_user_date ON recurring_schedule(user_id, date_stamp);

ALTER TABLE pay_stub DROP COLUMN advance;
ALTER TABLE pay_stub DROP COLUMN confirm_number;
ALTER TABLE pay_stub ADD COLUMN type_id smallint DEFAULT 10;
ALTER TABLE pay_stub ADD COLUMN run_id smallint DEFAULT 1;
DROP INDEX pay_stub_user_id;
CREATE INDEX pay_stub_user_id_pay_period_id ON pay_stub(user_id,pay_period_id);
CREATE UNIQUE INDEX pay_stub_user_id_pay_period_id_run_id ON pay_stub(user_id,pay_period_id,run_id) WHERE deleted = 0 AND temp = 0;
ALTER TABLE pay_stub CLUSTER ON pay_stub_user_id_pay_period_id;

ALTER TABLE company_deduction ADD COLUMN apply_payroll_run_type_id smallint DEFAULT 0;

ALTER TABLE users ADD COLUMN feedback_rating smallint DEFAULT NULL;

ALTER TABLE user_deduction ADD COLUMN length_of_service_date date;
ALTER TABLE user_deduction ADD COLUMN start_date date;
ALTER TABLE user_deduction ADD COLUMN end_date date;

ALTER TABLE roe ADD COLUMN final_pay_stub_end_date integer;
ALTER TABLE roe ADD COLUMN final_pay_stub_transaction_date integer;
UPDATE roe SET final_pay_stub_end_date = pay_period_end_date;
UPDATE roe SET final_pay_stub_transaction_date = pay_period_end_date;

--Set all PAID/INUSE PSAs that are not actually assigned to pay stub as ACTIVE;
UPDATE pay_stub_amendment SET status_id = 50 WHERE id IN ( SELECT a.id FROM pay_stub_amendment as a LEFT JOIN pay_stub_entry as c ON ( c.pay_stub_amendment_id = a.id AND c.deleted = 0 ) LEFT JOIN pay_stub as b ON ( a.user_id = b.user_id AND to_timestamp(a.effective_date) >= b.start_date AND to_timestamp(a.effective_date) <= b.end_date AND b.deleted = 0 ) WHERE a.status_id in (52,55) AND b.id IS NULL AND c.id IS NULL AND a.deleted = 0 );
--Set all ACTIVE/INUSE PSAs to PAID if they are assigned to a pay stub;
UPDATE pay_stub_amendment SET status_id = 55 WHERE id IN ( SELECT a.id FROM pay_stub_amendment as a, pay_stub_entry as b, pay_stub as c WHERE a.id = b.pay_stub_amendment_id AND b.pay_stub_id = c.id AND a.status_id in (50,52) AND c.status_id in (40,100) AND (a.deleted = 0 AND b.deleted = 0 AND c.deleted = 0) );
--Delete all PSAs with an amount of 0 that could have been assigned to a pay stub but weren't. (PSA was created after pay stub was was, and the pay stub was not regenerated);
UPDATE pay_stub_amendment SET deleted = 1, deleted_date = extract('epoch' from now() ), deleted_by = 0 WHERE id IN ( SELECT a.id FROM pay_stub_amendment as a, pay_stub as b WHERE a.user_id = b.user_id AND to_timestamp(a.effective_date) >= b.start_date AND to_timestamp(a.effective_date) <= b.end_date AND b.status_id in (40,100) AND a.status_id IN (50,52) AND a.amount = 0 ) AND deleted = 0;

ALTER TABLE accrual_policy ADD COLUMN apply_frequency_quarter_month smallint DEFAULT 1;
ALTER TABLE accrual_policy ADD COLUMN enable_pro_rate_initial_period smallint DEFAULT 0;
ALTER TABLE accrual_policy ADD COLUMN enable_opening_balance smallint DEFAULT 0;
ALTER TABLE accrual_policy_milestone RENAME COLUMN minimum_time TO annual_maximum_time;

ALTER TABLE schedule_policy RENAME COLUMN absence_policy_id TO partial_shift_absence_policy_id;
ALTER TABLE schedule_policy ADD COLUMN full_shift_absence_policy_id integer;

DROP TABLE IF EXISTS income_tax_rate;
DROP TABLE IF EXISTS income_tax_rate_us;
DROP TABLE IF EXISTS income_tax_rate_cr;