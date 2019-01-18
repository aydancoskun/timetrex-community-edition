CREATE TABLE legal_entity (
  id integer NOT NULL AUTO_INCREMENT,
  company_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  classification_code varchar(250),
  legal_name varchar(250) NOT NULL,
  trade_name varchar(250) NOT NULL,
  address1 varchar(250),
  address2 varchar(250),
  country varchar(250),
  city varchar(250),
  province varchar(250),
  postal_code varchar(250),
  work_phone varchar(250),
  fax_phone varchar(250),
  start_date integer DEFAULT NULL,
  end_date integer DEFAULT NULL,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE payroll_remittance_agency (
  id integer NOT NULL AUTO_INCREMENT,
  legal_entity_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  name varchar(250) NOT NULL,
  description text,
  country varchar(250),
  province varchar(250),
  district varchar(250),
  agency_id varchar(250) NOT NULL,
  primary_identification varchar(250),
  secondary_identification varchar(250),
  tertiary_identification varchar(250),
  user_name varchar(250),
  password varchar(250),
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
  deleted smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE payroll_remittance_agency_event (
  id integer NOT NULL AUTO_INCREMENT,
  payroll_remittance_agency_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id VARCHAR(250) NOT NULL,
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
  note text DEFAULT NULL,
  last_due_date timestamp,
  due_date timestamp,
  start_date timestamp,
  end_date timestamp,
  next_reminder_date timestamp,
  last_reminder_date timestamp,
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE remittance_source_account (
  id integer NOT NULL AUTO_INCREMENT,
  legal_entity_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  country varchar(250),
  name varchar(250) NOT NULL,
  description text,
  data_format_id integer NOT NULL,
  currency_id integer NOT NULL,
  last_transaction_number varchar(250),
  value1 varchar(250),
  value2 varchar(250),
  value3 varchar(250),
  value4 varchar(250),
  value5 varchar(250),
  value6 varchar(250),
  value7 varchar(250),
  value8 varchar(250),
  value9 varchar(250),
  value10 varchar(250),
  value11 varchar(250),
  value12 varchar(250),
  value13 varchar(250),
  value14 varchar(250),
  value15 varchar(250),
  value16 varchar(250),
  value17 varchar(250),
  value18 varchar(250),
  value19 varchar(250),
  value20 varchar(250),
  value21 varchar(250),
  value22 varchar(250),
  value23 varchar(250),
  value24 varchar(250),
  value25 varchar(250),
  value26 varchar(250),
  value27 varchar(250),
  value28 varchar(250),
  value29 varchar(250),
  value30 varchar(250),
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE remittance_destination_account (
  id integer NOT NULL AUTO_INCREMENT,
  remittance_source_account_id integer NOT NULL,
  user_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  name varchar(250) NOT NULL,
  description text,
  currency_id integer NOT NULL,
  priority integer NOT NULL,
  amount_type_id integer NOT NULL,
  amount numeric(18,4) NOT NULL DEFAULT 0,
  percent_amount numeric(18,4) NOT NULL DEFAULT 0,
  value1 varchar(250),
  value2 varchar(250),
  value3 varchar(250),
  value4 varchar(250),
  value5 varchar(250),
  value6 varchar(250),
  value7 varchar(250),
  value8 varchar(250),
  value9 varchar(250),
  value10 varchar(250),
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB;

CREATE TABLE pay_stub_transaction (
  id integer NOT NULL AUTO_INCREMENT,
  parent_id integer NOT NULL DEFAULT 0,
  pay_stub_id integer NOT NULL,
  remittance_source_account_id integer NOT NULL,
  remittance_destination_account_id integer NOT NULL,
  currency_id integer NOT NULL,
  status_id integer NOT NULL,
  type_id integer NOT NULL,
  currency_rate numeric(18,10) NOT NULL DEFAULT 1,
  amount numeric(18,4) NOT NULL DEFAULT 0,
  transaction_date timestamp,
  confirmation_number varchar(250),
  note varchar(250),
  created_date integer,
  created_by integer,
  updated_date integer,
  updated_by integer,
  deleted_date integer,
  deleted_by integer,
  deleted smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY(id)
) ENGINE=InnoDB;



--DEFAULT 0 must be on the below three tables, so we can properly upgrade the DB for existing customers;
ALTER TABLE company_deduction ADD COLUMN legal_entity_id integer DEFAULT 0;
ALTER TABLE company_deduction ADD COLUMN payroll_remittance_agency_id integer DEFAULT 0;
ALTER TABLE users ADD COLUMN legal_entity_id integer DEFAULT 0;
ALTER TABLE user_default ADD COLUMN legal_entity_id integer DEFAULT 0;


CREATE UNIQUE INDEX legal_entity_id ON legal_entity(id);
CREATE INDEX legal_entity_company_id ON legal_entity(company_id);

CREATE UNIQUE INDEX payroll_remittance_agency_id ON payroll_remittance_agency(id);
CREATE INDEX payroll_remittance_agency_legal_entity_id ON payroll_remittance_agency(legal_entity_id);

CREATE UNIQUE INDEX remittance_source_account_id ON remittance_source_account(id);
CREATE INDEX remittance_source_account_legal_entity_id ON remittance_source_account(legal_entity_id);

CREATE UNIQUE INDEX remittance_destination_account_id ON remittance_destination_account(id);
CREATE INDEX remittance_destination_account_user_id ON remittance_destination_account(user_id);
CREATE INDEX remittance_destination_account_remittance_source_account_id ON remittance_destination_account(remittance_source_account_id);

CREATE UNIQUE INDEX pay_stub_transaction_id ON pay_stub_transaction(id);
CREATE INDEX pay_stub_transaction_pay_stub_id ON pay_stub_transaction(pay_stub_id);
CREATE INDEX pay_stub_transaction_remittance_source_account_id ON pay_stub_transaction(remittance_source_account_id);

CREATE UNIQUE INDEX payroll_remittance_agency_event_id ON payroll_remittance_agency_event(id);

DROP TABLE IF EXISTS user_generic_status_batch_id_seq;
DROP TABLE IF EXISTS user_hierarchy_seperator_id_seq;

DROP TABLE IF EXISTS user_pay_period_total;
DROP TABLE IF EXISTS user_pay_period_total_id_seq;
DROP TABLE bread_crumb;
DROP TABLE IF EXISTS bread_crumb_id_seq;