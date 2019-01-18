CREATE TABLE legal_entity (
  id serial NOT NULL,
  company_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  classification_code character varying,
  legal_name character varying NOT NULL,
  trade_name character varying NOT NULL,
  address1 character varying,
  address2 character varying,
  country character varying,
  city character varying,
  province character varying,
  postal_code character varying,
  work_phone character varying,
  fax_phone character varying,
  start_date integer DEFAULT NULL,
  end_date integer DEFAULT NULL,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE payroll_remittance_agency (
  id serial NOT NULL,
  legal_entity_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  name character varying NOT NULL,
  description text,
  country character varying,
  province character varying,
  district character varying,
  agency_id character varying NOT NULL,
  primary_identification character varying,
  secondary_identification character varying,
  tertiary_identification character varying,
  user_name character varying,
  password character varying,
  contact_user_id integer NOT NULL,
  remittance_source_account_id integer NOT NULL,
  start_date integer DEFAULT NULL,
  end_date integer DEFAULT NULL,
  always_week_day_id smallint DEFAULT 0,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE payroll_remittance_agency_event (
  id SERIAL NOT NULL,
  payroll_remittance_agency_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id CHARACTER VARYING NOT NULL,
  frequency_id integer NOT NULL,
  quarter_month smallint DEFAULT 0,
  primary_month smallint DEFAULT 0,
  primary_day_of_month smallint DEFAULT 0,
  secondary_month smallint DEFAULT 0,
  secondary_day_of_month smallint DEFAULT 0,
  day_of_week smallint DEFAULT 0,
  due_date_delay_days INT,
  effective_date integer,
  reminder_user_id integer,
  user_report_data_id integer,
  reminder_days integer,
  note CHARACTER VARYING NULL,
  last_due_date timestamp with time zone,
  due_date timestamp with time zone,
  start_date timestamp with time zone,
  end_date timestamp with time zone,
  next_reminder_date timestamp with time zone,
  last_reminder_date timestamp with time zone,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL
);

CREATE TABLE remittance_source_account (
  id serial NOT NULL,
  legal_entity_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  country character varying,
  name character varying NOT NULL,
  description text,
  data_format_id integer NOT NULL,
  currency_id integer NOT NULL,
  last_transaction_number character varying,
  value1 character varying,
  value2 character varying,
  value3 character varying,
  value4 character varying,
  value5 character varying,
  value6 character varying,
  value7 character varying,
  value8 character varying,
  value9 character varying,
  value10 character varying,
  value11 character varying,
  value12 character varying,
  value13 character varying,
  value14 character varying,
  value15 character varying,
  value16 character varying,
  value17 character varying,
  value18 character varying,
  value19 character varying,
  value20 character varying,
  value21 character varying,
  value22 character varying,
  value23 character varying,
  value24 character varying,
  value25 character varying,
  value26 character varying,
  value27 character varying,
  value28 character varying,
  value29 character varying,
  value30 character varying,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL
);


CREATE TABLE remittance_destination_account (
  id serial NOT NULL,
  remittance_source_account_id integer NOT NULL,
  user_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  name character varying NOT NULL,
  description text,
  currency_id integer NOT NULL,
  priority integer NOT NULL,
  amount_type_id integer NOT NULL,
  amount numeric(18,4) NOT NULL DEFAULT 0,
  percent_amount numeric(18,4) NOT NULL DEFAULT 0,
  value1 character varying,
  value2 character varying,
  value3 character varying,
  value4 character varying,
  value5 character varying,
  value6 character varying,
  value7 character varying,
  value8 character varying,
  value9 character varying,
  value10 character varying,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL
);


CREATE TABLE pay_stub_transaction (
  id serial NOT NULL,
  parent_id integer NOT NULL DEFAULT 0,
  pay_stub_id integer NOT NULL,
  remittance_source_account_id integer NOT NULL,
  remittance_destination_account_id integer NOT NULL,
  currency_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  currency_rate numeric(18,10) NOT NULL DEFAULT 1,
  amount numeric(18,4) NOT NULL DEFAULT 0,
  transaction_date timestamp without time zone,
  confirmation_number character varying,
  note character varying,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL
);



--DEFAULT 0 must be on the below three tables, so we can properly upgrade the DB for existing customers;
ALTER TABLE company_deduction ADD COLUMN legal_entity_id integer DEFAULT 0;
ALTER TABLE company_deduction ADD COLUMN payroll_remittance_agency_id integer DEFAULT 0;
ALTER TABLE users ADD COLUMN legal_entity_id integer DEFAULT 0;
ALTER TABLE user_default ADD COLUMN legal_entity_id integer DEFAULT 0;


CREATE UNIQUE INDEX legal_entity_id ON legal_entity(id);
CREATE INDEX legal_entity_company_id ON legal_entity(company_id);
ALTER TABLE "legal_entity" CLUSTER ON "legal_entity_company_id";

CREATE UNIQUE INDEX payroll_remittance_agency_id ON payroll_remittance_agency(id);
CREATE INDEX payroll_remittance_agency_legal_entity_id ON payroll_remittance_agency(legal_entity_id);
ALTER TABLE "payroll_remittance_agency" CLUSTER ON "payroll_remittance_agency_legal_entity_id";

CREATE UNIQUE INDEX remittance_source_account_id ON remittance_source_account(id);
CREATE INDEX remittance_source_account_legal_entity_id ON remittance_source_account(legal_entity_id);
ALTER TABLE "remittance_source_account" CLUSTER ON "remittance_source_account_legal_entity_id";

CREATE UNIQUE INDEX remittance_destination_account_id ON remittance_destination_account(id);
CREATE INDEX remittance_destination_account_user_id ON remittance_destination_account(user_id);
CREATE INDEX remittance_destination_account_remittance_source_account_id ON remittance_destination_account(remittance_source_account_id);
ALTER TABLE "remittance_destination_account" CLUSTER ON "remittance_destination_account_user_id";

CREATE UNIQUE INDEX pay_stub_transaction_id ON pay_stub_transaction(id);
CREATE INDEX pay_stub_transaction_pay_stub_id ON pay_stub_transaction(pay_stub_id);
CREATE INDEX pay_stub_transaction_remittance_source_account_id ON pay_stub_transaction(remittance_source_account_id);
ALTER TABLE "pay_stub_transaction" CLUSTER ON "pay_stub_transaction_pay_stub_id";

CREATE UNIQUE INDEX payroll_remittance_agency_event_id ON payroll_remittance_agency_event(id);
CREATE INDEX payroll_remittance_agency_event_payroll_remittance_agency_id ON payroll_remittance_agency_event(payroll_remittance_agency_id);

DROP SEQUENCE IF EXISTS user_generic_status_batch_id_seq;
DROP SEQUENCE IF EXISTS user_hierarchy_seperator_id_seq;

DROP TABLE IF EXISTS user_pay_period_total;
DROP SEQUENCE IF EXISTS user_pay_period_total_id_seq;
DROP TABLE bread_crumb;
DROP SEQUENCE IF EXISTS bread_crumb_id_seq;
