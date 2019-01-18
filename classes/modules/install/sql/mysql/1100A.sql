--Fix invalid time default values that prevent altering the table later on;
SET SQL_MODE='ALLOW_INVALID_DATES';
ALTER TABLE `recurring_schedule` CHANGE `start_time` `start_time` timestamp DEFAULT '1970-01-01 00:00:01',
  CHANGE `end_time` `end_time` timestamp DEFAULT '1970-01-01 00:00:01';
ALTER TABLE `schedule` CHANGE `start_time` `start_time` timestamp DEFAULT '1970-01-01 00:00:01',
  CHANGE `end_time` `end_time` timestamp DEFAULT '1970-01-01 00:00:01';
SET SQL_MODE='ANSI';

ALTER TABLE `absence_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `absence_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `absence_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_accrual_policy_id` char(36) AFTER `accrual_policy_id`;
UPDATE `absence_policy` set `new_accrual_policy_id` = CASE WHEN ( `accrual_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `accrual_policy_id`,
  CHANGE `new_accrual_policy_id` `accrual_policy_id` char(36),
  ADD COLUMN `new_premium_policy_id` char(36) AFTER `premium_policy_id`;
UPDATE `absence_policy` set `new_premium_policy_id` = CASE WHEN ( `premium_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `premium_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`premium_policy_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `premium_policy_id`,
  CHANGE `new_premium_policy_id` `premium_policy_id` char(36),
  ADD COLUMN `new_pay_stub_entry_account_id` char(36) AFTER `pay_stub_entry_account_id`;
UPDATE `absence_policy` set `new_pay_stub_entry_account_id` = CASE WHEN ( `pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `pay_stub_entry_account_id`,
  CHANGE `new_pay_stub_entry_account_id` `pay_stub_entry_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `absence_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `absence_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `absence_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_wage_group_id` char(36) AFTER `wage_group_id`;
UPDATE `absence_policy` set `new_wage_group_id` = CASE WHEN ( `wage_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `wage_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`wage_group_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `wage_group_id`,
  CHANGE `new_wage_group_id` `wage_group_id` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `absence_policy` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `absence_policy` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `absence_policy` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36);

ALTER TABLE `accrual` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `accrual` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `accrual` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_accrual_policy_account_id` char(36) AFTER `accrual_policy_account_id`;
UPDATE `accrual` set `new_accrual_policy_account_id` = CASE WHEN ( `accrual_policy_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_account_id`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `accrual_policy_account_id`,
  CHANGE `new_accrual_policy_account_id` `accrual_policy_account_id` char(36),
  ADD COLUMN `new_user_date_total_id` char(36) AFTER `user_date_total_id`;
UPDATE `accrual` set `new_user_date_total_id` = CASE WHEN ( `user_date_total_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_date_total_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_date_total_id`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `user_date_total_id`,
  CHANGE `new_user_date_total_id` `user_date_total_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `accrual` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `accrual` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `accrual` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_accrual_policy_id` char(36) AFTER `accrual_policy_id`;
UPDATE `accrual` set `new_accrual_policy_id` = CASE WHEN ( `accrual_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_id`, 12, '0') ) END END;
ALTER TABLE `accrual` DROP COLUMN `accrual_policy_id`,
  CHANGE `new_accrual_policy_id` `accrual_policy_id` char(36);

ALTER TABLE `accrual_balance` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `accrual_balance` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `accrual_balance` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `accrual_balance` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `accrual_balance` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_accrual_policy_account_id` char(36) AFTER `accrual_policy_account_id`;
UPDATE `accrual_balance` set `new_accrual_policy_account_id` = CASE WHEN ( `accrual_policy_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_account_id`, 12, '0') ) END END;
ALTER TABLE `accrual_balance` DROP COLUMN `accrual_policy_account_id`,
  CHANGE `new_accrual_policy_account_id` `accrual_policy_account_id` char(36);

ALTER TABLE `accrual_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `accrual_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `accrual_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `accrual_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `accrual_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `accrual_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_contributing_shift_policy_id` char(36) AFTER `contributing_shift_policy_id`;
UPDATE `accrual_policy` set `new_contributing_shift_policy_id` = CASE WHEN ( `contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `contributing_shift_policy_id`,
  CHANGE `new_contributing_shift_policy_id` `contributing_shift_policy_id` char(36),
  ADD COLUMN `new_length_of_service_contributing_pay_code_policy_id` char(36) AFTER `length_of_service_contributing_pay_code_policy_id`;
UPDATE `accrual_policy` set `new_length_of_service_contributing_pay_code_policy_id` = CASE WHEN ( `length_of_service_contributing_pay_code_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `length_of_service_contributing_pay_code_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`length_of_service_contributing_pay_code_policy_id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `length_of_service_contributing_pay_code_policy_id`,
  CHANGE `new_length_of_service_contributing_pay_code_policy_id` `length_of_service_contributing_pay_code_policy_id` char(36),
  ADD COLUMN `new_accrual_policy_account_id` char(36) AFTER `accrual_policy_account_id`;
UPDATE `accrual_policy` set `new_accrual_policy_account_id` = CASE WHEN ( `accrual_policy_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_account_id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy` DROP COLUMN `accrual_policy_account_id`,
  CHANGE `new_accrual_policy_account_id` `accrual_policy_account_id` char(36);

ALTER TABLE `accrual_policy_account` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `accrual_policy_account` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_account` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `accrual_policy_account` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_account` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `accrual_policy_account` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_account` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `accrual_policy_account` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_account` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `accrual_policy_account` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_account` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `accrual_policy_milestone` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `accrual_policy_milestone` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_milestone` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_accrual_policy_id` char(36) AFTER `accrual_policy_id`;
UPDATE `accrual_policy_milestone` set `new_accrual_policy_id` = CASE WHEN ( `accrual_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_id`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_milestone` DROP COLUMN `accrual_policy_id`,
  CHANGE `new_accrual_policy_id` `accrual_policy_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `accrual_policy_milestone` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_milestone` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `accrual_policy_milestone` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_milestone` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `accrual_policy_milestone` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `accrual_policy_milestone` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `authentication` ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `authentication` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `authentication` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36);

ALTER TABLE `authorizations` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `authorizations` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `authorizations` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `authorizations` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `authorizations` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `authorizations` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `authorizations` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `authorizations` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `authorizations` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `authorizations` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `authorizations` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `bank_account` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `bank_account` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `bank_account` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `bank_account` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `bank_account` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `bank_account` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `bank_account` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `bank_account` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `bank_account` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `bank_account` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `bank_account` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `bank_account` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `bank_account` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `branch` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `branch` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `branch` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `branch` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `branch` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `branch` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `branch` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `branch` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `branch` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `branch` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `branch` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `break_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `break_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `break_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `break_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `break_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `break_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `break_policy` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `break_policy` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `break_policy` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `break_policy` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `break_policy` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `break_policy` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `break_policy` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36);

ALTER TABLE `company` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `company` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `company` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `company` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `company` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `company_deduction` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_deduction` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `company_deduction` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_pay_stub_entry_account_id` char(36) AFTER `pay_stub_entry_account_id`;
UPDATE `company_deduction` set `new_pay_stub_entry_account_id` = CASE WHEN ( `pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `pay_stub_entry_account_id`,
  CHANGE `new_pay_stub_entry_account_id` `pay_stub_entry_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `company_deduction` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `company_deduction` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `company_deduction` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_length_of_service_contributing_pay_code_policy_id` char(36) AFTER `length_of_service_contributing_pay_code_policy_id`;
UPDATE `company_deduction` set `new_length_of_service_contributing_pay_code_policy_id` = CASE WHEN ( `length_of_service_contributing_pay_code_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `length_of_service_contributing_pay_code_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`length_of_service_contributing_pay_code_policy_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `length_of_service_contributing_pay_code_policy_id`,
  CHANGE `new_length_of_service_contributing_pay_code_policy_id` `length_of_service_contributing_pay_code_policy_id` char(36),
  ADD COLUMN `new_payroll_remittance_agency_id` char(36) AFTER `payroll_remittance_agency_id`;
UPDATE `company_deduction` set `new_payroll_remittance_agency_id` = CASE WHEN ( `payroll_remittance_agency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `payroll_remittance_agency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`payroll_remittance_agency_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `payroll_remittance_agency_id`,
  CHANGE `new_payroll_remittance_agency_id` `payroll_remittance_agency_id` char(36),
  ADD COLUMN `new_legal_entity_id` char(36) AFTER `legal_entity_id`;
UPDATE `company_deduction` set `new_legal_entity_id` = CASE WHEN ( `legal_entity_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `legal_entity_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`legal_entity_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction` DROP COLUMN `legal_entity_id`,
  CHANGE `new_legal_entity_id` `legal_entity_id` char(36);

ALTER TABLE `company_deduction_pay_stub_entry_account` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_deduction_pay_stub_entry_account` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_deduction_pay_stub_entry_account` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_deduction_id` char(36) AFTER `company_deduction_id`;
UPDATE `company_deduction_pay_stub_entry_account` set `new_company_deduction_id` = CASE WHEN ( `company_deduction_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_deduction_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_deduction_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction_pay_stub_entry_account` DROP COLUMN `company_deduction_id`,
  CHANGE `new_company_deduction_id` `company_deduction_id` char(36),
  ADD COLUMN `new_pay_stub_entry_account_id` char(36) AFTER `pay_stub_entry_account_id`;
UPDATE `company_deduction_pay_stub_entry_account` set `new_pay_stub_entry_account_id` = CASE WHEN ( `pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `company_deduction_pay_stub_entry_account` DROP COLUMN `pay_stub_entry_account_id`,
  CHANGE `new_pay_stub_entry_account_id` `pay_stub_entry_account_id` char(36);

ALTER TABLE `company_generic_map` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_generic_map` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_generic_map` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `company_generic_map` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `company_generic_map` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `company_generic_map` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `company_generic_map` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36),
  ADD COLUMN `new_map_id` char(36) AFTER `map_id`;
UPDATE `company_generic_map` set `new_map_id` = CASE WHEN ( `map_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `map_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`map_id`, 12, '0') ) END END;
ALTER TABLE `company_generic_map` DROP COLUMN `map_id`,
  CHANGE `new_map_id` `map_id` char(36);

ALTER TABLE `company_generic_tag` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_generic_tag` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `company_generic_tag` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `company_generic_tag` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `company_generic_tag` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `company_generic_tag` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `company_generic_tag_map` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_generic_tag_map` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag_map` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `company_generic_tag_map` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag_map` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36),
  ADD COLUMN `new_tag_id` char(36) AFTER `tag_id`;
UPDATE `company_generic_tag_map` set `new_tag_id` = CASE WHEN ( `tag_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `tag_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`tag_id`, 12, '0') ) END END;
ALTER TABLE `company_generic_tag_map` DROP COLUMN `tag_id`,
  CHANGE `new_tag_id` `tag_id` char(36);

ALTER TABLE `company_setting` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_setting` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_setting` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `company_setting` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `company_setting` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `company_setting` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `company_setting` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `company_setting` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `company_setting` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `company_setting` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `company_setting` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `company_user_count` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `company_user_count` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `company_user_count` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `company_user_count` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `company_user_count` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36);

ALTER TABLE `contributing_pay_code_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `contributing_pay_code_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `contributing_pay_code_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `contributing_pay_code_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `contributing_pay_code_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `contributing_pay_code_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `contributing_pay_code_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `contributing_pay_code_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `contributing_pay_code_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `contributing_pay_code_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `contributing_pay_code_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `contributing_shift_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `contributing_shift_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `contributing_shift_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `contributing_shift_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `contributing_shift_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_contributing_pay_code_policy_id` char(36) AFTER `contributing_pay_code_policy_id`;
UPDATE `contributing_shift_policy` set `new_contributing_pay_code_policy_id` = CASE WHEN ( `contributing_pay_code_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contributing_pay_code_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contributing_pay_code_policy_id`, 12, '0') ) END END;
ALTER TABLE `contributing_shift_policy` DROP COLUMN `contributing_pay_code_policy_id`,
  CHANGE `new_contributing_pay_code_policy_id` `contributing_pay_code_policy_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `contributing_shift_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `contributing_shift_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `contributing_shift_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `contributing_shift_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `contributing_shift_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `contributing_shift_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `cron` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `cron` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `cron` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `cron` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `cron` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `cron` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `cron` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `cron` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `cron` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `currency` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `currency` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `currency` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `currency` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `currency` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `currency` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `currency` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `currency` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `currency` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `currency` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `currency` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `currency_rate` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `currency_rate` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `currency_rate` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `currency_rate` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `currency_rate` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `currency_rate` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `currency_rate` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `currency_rate` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `currency_rate` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `currency_rate` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `currency_rate` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `department` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `department` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `department` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `department` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `department` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `department` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `department` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `department` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `department` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `department` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `department` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `department_branch` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `department_branch` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `department_branch` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `department_branch` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `department_branch` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `department_branch` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `department_branch` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36);

ALTER TABLE `department_branch_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `department_branch_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `department_branch_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_department_branch_id` char(36) AFTER `department_branch_id`;
UPDATE `department_branch_user` set `new_department_branch_id` = CASE WHEN ( `department_branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_branch_id`, 12, '0') ) END END;
ALTER TABLE `department_branch_user` DROP COLUMN `department_branch_id`,
  CHANGE `new_department_branch_id` `department_branch_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `department_branch_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `department_branch_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `ethnic_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `ethnic_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `ethnic_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `ethnic_group` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `ethnic_group` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `ethnic_group` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `ethnic_group` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `ethnic_group` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `ethnic_group` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `ethnic_group` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `ethnic_group` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `exception` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `exception` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `exception` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `exception` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_exception_policy_id` char(36) AFTER `exception_policy_id`;
UPDATE `exception` set `new_exception_policy_id` = CASE WHEN ( `exception_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `exception_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`exception_policy_id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `exception_policy_id`,
  CHANGE `new_exception_policy_id` `exception_policy_id` char(36),
  ADD COLUMN `new_punch_id` char(36) AFTER `punch_id`;
UPDATE `exception` set `new_punch_id` = CASE WHEN ( `punch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `punch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`punch_id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `punch_id`,
  CHANGE `new_punch_id` `punch_id` char(36),
  ADD COLUMN `new_punch_control_id` char(36) AFTER `punch_control_id`;
UPDATE `exception` set `new_punch_control_id` = CASE WHEN ( `punch_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `punch_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`punch_control_id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `punch_control_id`,
  CHANGE `new_punch_control_id` `punch_control_id` char(36),
  ADD COLUMN `new_acknowledged_reason_id` char(36) AFTER `acknowledged_reason_id`;
UPDATE `exception` set `new_acknowledged_reason_id` = CASE WHEN ( `acknowledged_reason_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `acknowledged_reason_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`acknowledged_reason_id`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `acknowledged_reason_id`,
  CHANGE `new_acknowledged_reason_id` `acknowledged_reason_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `exception` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `exception` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `exception` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `exception` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `exception_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `exception_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `exception_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_exception_policy_control_id` char(36) AFTER `exception_policy_control_id`;
UPDATE `exception_policy` set `new_exception_policy_control_id` = CASE WHEN ( `exception_policy_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `exception_policy_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`exception_policy_control_id`, 12, '0') ) END END;
ALTER TABLE `exception_policy` DROP COLUMN `exception_policy_control_id`,
  CHANGE `new_exception_policy_control_id` `exception_policy_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `exception_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `exception_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `exception_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `exception_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `exception_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `exception_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `exception_policy_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `exception_policy_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `exception_policy_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `exception_policy_control` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `exception_policy_control` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `exception_policy_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `exception_policy_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `exception_policy_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `exception_policy_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `exception_policy_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `exception_policy_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `help` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `help` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `help` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `help` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `help` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `help` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `help` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `help` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `help` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `help_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `help_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `help_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_help_group_control_id` char(36) AFTER `help_group_control_id`;
UPDATE `help_group` set `new_help_group_control_id` = CASE WHEN ( `help_group_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `help_group_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`help_group_control_id`, 12, '0') ) END END;
ALTER TABLE `help_group` DROP COLUMN `help_group_control_id`,
  CHANGE `new_help_group_control_id` `help_group_control_id` char(36),
  ADD COLUMN `new_help_id` char(36) AFTER `help_id`;
UPDATE `help_group` set `new_help_id` = CASE WHEN ( `help_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `help_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`help_id`, 12, '0') ) END END;
ALTER TABLE `help_group` DROP COLUMN `help_id`,
  CHANGE `new_help_id` `help_id` char(36);

ALTER TABLE `help_group_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `help_group_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `help_group_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `help_group_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `help_group_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `help_group_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `help_group_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `help_group_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `help_group_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `hierarchy_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `hierarchy_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `hierarchy_control` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_control` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `hierarchy_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `hierarchy_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `hierarchy_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `hierarchy_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `hierarchy_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `hierarchy_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `hierarchy_level` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `hierarchy_level` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_level` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_hierarchy_control_id` char(36) AFTER `hierarchy_control_id`;
UPDATE `hierarchy_level` set `new_hierarchy_control_id` = CASE WHEN ( `hierarchy_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `hierarchy_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`hierarchy_control_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_level` DROP COLUMN `hierarchy_control_id`,
  CHANGE `new_hierarchy_control_id` `hierarchy_control_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `hierarchy_level` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_level` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `hierarchy_level` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `hierarchy_level` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `hierarchy_level` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `hierarchy_level` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `hierarchy_level` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `hierarchy_level` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `hierarchy_object_type` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `hierarchy_object_type` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_object_type` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_hierarchy_control_id` char(36) AFTER `hierarchy_control_id`;
UPDATE `hierarchy_object_type` set `new_hierarchy_control_id` = CASE WHEN ( `hierarchy_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `hierarchy_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`hierarchy_control_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_object_type` DROP COLUMN `hierarchy_control_id`,
  CHANGE `new_hierarchy_control_id` `hierarchy_control_id` char(36);

ALTER TABLE `hierarchy_share` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `hierarchy_share` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_share` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_hierarchy_control_id` char(36) AFTER `hierarchy_control_id`;
UPDATE `hierarchy_share` set `new_hierarchy_control_id` = CASE WHEN ( `hierarchy_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `hierarchy_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`hierarchy_control_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_share` DROP COLUMN `hierarchy_control_id`,
  CHANGE `new_hierarchy_control_id` `hierarchy_control_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `hierarchy_share` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_share` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `hierarchy_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `hierarchy_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_user` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_hierarchy_control_id` char(36) AFTER `hierarchy_control_id`;
UPDATE `hierarchy_user` set `new_hierarchy_control_id` = CASE WHEN ( `hierarchy_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `hierarchy_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`hierarchy_control_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_user` DROP COLUMN `hierarchy_control_id`,
  CHANGE `new_hierarchy_control_id` `hierarchy_control_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `hierarchy_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `hierarchy_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `holiday_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `holiday_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `holiday_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_absence_policy_id` char(36) AFTER `absence_policy_id`;
UPDATE `holiday_policy` set `new_absence_policy_id` = CASE WHEN ( `absence_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `absence_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`absence_policy_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `absence_policy_id`,
  CHANGE `new_absence_policy_id` `absence_policy_id` char(36),
  ADD COLUMN `new_round_interval_policy_id` char(36) AFTER `round_interval_policy_id`;
UPDATE `holiday_policy` set `new_round_interval_policy_id` = CASE WHEN ( `round_interval_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `round_interval_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`round_interval_policy_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `round_interval_policy_id`,
  CHANGE `new_round_interval_policy_id` `round_interval_policy_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `holiday_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `holiday_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `holiday_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_contributing_shift_policy_id` char(36) AFTER `contributing_shift_policy_id`;
UPDATE `holiday_policy` set `new_contributing_shift_policy_id` = CASE WHEN ( `contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `contributing_shift_policy_id`,
  CHANGE `new_contributing_shift_policy_id` `contributing_shift_policy_id` char(36),
  ADD COLUMN `new_eligible_contributing_shift_policy_id` char(36) AFTER `eligible_contributing_shift_policy_id`;
UPDATE `holiday_policy` set `new_eligible_contributing_shift_policy_id` = CASE WHEN ( `eligible_contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `eligible_contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`eligible_contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy` DROP COLUMN `eligible_contributing_shift_policy_id`,
  CHANGE `new_eligible_contributing_shift_policy_id` `eligible_contributing_shift_policy_id` char(36);

ALTER TABLE `holiday_policy_recurring_holiday` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `holiday_policy_recurring_holiday` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy_recurring_holiday` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_holiday_policy_id` char(36) AFTER `holiday_policy_id`;
UPDATE `holiday_policy_recurring_holiday` set `new_holiday_policy_id` = CASE WHEN ( `holiday_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `holiday_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`holiday_policy_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy_recurring_holiday` DROP COLUMN `holiday_policy_id`,
  CHANGE `new_holiday_policy_id` `holiday_policy_id` char(36),
  ADD COLUMN `new_recurring_holiday_id` char(36) AFTER `recurring_holiday_id`;
UPDATE `holiday_policy_recurring_holiday` set `new_recurring_holiday_id` = CASE WHEN ( `recurring_holiday_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_holiday_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_holiday_id`, 12, '0') ) END END;
ALTER TABLE `holiday_policy_recurring_holiday` DROP COLUMN `recurring_holiday_id`,
  CHANGE `new_recurring_holiday_id` `recurring_holiday_id` char(36);

ALTER TABLE `holidays` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `holidays` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `holidays` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_holiday_policy_id` char(36) AFTER `holiday_policy_id`;
UPDATE `holidays` set `new_holiday_policy_id` = CASE WHEN ( `holiday_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `holiday_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`holiday_policy_id`, 12, '0') ) END END;
ALTER TABLE `holidays` DROP COLUMN `holiday_policy_id`,
  CHANGE `new_holiday_policy_id` `holiday_policy_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `holidays` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `holidays` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `holidays` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `holidays` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `holidays` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `holidays` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `kpi` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `kpi` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `kpi` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `kpi` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `kpi` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `kpi` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `kpi` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `kpi` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `kpi` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `kpi` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `kpi` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `kpi_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `kpi_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `kpi_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `kpi_group` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `kpi_group` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `kpi_group` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `kpi_group` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `kpi_group` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `kpi_group` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `kpi_group` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `kpi_group` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `kpi_group` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `kpi_group` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36);

ALTER TABLE `legal_entity` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `legal_entity` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `legal_entity` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `legal_entity` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `legal_entity` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `legal_entity` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `legal_entity` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `legal_entity` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `legal_entity` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `legal_entity` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `legal_entity` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `meal_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `meal_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `meal_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `meal_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `meal_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `meal_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `meal_policy` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `meal_policy` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `meal_policy` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `meal_policy` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `meal_policy` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `meal_policy` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `meal_policy` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36);

ALTER TABLE `message` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `message` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `message` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `message` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `message` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36),
  ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `message` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `message` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `message` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `message` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `message` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `message` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `message` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `message` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `message_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `message_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `message_control` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `message_control` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `message_control` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `message_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `message_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `message_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `message_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `message_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `message_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `message_recipient` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `message_recipient` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `message_recipient` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `message_recipient` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `message_recipient` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_message_sender_id` char(36) AFTER `message_sender_id`;
UPDATE `message_recipient` set `new_message_sender_id` = CASE WHEN ( `message_sender_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `message_sender_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`message_sender_id`, 12, '0') ) END END;
ALTER TABLE `message_recipient` DROP COLUMN `message_sender_id`,
  CHANGE `new_message_sender_id` `message_sender_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `message_recipient` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `message_recipient` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `message_recipient` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `message_recipient` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `message_recipient` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `message_recipient` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `message_sender` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `message_sender` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `message_sender` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `message_sender` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36),
  ADD COLUMN `new_message_control_id` char(36) AFTER `message_control_id`;
UPDATE `message_sender` set `new_message_control_id` = CASE WHEN ( `message_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `message_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`message_control_id`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `message_control_id`,
  CHANGE `new_message_control_id` `message_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `message_sender` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `message_sender` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `message_sender` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `message_sender` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `other_field` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `other_field` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `other_field` DROP COLUMN `id`, DROP PRIMARY KEY;
ALTER TABLE `other_field` CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id);
ALTER TABLE `other_field` ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `other_field` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `other_field` DROP COLUMN `company_id`;
ALTER TABLE `other_field` CHANGE `new_company_id` `company_id` char(36);
ALTER TABLE `other_field` ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `other_field` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `other_field` DROP COLUMN `created_by`;
ALTER TABLE `other_field` CHANGE `new_created_by` `created_by` char(36);
ALTER TABLE `other_field` ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `other_field` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `other_field` DROP COLUMN `updated_by`;
ALTER TABLE `other_field` CHANGE `new_updated_by` `updated_by` char(36);
ALTER TABLE `other_field` ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `other_field` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `other_field` DROP COLUMN `deleted_by`;
ALTER TABLE `other_field` CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `over_time_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `over_time_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `over_time_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_accrual_policy_id` char(36) AFTER `accrual_policy_id`;
UPDATE `over_time_policy` set `new_accrual_policy_id` = CASE WHEN ( `accrual_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `accrual_policy_id`,
  CHANGE `new_accrual_policy_id` `accrual_policy_id` char(36),
  ADD COLUMN `new_pay_stub_entry_account_id` char(36) AFTER `pay_stub_entry_account_id`;
UPDATE `over_time_policy` set `new_pay_stub_entry_account_id` = CASE WHEN ( `pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `pay_stub_entry_account_id`,
  CHANGE `new_pay_stub_entry_account_id` `pay_stub_entry_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `over_time_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `over_time_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `over_time_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_wage_group_id` char(36) AFTER `wage_group_id`;
UPDATE `over_time_policy` set `new_wage_group_id` = CASE WHEN ( `wage_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `wage_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`wage_group_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `wage_group_id`,
  CHANGE `new_wage_group_id` `wage_group_id` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `over_time_policy` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `over_time_policy` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36),
  ADD COLUMN `new_contributing_shift_policy_id` char(36) AFTER `contributing_shift_policy_id`;
UPDATE `over_time_policy` set `new_contributing_shift_policy_id` = CASE WHEN ( `contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `contributing_shift_policy_id`,
  CHANGE `new_contributing_shift_policy_id` `contributing_shift_policy_id` char(36),
  ADD COLUMN `new_trigger_time_adjust_contributing_shift_policy_id` char(36) AFTER `trigger_time_adjust_contributing_shift_policy_id`;
UPDATE `over_time_policy` set `new_trigger_time_adjust_contributing_shift_policy_id` = CASE WHEN ( `trigger_time_adjust_contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `trigger_time_adjust_contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`trigger_time_adjust_contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `over_time_policy` DROP COLUMN `trigger_time_adjust_contributing_shift_policy_id`,
  CHANGE `new_trigger_time_adjust_contributing_shift_policy_id` `trigger_time_adjust_contributing_shift_policy_id` char(36);

ALTER TABLE `pay_code` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_code` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `pay_code` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `pay_code` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36),
  ADD COLUMN `new_pay_stub_entry_account_id` char(36) AFTER `pay_stub_entry_account_id`;
UPDATE `pay_code` set `new_pay_stub_entry_account_id` = CASE WHEN ( `pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `pay_stub_entry_account_id`,
  CHANGE `new_pay_stub_entry_account_id` `pay_stub_entry_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_code` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_code` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_code` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_code` DROP COLUMN `deleted_by`,
CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_formula_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_formula_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `pay_formula_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_wage_source_contributing_shift_policy_id` char(36) AFTER `wage_source_contributing_shift_policy_id`;
UPDATE `pay_formula_policy` set `new_wage_source_contributing_shift_policy_id` = CASE WHEN ( `wage_source_contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `wage_source_contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`wage_source_contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `wage_source_contributing_shift_policy_id`,
  CHANGE `new_wage_source_contributing_shift_policy_id` `wage_source_contributing_shift_policy_id` char(36),
  ADD COLUMN `new_time_source_contributing_shift_policy_id` char(36) AFTER `time_source_contributing_shift_policy_id`;
UPDATE `pay_formula_policy` set `new_time_source_contributing_shift_policy_id` = CASE WHEN ( `time_source_contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `time_source_contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`time_source_contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `time_source_contributing_shift_policy_id`,
  CHANGE `new_time_source_contributing_shift_policy_id` `time_source_contributing_shift_policy_id` char(36),
  ADD COLUMN `new_wage_group_id` char(36) AFTER `wage_group_id`;
UPDATE `pay_formula_policy` set `new_wage_group_id` = CASE WHEN ( `wage_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `wage_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`wage_group_id`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `wage_group_id`,
  CHANGE `new_wage_group_id` `wage_group_id` char(36),
  ADD COLUMN `new_accrual_policy_account_id` char(36) AFTER `accrual_policy_account_id`;
UPDATE `pay_formula_policy` set `new_accrual_policy_account_id` = CASE WHEN ( `accrual_policy_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_account_id`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `accrual_policy_account_id`,
  CHANGE `new_accrual_policy_account_id` `accrual_policy_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_formula_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_formula_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_formula_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_formula_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_period` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_period` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `pay_period` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_pay_period_schedule_id` char(36) AFTER `pay_period_schedule_id`;
UPDATE `pay_period` set `new_pay_period_schedule_id` = CASE WHEN ( `pay_period_schedule_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_schedule_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_schedule_id`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `pay_period_schedule_id`,
  CHANGE `new_pay_period_schedule_id` `pay_period_schedule_id` char(36),
  ADD COLUMN `new_tainted_by` char(36) AFTER `tainted_by`;
UPDATE `pay_period` set `new_tainted_by` = CASE WHEN ( `tainted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `tainted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`tainted_by`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `tainted_by`,
  CHANGE `new_tainted_by` `tainted_by` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_period` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_period` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_period` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_period` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_period_schedule` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_period_schedule` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `pay_period_schedule` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_period_schedule` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_period_schedule` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_period_schedule` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_period_schedule_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_period_schedule_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_pay_period_schedule_id` char(36) AFTER `pay_period_schedule_id`;
UPDATE `pay_period_schedule_user` set `new_pay_period_schedule_id` = CASE WHEN ( `pay_period_schedule_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_schedule_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_schedule_id`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule_user` DROP COLUMN `pay_period_schedule_id`,
  CHANGE `new_pay_period_schedule_id` `pay_period_schedule_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `pay_period_schedule_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `pay_period_schedule_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `pay_period_time_sheet_verify` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_period_time_sheet_verify` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_period_time_sheet_verify` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `pay_period_time_sheet_verify` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `pay_period_time_sheet_verify` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `pay_period_time_sheet_verify` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `pay_period_time_sheet_verify` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_period_time_sheet_verify` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_period_time_sheet_verify` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_period_time_sheet_verify` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_period_time_sheet_verify` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_period_time_sheet_verify` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_period_time_sheet_verify` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_stub` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_stub` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `pay_stub` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `pay_stub` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_status_by` char(36) AFTER `status_by`;
UPDATE `pay_stub` set `new_status_by` = CASE WHEN ( `status_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `status_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`status_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `status_by`,
  CHANGE `new_status_by` `status_by` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_stub` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_stub` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_stub` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `pay_stub` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36);

ALTER TABLE `pay_stub_amendment` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_stub_amendment` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `pay_stub_amendment` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_stub_entry_name_id` char(36) AFTER `pay_stub_entry_name_id`;
UPDATE `pay_stub_amendment` set `new_pay_stub_entry_name_id` = CASE WHEN ( `pay_stub_entry_name_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_name_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_name_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `pay_stub_entry_name_id`,
  CHANGE `new_pay_stub_entry_name_id` `pay_stub_entry_name_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_stub_amendment` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_stub_amendment` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_stub_amendment` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_recurring_ps_amendment_id` char(36) AFTER `recurring_ps_amendment_id`;
UPDATE `pay_stub_amendment` set `new_recurring_ps_amendment_id` = CASE WHEN ( `recurring_ps_amendment_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_ps_amendment_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_ps_amendment_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `recurring_ps_amendment_id`,
  CHANGE `new_recurring_ps_amendment_id` `recurring_ps_amendment_id` char(36),
  ADD COLUMN `new_percent_amount_entry_name_id` char(36) AFTER `percent_amount_entry_name_id`;
UPDATE `pay_stub_amendment` set `new_percent_amount_entry_name_id` = CASE WHEN ( `percent_amount_entry_name_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `percent_amount_entry_name_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`percent_amount_entry_name_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_amendment` DROP COLUMN `percent_amount_entry_name_id`,
  CHANGE `new_percent_amount_entry_name_id` `percent_amount_entry_name_id` char(36);

ALTER TABLE `pay_stub_entry` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_stub_entry` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_pay_stub_id` char(36) AFTER `pay_stub_id`;
UPDATE `pay_stub_entry` set `new_pay_stub_id` = CASE WHEN ( `pay_stub_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `pay_stub_id`,
  CHANGE `new_pay_stub_id` `pay_stub_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_stub_entry` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_stub_entry` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_stub_entry` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_pay_stub_entry_name_id` char(36) AFTER `pay_stub_entry_name_id`;
UPDATE `pay_stub_entry` set `new_pay_stub_entry_name_id` = CASE WHEN ( `pay_stub_entry_name_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_name_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_name_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `pay_stub_entry_name_id`,
  CHANGE `new_pay_stub_entry_name_id` `pay_stub_entry_name_id` char(36),
  ADD COLUMN `new_pay_stub_amendment_id` char(36) AFTER `pay_stub_amendment_id`;
UPDATE `pay_stub_entry` set `new_pay_stub_amendment_id` = CASE WHEN ( `pay_stub_amendment_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_amendment_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_amendment_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `pay_stub_amendment_id`,
  CHANGE `new_pay_stub_amendment_id` `pay_stub_amendment_id` char(36),
  ADD COLUMN `new_user_expense_id` char(36) AFTER `user_expense_id`;
UPDATE `pay_stub_entry` set `new_user_expense_id` = CASE WHEN ( `user_expense_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_expense_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_expense_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry` DROP COLUMN `user_expense_id`,
  CHANGE `new_user_expense_id` `user_expense_id` char(36);

ALTER TABLE `pay_stub_entry_account` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_stub_entry_account` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `pay_stub_entry_account` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_accrual_pay_stub_entry_account_id` char(36) AFTER `accrual_pay_stub_entry_account_id`;
UPDATE `pay_stub_entry_account` set `new_accrual_pay_stub_entry_account_id` = CASE WHEN ( `accrual_pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account` DROP COLUMN `accrual_pay_stub_entry_account_id`,
  CHANGE `new_accrual_pay_stub_entry_account_id` `accrual_pay_stub_entry_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_stub_entry_account` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_stub_entry_account` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_stub_entry_account` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_stub_entry_account_link` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_stub_entry_account_link` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `pay_stub_entry_account_link` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_stub_entry_account_link` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_stub_entry_account_link` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_stub_entry_account_link` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `deleted_by`,CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_stub_transaction` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `pay_stub_transaction` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
    ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `pay_stub_transaction` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36),
  ADD COLUMN `new_pay_stub_id` char(36) AFTER `pay_stub_id`;
UPDATE `pay_stub_transaction` set `new_pay_stub_id` = CASE WHEN ( `pay_stub_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `pay_stub_id`,
  CHANGE `new_pay_stub_id` `pay_stub_id` char(36),
  ADD COLUMN `new_remittance_source_account_id` char(36) AFTER `remittance_source_account_id`;
UPDATE `pay_stub_transaction` set `new_remittance_source_account_id` = CASE WHEN ( `remittance_source_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `remittance_source_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`remittance_source_account_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `remittance_source_account_id`,
  CHANGE `new_remittance_source_account_id` `remittance_source_account_id` char(36),
  ADD COLUMN `new_remittance_destination_account_id` char(36) AFTER `remittance_destination_account_id`;
UPDATE `pay_stub_transaction` set `new_remittance_destination_account_id` = CASE WHEN ( `remittance_destination_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `remittance_destination_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`remittance_destination_account_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `remittance_destination_account_id`,
  CHANGE `new_remittance_destination_account_id` `remittance_destination_account_id` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `pay_stub_transaction` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `pay_stub_transaction` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `pay_stub_transaction` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `pay_stub_transaction` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `pay_stub_transaction` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `payroll_remittance_agency` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `payroll_remittance_agency` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_legal_entity_id` char(36) AFTER `legal_entity_id`;
UPDATE `payroll_remittance_agency` set `new_legal_entity_id` = CASE WHEN ( `legal_entity_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `legal_entity_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`legal_entity_id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `legal_entity_id`,
  CHANGE `new_legal_entity_id` `legal_entity_id` char(36),
  ADD COLUMN `new_contact_user_id` char(36) AFTER `contact_user_id`;
UPDATE `payroll_remittance_agency` set `new_contact_user_id` = CASE WHEN ( `contact_user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contact_user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contact_user_id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `contact_user_id`,
  CHANGE `new_contact_user_id` `contact_user_id` char(36),
  ADD COLUMN `new_remittance_source_account_id` char(36) AFTER `remittance_source_account_id`;
UPDATE `payroll_remittance_agency` set `new_remittance_source_account_id` = CASE WHEN ( `remittance_source_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `remittance_source_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`remittance_source_account_id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `remittance_source_account_id`,
  CHANGE `new_remittance_source_account_id` `remittance_source_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `payroll_remittance_agency` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `payroll_remittance_agency` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `payroll_remittance_agency` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `payroll_remittance_agency_event` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `payroll_remittance_agency_event` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_payroll_remittance_agency_id` char(36) AFTER `payroll_remittance_agency_id`;
UPDATE `payroll_remittance_agency_event` set `new_payroll_remittance_agency_id` = CASE WHEN ( `payroll_remittance_agency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `payroll_remittance_agency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`payroll_remittance_agency_id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `payroll_remittance_agency_id`,
  CHANGE `new_payroll_remittance_agency_id` `payroll_remittance_agency_id` char(36),
  ADD COLUMN `new_reminder_user_id` char(36) AFTER `reminder_user_id`;
UPDATE `payroll_remittance_agency_event` set `new_reminder_user_id` = CASE WHEN ( `reminder_user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `reminder_user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`reminder_user_id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `reminder_user_id`,
  CHANGE `new_reminder_user_id` `reminder_user_id` char(36),
  ADD COLUMN `new_user_report_data_id` char(36) AFTER `user_report_data_id`;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `user_report_data_id`,
  CHANGE `new_user_report_data_id` `user_report_data_id` char(36),
  ADD COLUMN `new_user_report_data_id` char(36) AFTER `user_report_data_id`;
UPDATE `payroll_remittance_agency_event` set `new_user_report_data_id` = CASE WHEN ( `user_report_data_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_report_data_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_report_data_id`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `user_report_data_id`,
  CHANGE `new_user_report_data_id` `user_report_data_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `payroll_remittance_agency_event` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `payroll_remittance_agency_event` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
    ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `payroll_remittance_agency_event` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `payroll_remittance_agency_event` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `permission` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `permission` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `permission` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_permission_control_id` char(36) AFTER `permission_control_id`;
UPDATE `permission` set `new_permission_control_id` = CASE WHEN ( `permission_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `permission_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`permission_control_id`, 12, '0') ) END END;
ALTER TABLE `permission` DROP COLUMN `permission_control_id`,
  CHANGE `new_permission_control_id` `permission_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `permission` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `permission` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `permission` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `permission` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `permission` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `permission` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `permission_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `permission_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `permission_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `permission_control` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `permission_control` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `permission_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `permission_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `permission_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `permission_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `permission_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `permission_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `permission_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `permission_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `permission_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
    ADD COLUMN `new_permission_control_id` char(36) AFTER `permission_control_id`;
UPDATE `permission_user` set `new_permission_control_id` = CASE WHEN ( `permission_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `permission_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`permission_control_id`, 12, '0') ) END END;
ALTER TABLE `permission_user` DROP COLUMN `permission_control_id`,
  CHANGE `new_permission_control_id` `permission_control_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `permission_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `permission_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `policy_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `policy_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `policy_group` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
    ADD COLUMN `new_exception_policy_control_id` char(36) AFTER `exception_policy_control_id`;
UPDATE `policy_group` set `new_exception_policy_control_id` = CASE WHEN ( `exception_policy_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `exception_policy_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`exception_policy_control_id`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `exception_policy_control_id`,
  CHANGE `new_exception_policy_control_id` `exception_policy_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `policy_group` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `policy_group` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `policy_group` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_accrual_policy_id` char(36) AFTER `accrual_policy_id`;
UPDATE `policy_group` set `new_accrual_policy_id` = CASE WHEN ( `accrual_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_id`, 12, '0') ) END END;
ALTER TABLE `policy_group` DROP COLUMN `accrual_policy_id`,
  CHANGE `new_accrual_policy_id` `accrual_policy_id` char(36);

ALTER TABLE `policy_group_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `policy_group_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `policy_group_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_policy_group_id` char(36) AFTER `policy_group_id`;
UPDATE `policy_group_user` set `new_policy_group_id` = CASE WHEN ( `policy_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `policy_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`policy_group_id`, 12, '0') ) END END;
ALTER TABLE `policy_group_user` DROP COLUMN `policy_group_id`,
  CHANGE `new_policy_group_id` `policy_group_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `policy_group_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `policy_group_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `premium_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `premium_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `premium_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_accrual_policy_id` char(36) AFTER `accrual_policy_id`;
UPDATE `premium_policy` set `new_accrual_policy_id` = CASE WHEN ( `accrual_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `accrual_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`accrual_policy_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `accrual_policy_id`,
  CHANGE `new_accrual_policy_id` `accrual_policy_id` char(36),
  ADD COLUMN `new_pay_stub_entry_account_id` char(36) AFTER `pay_stub_entry_account_id`;
UPDATE `premium_policy` set `new_pay_stub_entry_account_id` = CASE WHEN ( `pay_stub_entry_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_account_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `pay_stub_entry_account_id`,
  CHANGE `new_pay_stub_entry_account_id` `pay_stub_entry_account_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `premium_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `premium_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `premium_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_wage_group_id` char(36) AFTER `wage_group_id`;
UPDATE `premium_policy` set `new_wage_group_id` = CASE WHEN ( `wage_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `wage_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`wage_group_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `wage_group_id`,
  CHANGE `new_wage_group_id` `wage_group_id` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `premium_policy` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `premium_policy` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36),
  ADD COLUMN `new_contributing_shift_policy_id` char(36) AFTER `contributing_shift_policy_id`;
UPDATE `premium_policy` set `new_contributing_shift_policy_id` = CASE WHEN ( `contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy` DROP COLUMN `contributing_shift_policy_id`,
  CHANGE `new_contributing_shift_policy_id` `contributing_shift_policy_id` char(36);

ALTER TABLE `premium_policy_branch` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `premium_policy_branch` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `premium_policy_branch` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_premium_policy_id` char(36) AFTER `premium_policy_id`;
UPDATE `premium_policy_branch` set `new_premium_policy_id` = CASE WHEN ( `premium_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `premium_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`premium_policy_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy_branch` DROP COLUMN `premium_policy_id`,
  CHANGE `new_premium_policy_id` `premium_policy_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `premium_policy_branch` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy_branch` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36);

ALTER TABLE `premium_policy_department` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `premium_policy_department` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `premium_policy_department` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_premium_policy_id` char(36) AFTER `premium_policy_id`;
UPDATE `premium_policy_department` set `new_premium_policy_id` = CASE WHEN ( `premium_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `premium_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`premium_policy_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy_department` DROP COLUMN `premium_policy_id`,
  CHANGE `new_premium_policy_id` `premium_policy_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `premium_policy_department` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `premium_policy_department` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36);

ALTER TABLE `punch` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `punch` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `punch` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_punch_control_id` char(36) AFTER `punch_control_id`;
UPDATE `punch` set `new_punch_control_id` = CASE WHEN ( `punch_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `punch_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`punch_control_id`, 12, '0') ) END END;
ALTER TABLE `punch` DROP COLUMN `punch_control_id`,
  CHANGE `new_punch_control_id` `punch_control_id` char(36),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `punch` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `punch` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `punch` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `punch` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `punch` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `punch` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `punch` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `punch` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `punch_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `punch_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `punch_control` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `punch_control` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `punch_control` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `punch_control` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `punch_control` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `punch_control` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `punch_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `punch_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `punch_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `punch_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `qualification` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `qualification` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `qualification` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `qualification` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `qualification` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_group_id` char(36) AFTER `group_id`;
UPDATE `qualification` set `new_group_id` = CASE WHEN ( `group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`group_id`, 12, '0') ) END END;
ALTER TABLE `qualification` DROP COLUMN `group_id`,
  CHANGE `new_group_id` `group_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `qualification` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `qualification` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `qualification` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `qualification` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `qualification` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `qualification` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `qualification_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `qualification_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `qualification_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `qualification_group` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `qualification_group` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `qualification_group` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `qualification_group` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `qualification_group` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `qualification_group` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `qualification_group` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `qualification_group` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `qualification_group` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `qualification_group` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36);

ALTER TABLE `recurring_holiday` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_holiday` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_holiday` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `recurring_holiday` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `recurring_holiday` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `recurring_holiday` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `recurring_holiday` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `recurring_holiday` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `recurring_holiday` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `recurring_holiday` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `recurring_holiday` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `recurring_ps_amendment` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_ps_amendment` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `recurring_ps_amendment` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_pay_stub_entry_name_id` char(36) AFTER `pay_stub_entry_name_id`;
UPDATE `recurring_ps_amendment` set `new_pay_stub_entry_name_id` = CASE WHEN ( `pay_stub_entry_name_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_stub_entry_name_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_stub_entry_name_id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `pay_stub_entry_name_id`,
  CHANGE `new_pay_stub_entry_name_id` `pay_stub_entry_name_id` char(36),
  ADD COLUMN `new_percent_amount_entry_name_id` char(36) AFTER `percent_amount_entry_name_id`;
UPDATE `recurring_ps_amendment` set `new_percent_amount_entry_name_id` = CASE WHEN ( `percent_amount_entry_name_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `percent_amount_entry_name_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`percent_amount_entry_name_id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `percent_amount_entry_name_id`,
  CHANGE `new_percent_amount_entry_name_id` `percent_amount_entry_name_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `recurring_ps_amendment` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `recurring_ps_amendment` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `recurring_ps_amendment` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `recurring_ps_amendment_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_ps_amendment_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_recurring_ps_amendment_id` char(36) AFTER `recurring_ps_amendment_id`;
UPDATE `recurring_ps_amendment_user` set `new_recurring_ps_amendment_id` = CASE WHEN ( `recurring_ps_amendment_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_ps_amendment_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_ps_amendment_id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment_user` DROP COLUMN `recurring_ps_amendment_id`,
  CHANGE `new_recurring_ps_amendment_id` `recurring_ps_amendment_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `recurring_ps_amendment_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `recurring_ps_amendment_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `recurring_schedule` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_schedule` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `recurring_schedule` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `recurring_schedule` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_recurring_schedule_control_id` char(36) AFTER `recurring_schedule_control_id`;
UPDATE `recurring_schedule` set `new_recurring_schedule_control_id` = CASE WHEN ( `recurring_schedule_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_schedule_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_schedule_control_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `recurring_schedule_control_id`,
  CHANGE `new_recurring_schedule_control_id` `recurring_schedule_control_id` char(36),
  ADD COLUMN `new_schedule_policy_id` char(36) AFTER `schedule_policy_id`;
UPDATE `recurring_schedule` set `new_schedule_policy_id` = CASE WHEN ( `schedule_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `schedule_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`schedule_policy_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `schedule_policy_id`,
  CHANGE `new_schedule_policy_id` `schedule_policy_id` char(36),
  ADD COLUMN `new_absence_policy_id` char(36) AFTER `absence_policy_id`;
UPDATE `recurring_schedule` set `new_absence_policy_id` = CASE WHEN ( `absence_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `absence_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`absence_policy_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `absence_policy_id`,
  CHANGE `new_absence_policy_id` `absence_policy_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `recurring_schedule` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `recurring_schedule` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `recurring_schedule` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `recurring_schedule` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36),
  ADD COLUMN `new_recurring_schedule_template_control_id` char(36) AFTER `recurring_schedule_template_control_id`;
UPDATE `recurring_schedule` set `new_recurring_schedule_template_control_id` = CASE WHEN ( `recurring_schedule_template_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_schedule_template_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_schedule_template_control_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `recurring_schedule_template_control_id`,
  CHANGE `new_recurring_schedule_template_control_id` `recurring_schedule_template_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `recurring_schedule` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
    ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `recurring_schedule` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `recurring_schedule` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `recurring_schedule_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_schedule_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `recurring_schedule_control` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_control` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_recurring_schedule_template_control_id` char(36) AFTER `recurring_schedule_template_control_id`;
UPDATE `recurring_schedule_control` set `new_recurring_schedule_template_control_id` = CASE WHEN ( `recurring_schedule_template_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_schedule_template_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_schedule_template_control_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_control` DROP COLUMN `recurring_schedule_template_control_id`,
  CHANGE `new_recurring_schedule_template_control_id` `recurring_schedule_template_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `recurring_schedule_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `recurring_schedule_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `recurring_schedule_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `recurring_schedule_template` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_schedule_template` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_recurring_schedule_template_control_id` char(36) AFTER `recurring_schedule_template_control_id`;
UPDATE `recurring_schedule_template` set `new_recurring_schedule_template_control_id` = CASE WHEN ( `recurring_schedule_template_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_schedule_template_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_schedule_template_control_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `recurring_schedule_template_control_id`,
  CHANGE `new_recurring_schedule_template_control_id` `recurring_schedule_template_control_id` char(36),
  ADD COLUMN `new_schedule_policy_id` char(36) AFTER `schedule_policy_id`;
UPDATE `recurring_schedule_template` set `new_schedule_policy_id` = CASE WHEN ( `schedule_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `schedule_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`schedule_policy_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `schedule_policy_id`,
  CHANGE `new_schedule_policy_id` `schedule_policy_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `recurring_schedule_template` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `recurring_schedule_template` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `recurring_schedule_template` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `recurring_schedule_template` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `recurring_schedule_template` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `recurring_schedule_template` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `recurring_schedule_template` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
    ADD COLUMN `new_absence_policy_id` char(36) AFTER `absence_policy_id`;
UPDATE `recurring_schedule_template` set `new_absence_policy_id` = CASE WHEN ( `absence_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `absence_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`absence_policy_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template` DROP COLUMN `absence_policy_id`,
  CHANGE `new_absence_policy_id` `absence_policy_id` char(36);

ALTER TABLE `recurring_schedule_template_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_schedule_template_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `recurring_schedule_template_control` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template_control` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `recurring_schedule_template_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `recurring_schedule_template_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `recurring_schedule_template_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_template_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `recurring_schedule_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `recurring_schedule_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_recurring_schedule_control_id` char(36) AFTER `recurring_schedule_control_id`;
UPDATE `recurring_schedule_user` set `new_recurring_schedule_control_id` = CASE WHEN ( `recurring_schedule_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_schedule_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_schedule_control_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_user` DROP COLUMN `recurring_schedule_control_id`,
  CHANGE `new_recurring_schedule_control_id` `recurring_schedule_control_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `recurring_schedule_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `recurring_schedule_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `regular_time_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `regular_time_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `regular_time_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_contributing_shift_policy_id` char(36) AFTER `contributing_shift_policy_id`;
UPDATE `regular_time_policy` set `new_contributing_shift_policy_id` = CASE WHEN ( `contributing_shift_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `contributing_shift_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`contributing_shift_policy_id`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `contributing_shift_policy_id`,
  CHANGE `new_contributing_shift_policy_id` `contributing_shift_policy_id` char(36),
  ADD COLUMN `new_pay_formula_policy_id` char(36) AFTER `pay_formula_policy_id`;
UPDATE `regular_time_policy` set `new_pay_formula_policy_id` = CASE WHEN ( `pay_formula_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_formula_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_formula_policy_id`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `pay_formula_policy_id`,
  CHANGE `new_pay_formula_policy_id` `pay_formula_policy_id` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `regular_time_policy` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `regular_time_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `regular_time_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `regular_time_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `regular_time_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `remittance_destination_account` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `remittance_destination_account` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_remittance_source_account_id` char(36) AFTER `remittance_source_account_id`;
UPDATE `remittance_destination_account` set `new_remittance_source_account_id` = CASE WHEN ( `remittance_source_account_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `remittance_source_account_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`remittance_source_account_id`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `remittance_source_account_id`,
  CHANGE `new_remittance_source_account_id` `remittance_source_account_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `remittance_destination_account` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `remittance_destination_account` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `remittance_destination_account` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `remittance_destination_account` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `remittance_destination_account` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `remittance_destination_account` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `remittance_source_account` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `remittance_source_account` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `remittance_source_account` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_legal_entity_id` char(36) AFTER `legal_entity_id`;
UPDATE `remittance_source_account` set `new_legal_entity_id` = CASE WHEN ( `legal_entity_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `legal_entity_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`legal_entity_id`, 12, '0') ) END END;
ALTER TABLE `remittance_source_account` DROP COLUMN `legal_entity_id`,
  CHANGE `new_legal_entity_id` `legal_entity_id` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `remittance_source_account` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `remittance_source_account` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `remittance_source_account` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `remittance_source_account` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `remittance_source_account` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `remittance_source_account` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `remittance_source_account` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `remittance_source_account` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `request` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `request` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `request` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `request` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `request` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `request` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `request` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `request` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `request` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `request` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `request` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `request` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `request` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `roe` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `roe` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `roe` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `roe` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `roe` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `roe` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `roe` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `roe` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `roe` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `roe` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `roe` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `round_interval_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `round_interval_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `round_interval_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `round_interval_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `round_interval_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `round_interval_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `round_interval_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `round_interval_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `round_interval_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `round_interval_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `round_interval_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `schedule` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `schedule` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `schedule` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `schedule` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `schedule` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_schedule_policy_id` char(36) AFTER `schedule_policy_id`;
UPDATE `schedule` set `new_schedule_policy_id` = CASE WHEN ( `schedule_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `schedule_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`schedule_policy_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `schedule_policy_id`,
  CHANGE `new_schedule_policy_id` `schedule_policy_id` char(36),
  ADD COLUMN `new_absence_policy_id` char(36) AFTER `absence_policy_id`;
UPDATE `schedule` set `new_absence_policy_id` = CASE WHEN ( `absence_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `absence_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`absence_policy_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `absence_policy_id`,
  CHANGE `new_absence_policy_id` `absence_policy_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `schedule` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `schedule` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `schedule` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `schedule` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36),
  ADD COLUMN `new_replaced_id` char(36) AFTER `replaced_id`;
UPDATE `schedule` set `new_replaced_id` = CASE WHEN ( `replaced_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `replaced_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`replaced_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `replaced_id`,
  CHANGE `new_replaced_id` `replaced_id` char(36),
    ADD COLUMN `new_recurring_schedule_template_control_id` char(36) AFTER `recurring_schedule_template_control_id`;
UPDATE `schedule` set `new_recurring_schedule_template_control_id` = CASE WHEN ( `recurring_schedule_template_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `recurring_schedule_template_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`recurring_schedule_template_control_id`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `recurring_schedule_template_control_id`,
  CHANGE `new_recurring_schedule_template_control_id` `recurring_schedule_template_control_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `schedule` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `schedule` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `schedule` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `schedule` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `schedule_policy` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `schedule_policy` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `schedule_policy` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_meal_policy_id` char(36) AFTER `meal_policy_id`;
UPDATE `schedule_policy` set `new_meal_policy_id` = CASE WHEN ( `meal_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `meal_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`meal_policy_id`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `meal_policy_id`,
  CHANGE `new_meal_policy_id` `meal_policy_id` char(36),
  ADD COLUMN `new_over_time_policy_id` char(36) AFTER `over_time_policy_id`;
UPDATE `schedule_policy` set `new_over_time_policy_id` = CASE WHEN ( `over_time_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `over_time_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`over_time_policy_id`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `over_time_policy_id`,
  CHANGE `new_over_time_policy_id` `over_time_policy_id` char(36),
  ADD COLUMN `new_partial_shift_absence_policy_id` char(36) AFTER `partial_shift_absence_policy_id`;
UPDATE `schedule_policy` set `new_partial_shift_absence_policy_id` = CASE WHEN ( `partial_shift_absence_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `partial_shift_absence_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`partial_shift_absence_policy_id`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `partial_shift_absence_policy_id`,
  CHANGE `new_partial_shift_absence_policy_id` `partial_shift_absence_policy_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `schedule_policy` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `schedule_policy` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `schedule_policy` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_full_shift_absence_policy_id` char(36) AFTER `full_shift_absence_policy_id`;
UPDATE `schedule_policy` set `new_full_shift_absence_policy_id` = CASE WHEN ( `full_shift_absence_policy_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `full_shift_absence_policy_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`full_shift_absence_policy_id`, 12, '0') ) END END;
ALTER TABLE `schedule_policy` DROP COLUMN `full_shift_absence_policy_id`,
  CHANGE `new_full_shift_absence_policy_id` `full_shift_absence_policy_id` char(36);

ALTER TABLE `station` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `station` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `station` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `station` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `station` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `station` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `station` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
  ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `station` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `station` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `station` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36);

ALTER TABLE `station_branch` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station_branch` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station_branch` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `station_branch` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `station_branch` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `station_branch` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `station_branch` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36);

ALTER TABLE `station_department` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station_department` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station_department` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `station_department` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `station_department` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `station_department` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `station_department` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36);

ALTER TABLE `station_exclude_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station_exclude_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station_exclude_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `station_exclude_user` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `station_exclude_user` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `station_exclude_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `station_exclude_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `station_include_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station_include_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station_include_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `station_include_user` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `station_include_user` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `station_include_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `station_include_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `station_user` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station_user` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station_user` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `station_user` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `station_user` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
    ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `station_user` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `station_user` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36);

ALTER TABLE `station_user_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `station_user_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `station_user_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_station_id` char(36) AFTER `station_id`;
UPDATE `station_user_group` set `new_station_id` = CASE WHEN ( `station_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `station_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`station_id`, 12, '0') ) END END;
ALTER TABLE `station_user_group` DROP COLUMN `station_id`,
  CHANGE `new_station_id` `station_id` char(36),
  ADD COLUMN `new_group_id` char(36) AFTER `group_id`;
UPDATE `station_user_group` set `new_group_id` = CASE WHEN ( `group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`group_id`, 12, '0') ) END END;
ALTER TABLE `station_user_group` DROP COLUMN `group_id`,
  CHANGE `new_group_id` `group_id` char(36);

ALTER TABLE `system_log` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `system_log` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `system_log` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `system_log` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `system_log` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_object_id` char(36) AFTER `object_id`;
UPDATE `system_log` set `new_object_id` = CASE WHEN ( `object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`object_id`, 12, '0') ) END END;
ALTER TABLE `system_log` DROP COLUMN `object_id`,
  CHANGE `new_object_id` `object_id` char(36);

ALTER TABLE `system_log_detail` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `system_log_detail` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `system_log_detail` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_system_log_id` char(36) AFTER `system_log_id`;
UPDATE `system_log_detail` set `new_system_log_id` = CASE WHEN ( `system_log_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `system_log_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`system_log_id`, 12, '0') ) END END;
ALTER TABLE `system_log_detail` DROP COLUMN `system_log_id`,
  CHANGE `new_system_log_id` `system_log_id` char(36);

ALTER TABLE `system_setting` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `system_setting` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `system_setting` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id);

ALTER TABLE `user_contact` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_contact` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_contact` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_contact` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_contact` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_ethnic_group_id` char(36) AFTER `ethnic_group_id`;
UPDATE `user_contact` set `new_ethnic_group_id` = CASE WHEN ( `ethnic_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `ethnic_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`ethnic_group_id`, 12, '0') ) END END;
ALTER TABLE `user_contact` DROP COLUMN `ethnic_group_id`,
  CHANGE `new_ethnic_group_id` `ethnic_group_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_contact` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_contact` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_contact` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_contact` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_contact` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_contact` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

TRUNCATE `user_date`;
ALTER TABLE `user_date` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_date` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_date` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_date` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_date` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `user_date` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `user_date` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_date` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_date` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_date` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_date` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_date` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_date` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_date_total` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_date_total` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_date_total` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_pay_period_id` char(36) AFTER `pay_period_id`;
UPDATE `user_date_total` set `new_pay_period_id` = CASE WHEN ( `pay_period_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `pay_period_id`,
  CHANGE `new_pay_period_id` `pay_period_id` char(36),
  ADD COLUMN `new_src_object_id` char(36) AFTER `src_object_id`;
UPDATE `user_date_total` set `new_src_object_id` = CASE WHEN ( `src_object_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `src_object_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`src_object_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `src_object_id`,
  CHANGE `new_src_object_id` `src_object_id` char(36),
  ADD COLUMN `new_pay_code_id` char(36) AFTER `pay_code_id`;
UPDATE `user_date_total` set `new_pay_code_id` = CASE WHEN ( `pay_code_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_code_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_code_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `pay_code_id`,
  CHANGE `new_pay_code_id` `pay_code_id` char(36),
  ADD COLUMN `new_punch_control_id` char(36) AFTER `punch_control_id`;
UPDATE `user_date_total` set `new_punch_control_id` = CASE WHEN ( `punch_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `punch_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`punch_control_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `punch_control_id`,
  CHANGE `new_punch_control_id` `punch_control_id` char(36),
  ADD COLUMN `new_branch_id` char(36) AFTER `branch_id`;
UPDATE `user_date_total` set `new_branch_id` = CASE WHEN ( `branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`branch_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `branch_id`,
  CHANGE `new_branch_id` `branch_id` char(36),
  ADD COLUMN `new_department_id` char(36) AFTER `department_id`;
UPDATE `user_date_total` set `new_department_id` = CASE WHEN ( `department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`department_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `department_id`,
  CHANGE `new_department_id` `department_id` char(36),
    ADD COLUMN `new_job_id` char(36) AFTER `job_id`;
UPDATE `user_date_total` set `new_job_id` = CASE WHEN ( `job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `job_id`,
  CHANGE `new_job_id` `job_id` char(36),
  ADD COLUMN `new_job_item_id` char(36) AFTER `job_item_id`;
UPDATE `user_date_total` set `new_job_item_id` = CASE WHEN ( `job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`job_item_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `job_item_id`,
  CHANGE `new_job_item_id` `job_item_id` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `user_date_total` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_date_total` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_date_total` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_date_total` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_date_total` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_deduction` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_deduction` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_deduction` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_deduction` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_deduction` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_company_deduction_id` char(36) AFTER `company_deduction_id`;
UPDATE `user_deduction` set `new_company_deduction_id` = CASE WHEN ( `company_deduction_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_deduction_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_deduction_id`, 12, '0') ) END END;
ALTER TABLE `user_deduction` DROP COLUMN `company_deduction_id`,
  CHANGE `new_company_deduction_id` `company_deduction_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_deduction` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_deduction` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_deduction` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_deduction` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_deduction` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_deduction` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_default` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_default` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `user_default` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_pay_period_schedule_id` char(36) AFTER `pay_period_schedule_id`;
UPDATE `user_default` set `new_pay_period_schedule_id` = CASE WHEN ( `pay_period_schedule_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `pay_period_schedule_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`pay_period_schedule_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `pay_period_schedule_id`,
  CHANGE `new_pay_period_schedule_id` `pay_period_schedule_id` char(36),
  ADD COLUMN `new_policy_group_id` char(36) AFTER `policy_group_id`;
UPDATE `user_default` set `new_policy_group_id` = CASE WHEN ( `policy_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `policy_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`policy_group_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `policy_group_id`,
  CHANGE `new_policy_group_id` `policy_group_id` char(36),
  ADD COLUMN `new_title_id` char(36) AFTER `title_id`;
UPDATE `user_default` set `new_title_id` = CASE WHEN ( `title_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `title_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`title_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `title_id`,
  CHANGE `new_title_id` `title_id` char(36),
  ADD COLUMN `new_default_branch_id` char(36) AFTER `default_branch_id`;
UPDATE `user_default` set `new_default_branch_id` = CASE WHEN ( `default_branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `default_branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`default_branch_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `default_branch_id`,
  CHANGE `new_default_branch_id` `default_branch_id` char(36),
  ADD COLUMN `new_default_department_id` char(36) AFTER `default_department_id`;
UPDATE `user_default` set `new_default_department_id` = CASE WHEN ( `default_department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `default_department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`default_department_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `default_department_id`,
  CHANGE `new_default_department_id` `default_department_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_default` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_default` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_default` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `user_default` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_permission_control_id` char(36) AFTER `permission_control_id`;
UPDATE `user_default` set `new_permission_control_id` = CASE WHEN ( `permission_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `permission_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`permission_control_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `permission_control_id`,
  CHANGE `new_permission_control_id` `permission_control_id` char(36),
  ADD COLUMN `new_legal_entity_id` char(36) AFTER `legal_entity_id`;
UPDATE `user_default` set `new_legal_entity_id` = CASE WHEN ( `legal_entity_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `legal_entity_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`legal_entity_id`, 12, '0') ) END END;
ALTER TABLE `user_default` DROP COLUMN `legal_entity_id`,
  CHANGE `new_legal_entity_id` `legal_entity_id` char(36);

ALTER TABLE `user_default_company_deduction` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_default_company_deduction` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_default_company_deduction` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_default_id` char(36) AFTER `user_default_id`;
UPDATE `user_default_company_deduction` set `new_user_default_id` = CASE WHEN ( `user_default_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_default_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_default_id`, 12, '0') ) END END;
ALTER TABLE `user_default_company_deduction` DROP COLUMN `user_default_id`,
  CHANGE `new_user_default_id` `user_default_id` char(36),
  ADD COLUMN `new_company_deduction_id` char(36) AFTER `company_deduction_id`;
UPDATE `user_default_company_deduction` set `new_company_deduction_id` = CASE WHEN ( `company_deduction_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_deduction_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_deduction_id`, 12, '0') ) END END;
ALTER TABLE `user_default_company_deduction` DROP COLUMN `company_deduction_id`,
  CHANGE `new_company_deduction_id` `company_deduction_id` char(36);

ALTER TABLE `user_education` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_education` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_education` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_education` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_education` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_qualification_id` char(36) AFTER `qualification_id`;
UPDATE `user_education` set `new_qualification_id` = CASE WHEN ( `qualification_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `qualification_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`qualification_id`, 12, '0') ) END END;
ALTER TABLE `user_education` DROP COLUMN `qualification_id`,
  CHANGE `new_qualification_id` `qualification_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_education` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_education` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_education` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_education` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_education` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_education` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_generic_data` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_generic_data` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_generic_data` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_generic_data` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_generic_data` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_generic_data` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_generic_data` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_generic_data` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_generic_data` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_generic_data` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_generic_data` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `user_generic_data` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `user_generic_data` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36);

ALTER TABLE `user_generic_status` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_generic_status` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_generic_status` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_generic_status` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_generic_status` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_batch_id` char(36) AFTER `batch_id`;
UPDATE `user_generic_status` set `new_batch_id` = CASE WHEN ( `batch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `batch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`batch_id`, 12, '0') ) END END;
ALTER TABLE `user_generic_status` DROP COLUMN `batch_id`,
  CHANGE `new_batch_id` `batch_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_generic_status` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_generic_status` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_generic_status` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_generic_status` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_generic_status` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_generic_status` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_group` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `user_group` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `user_group` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_group` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_group` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_group` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_group` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_group` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_group` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_parent_id` char(36) AFTER `parent_id`;
UPDATE `user_group` set `new_parent_id` = CASE WHEN ( `parent_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `parent_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`parent_id`, 12, '0') ) END END;
ALTER TABLE `user_group` DROP COLUMN `parent_id`,
  CHANGE `new_parent_id` `parent_id` char(36);

ALTER TABLE `user_identification` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_identification` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_identification` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_identification` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_identification` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_identification` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_identification` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_identification` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_identification` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_identification` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_identification` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_language` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_language` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_language` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_language` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_language` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_qualification_id` char(36) AFTER `qualification_id`;
UPDATE `user_language` set `new_qualification_id` = CASE WHEN ( `qualification_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `qualification_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`qualification_id`, 12, '0') ) END END;
ALTER TABLE `user_language` DROP COLUMN `qualification_id`,
  CHANGE `new_qualification_id` `qualification_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_language` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_language` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_language` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_language` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_language` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_language` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_license` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_license` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_license` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_license` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_license` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_qualification_id` char(36) AFTER `qualification_id`;
UPDATE `user_license` set `new_qualification_id` = CASE WHEN ( `qualification_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `qualification_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`qualification_id`, 12, '0') ) END END;
ALTER TABLE `user_license` DROP COLUMN `qualification_id`,
  CHANGE `new_qualification_id` `qualification_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_license` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_license` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_license` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_license` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_license` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_license` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_membership` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_membership` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_membership` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_qualification_id` char(36) AFTER `qualification_id`;
UPDATE `user_membership` set `new_qualification_id` = CASE WHEN ( `qualification_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `qualification_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`qualification_id`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `qualification_id`,
  CHANGE `new_qualification_id` `qualification_id` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `user_membership` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_membership` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_membership` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_membership` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_membership` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_preference` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_preference` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_preference` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_preference` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_preference` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_preference` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_preference` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_preference` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_preference` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_preference` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_preference` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_report_data` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_report_data` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_report_data` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `user_report_data` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `user_report_data` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_report_data` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_report_data` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_report_data` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_report_data` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_report_data` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_report_data` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_report_data` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_report_data` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_review` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_review` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_review` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_review_control_id` char(36) AFTER `user_review_control_id`;
UPDATE `user_review` set `new_user_review_control_id` = CASE WHEN ( `user_review_control_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_review_control_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_review_control_id`, 12, '0') ) END END;
ALTER TABLE `user_review` DROP COLUMN `user_review_control_id`,
  CHANGE `new_user_review_control_id` `user_review_control_id` char(36),
  ADD COLUMN `new_kpi_id` char(36) AFTER `kpi_id`;
UPDATE `user_review` set `new_kpi_id` = CASE WHEN ( `kpi_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `kpi_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`kpi_id`, 12, '0') ) END END;
ALTER TABLE `user_review` DROP COLUMN `kpi_id`,
  CHANGE `new_kpi_id` `kpi_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_review` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_review` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_review` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_review` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_review` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_review` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_review_control` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_review_control` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_review_control` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_review_control` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_review_control` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_reviewer_user_id` char(36) AFTER `reviewer_user_id`;
UPDATE `user_review_control` set `new_reviewer_user_id` = CASE WHEN ( `reviewer_user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `reviewer_user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`reviewer_user_id`, 12, '0') ) END END;
ALTER TABLE `user_review_control` DROP COLUMN `reviewer_user_id`,
  CHANGE `new_reviewer_user_id` `reviewer_user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_review_control` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_review_control` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_review_control` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_review_control` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_review_control` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_review_control` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_setting` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_setting` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_setting` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_setting` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_setting` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_setting` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_setting` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_setting` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_setting` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_setting` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_setting` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_skill` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_skill` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_skill` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_skill` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_skill` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_qualification_id` char(36) AFTER `qualification_id`;
UPDATE `user_skill` set `new_qualification_id` = CASE WHEN ( `qualification_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `qualification_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`qualification_id`, 12, '0') ) END END;
ALTER TABLE `user_skill` DROP COLUMN `qualification_id`,
  CHANGE `new_qualification_id` `qualification_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_skill` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_skill` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_skill` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_skill` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_skill` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_skill` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_tax` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_tax` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_tax` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_tax` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_tax` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_tax` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_tax` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_tax` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_tax` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_tax` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_tax` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_title` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_title` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_title` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `user_title` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `user_title` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_title` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_title` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_title` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_title` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_title` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_title` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `user_wage` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `user_wage` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `user_wage` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36),
  ADD COLUMN `new_user_id` char(36) AFTER `user_id`;
UPDATE `user_wage` set `new_user_id` = CASE WHEN ( `user_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `user_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`user_id`, 12, '0') ) END END;
ALTER TABLE `user_wage` DROP COLUMN `user_id`,
  CHANGE `new_user_id` `user_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `user_wage` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `user_wage` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `user_wage` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `user_wage` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `user_wage` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `user_wage` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_wage_group_id` char(36) AFTER `wage_group_id`;
UPDATE `user_wage` set `new_wage_group_id` = CASE WHEN ( `wage_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `wage_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`wage_group_id`, 12, '0') ) END END;
ALTER TABLE `user_wage` DROP COLUMN `wage_group_id`,
  CHANGE `new_wage_group_id` `wage_group_id` char(36);

ALTER TABLE `users` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `users` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `id`, DROP PRIMARY KEY,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `users` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `users` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `users` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `users` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36),
  ADD COLUMN `new_title_id` char(36) AFTER `title_id`;
UPDATE `users` set `new_title_id` = CASE WHEN ( `title_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `title_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`title_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `title_id`,
  CHANGE `new_title_id` `title_id` char(36),
  ADD COLUMN `new_default_branch_id` char(36) AFTER `default_branch_id`;
UPDATE `users` set `new_default_branch_id` = CASE WHEN ( `default_branch_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `default_branch_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`default_branch_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `default_branch_id`,
  CHANGE `new_default_branch_id` `default_branch_id` char(36),
  ADD COLUMN `new_default_department_id` char(36) AFTER `default_department_id`;
UPDATE `users` set `new_default_department_id` = CASE WHEN ( `default_department_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `default_department_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`default_department_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `default_department_id`,
  CHANGE `new_default_department_id` `default_department_id` char(36),
  ADD COLUMN `new_group_id` char(36) AFTER `group_id`;
UPDATE `users` set `new_group_id` = CASE WHEN ( `group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`group_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `group_id`,
  CHANGE `new_group_id` `group_id` char(36),
  ADD COLUMN `new_currency_id` char(36) AFTER `currency_id`;
UPDATE `users` set `new_currency_id` = CASE WHEN ( `currency_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `currency_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`currency_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `currency_id`,
  CHANGE `new_currency_id` `currency_id` char(36),
  ADD COLUMN `new_ethnic_group_id` char(36) AFTER `ethnic_group_id`;
UPDATE `users` set `new_ethnic_group_id` = CASE WHEN ( `ethnic_group_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `ethnic_group_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`ethnic_group_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `ethnic_group_id`,
  CHANGE `new_ethnic_group_id` `ethnic_group_id` char(36),
  ADD COLUMN `new_default_job_id` char(36) AFTER `default_job_id`;
UPDATE `users` set `new_default_job_id` = CASE WHEN ( `default_job_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `default_job_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`default_job_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `default_job_id`,
  CHANGE `new_default_job_id` `default_job_id` char(36),
  ADD COLUMN `new_default_job_item_id` char(36) AFTER `default_job_item_id`;
UPDATE `users` set `new_default_job_item_id` = CASE WHEN ( `default_job_item_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `default_job_item_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`default_job_item_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `default_job_item_id`,
  CHANGE `new_default_job_item_id` `default_job_item_id` char(36),
  ADD COLUMN `new_legal_entity_id` char(36) AFTER `legal_entity_id`;
UPDATE `users` set `new_legal_entity_id` = CASE WHEN ( `legal_entity_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `legal_entity_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`legal_entity_id`, 12, '0') ) END END;
ALTER TABLE `users` DROP COLUMN `legal_entity_id`,
  CHANGE `new_legal_entity_id` `legal_entity_id` char(36);

ALTER TABLE `wage_group` ADD COLUMN `new_id` char(36) AFTER `id`;
UPDATE `wage_group` set `new_id` = CASE WHEN ( `id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`id`, 12, '0') ) END END;
ALTER TABLE `wage_group` DROP COLUMN `id`,
  CHANGE `new_id` `id` char(36), ADD PRIMARY KEY (id),
  ADD COLUMN `new_company_id` char(36) AFTER `company_id`;
UPDATE `wage_group` set `new_company_id` = CASE WHEN ( `company_id` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `company_id` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`company_id`, 12, '0') ) END END;
ALTER TABLE `wage_group` DROP COLUMN `company_id`,
  CHANGE `new_company_id` `company_id` char(36),
  ADD COLUMN `new_created_by` char(36) AFTER `created_by`;
UPDATE `wage_group` set `new_created_by` = CASE WHEN ( `created_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `created_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`created_by`, 12, '0') ) END END;
ALTER TABLE `wage_group` DROP COLUMN `created_by`,
  CHANGE `new_created_by` `created_by` char(36),
  ADD COLUMN `new_updated_by` char(36) AFTER `updated_by`;
UPDATE `wage_group` set `new_updated_by` = CASE WHEN ( `updated_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `updated_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`updated_by`, 12, '0') ) END END;
ALTER TABLE `wage_group` DROP COLUMN `updated_by`,
  CHANGE `new_updated_by` `updated_by` char(36),
  ADD COLUMN `new_deleted_by` char(36) AFTER `deleted_by`;
UPDATE `wage_group` set `new_deleted_by` = CASE WHEN ( `deleted_by` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `deleted_by` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`deleted_by`, 12, '0') ) END END;
ALTER TABLE `wage_group` DROP COLUMN `deleted_by`,
  CHANGE `new_deleted_by` `deleted_by` char(36);

ALTER TABLE `pay_stub_entry_account_link` ADD COLUMN `new_total_gross` char(36) AFTER `total_gross`;
UPDATE `pay_stub_entry_account_link` set `new_total_gross` = CASE WHEN ( `new_total_gross` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_total_gross` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_total_gross`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `total_gross`,
  CHANGE `new_total_gross` `total_gross` char(36),
  ADD COLUMN `new_total_employee_deduction` char(36) AFTER `total_employee_deduction`;
UPDATE `pay_stub_entry_account_link` set `new_total_employee_deduction` = CASE WHEN ( `new_total_employee_deduction` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_total_employee_deduction` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_total_employee_deduction`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `total_employee_deduction`,
  CHANGE `new_total_employee_deduction` `total_employee_deduction` char(36),
  ADD COLUMN `new_total_employer_deduction` char(36) AFTER `total_employer_deduction`;
UPDATE `pay_stub_entry_account_link` set `new_total_employer_deduction` = CASE WHEN ( `new_total_employer_deduction` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_total_employer_deduction` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_total_employer_deduction`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `total_employer_deduction`,
  CHANGE `new_total_employer_deduction` `total_employer_deduction` char(36),
  ADD COLUMN `new_total_net_pay` char(36) AFTER `total_net_pay`;
UPDATE `pay_stub_entry_account_link` set `new_total_net_pay` = CASE WHEN ( `new_total_net_pay` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_total_net_pay` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_total_net_pay`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `total_net_pay`,
  CHANGE `new_total_net_pay` `total_net_pay` char(36),
  ADD COLUMN `new_regular_time` char(36) AFTER `regular_time`;
UPDATE `pay_stub_entry_account_link` set `new_regular_time` = CASE WHEN ( `new_regular_time` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_regular_time` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_regular_time`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `regular_time`,
  CHANGE `new_regular_time` `regular_time` char(36),
  ADD COLUMN `new_employee_cpp` char(36) AFTER `employee_cpp`;
UPDATE `pay_stub_entry_account_link` set `new_employee_cpp` = CASE WHEN ( `new_employee_cpp` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_employee_cpp` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_employee_cpp`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `employee_cpp`,
  CHANGE `new_employee_cpp` `employee_cpp` char(36),
  ADD COLUMN `new_employee_ei` char(36) AFTER `employee_ei`;
UPDATE `pay_stub_entry_account_link` set `new_employee_ei` = CASE WHEN ( `new_employee_ei` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_employee_ei` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_employee_ei`, 12, '0') ) END END;
ALTER TABLE `pay_stub_entry_account_link` DROP COLUMN `employee_ei`,
  CHANGE `new_employee_ei` `employee_ei` char(36);

ALTER TABLE `company` ADD COLUMN `new_admin_contact` char(36) AFTER `admin_contact`;
UPDATE `company` set `new_admin_contact` = CASE WHEN ( `new_admin_contact` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_admin_contact` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_admin_contact`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `admin_contact`,
  CHANGE `new_admin_contact` `admin_contact` char(36),
  ADD COLUMN `new_billing_contact` char(36) AFTER `billing_contact`;
UPDATE `company` set `new_billing_contact` = CASE WHEN ( `new_billing_contact` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_billing_contact` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_billing_contact`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `billing_contact`,
  CHANGE `new_billing_contact` `billing_contact` char(36),
  ADD COLUMN `new_support_contact` char(36) AFTER `support_contact`;
UPDATE `company` set `new_support_contact` = CASE WHEN ( `new_support_contact` = 0 ) THEN '00000000-0000-0000-0000-000000000000' ELSE CASE WHEN ( `new_support_contact` = -1 ) THEN 'ffffffff-ffff-ffff-ffff-ffffffffffff' ELSE CONCAT('#UUID_PREFIX#-', lpad(`new_support_contact`, 12, '0') ) END END;
ALTER TABLE `company` DROP COLUMN `support_contact`,
  CHANGE `new_support_contact` `support_contact` char(36);

DROP TABLE IF EXISTS accrual_id_seq;
DROP TABLE IF EXISTS accrual_balance_id_seq;
DROP TABLE IF EXISTS company_generic_map_id_seq;
DROP TABLE IF EXISTS company_generic_tag_id_seq;
DROP TABLE IF EXISTS company_generic_tag_map_id_seq;
DROP TABLE IF EXISTS branch_id_seq;
DROP TABLE IF EXISTS company_deduction_pay_stub_entry_account_id_seq;
DROP TABLE IF EXISTS company_user_count_id_seq;
DROP TABLE IF EXISTS company_deduction_id_seq;
DROP TABLE IF EXISTS wage_group_id_seq;
DROP TABLE IF EXISTS company_id_seq;
DROP TABLE IF EXISTS company_setting_id_seq;
DROP TABLE IF EXISTS station_user_id_seq;
DROP TABLE IF EXISTS system_setting_id_seq;
DROP TABLE IF EXISTS user_date_total_id_seq;
DROP TABLE IF EXISTS currency_id_seq;
DROP TABLE IF EXISTS currency_rate_id_seq;
DROP TABLE IF EXISTS system_log_detail_id_seq;
DROP TABLE IF EXISTS station_exclude_user_id_seq;
DROP TABLE IF EXISTS station_department_id_seq;
DROP TABLE IF EXISTS station_include_user_id_seq;
DROP TABLE IF EXISTS exception_id_seq;
DROP TABLE IF EXISTS user_date_id_seq;
DROP TABLE IF EXISTS permission_id_seq;
DROP TABLE IF EXISTS station_id_seq;
DROP TABLE IF EXISTS station_user_group_id_seq;
DROP TABLE IF EXISTS permission_control_id_seq;
DROP TABLE IF EXISTS permission_user_id_seq;
DROP TABLE IF EXISTS other_field_id_seq;
DROP TABLE IF EXISTS station_branch_id_seq;
DROP TABLE IF EXISTS authorizations_id_seq;
DROP TABLE IF EXISTS system_log_id_seq;
DROP TABLE IF EXISTS cron_id_seq;
DROP TABLE IF EXISTS department_id_seq;
DROP TABLE IF EXISTS department_branch_id_seq;
DROP TABLE IF EXISTS department_branch_user_id_seq;
DROP TABLE IF EXISTS help_group_id_seq;
DROP TABLE IF EXISTS help_group_control_id_seq;
DROP TABLE IF EXISTS help_id_seq;
DROP TABLE IF EXISTS hierarchy_control_id_seq;
DROP TABLE IF EXISTS hierarchy_user_id_seq;
DROP TABLE IF EXISTS hierarchy_level_id_seq;
DROP TABLE IF EXISTS hierarchy_share_id_seq;
DROP TABLE IF EXISTS hierarchy_object_type_id_seq;
DROP TABLE IF EXISTS recurring_holiday_id_seq;
DROP TABLE IF EXISTS holidays_id_seq;
DROP TABLE IF EXISTS message_id_seq;
DROP TABLE IF EXISTS message_recipient_id_seq;
DROP TABLE IF EXISTS message_sender_id_seq;
DROP TABLE IF EXISTS message_control_id_seq;
DROP TABLE IF EXISTS roe_id_seq;
DROP TABLE IF EXISTS pay_period_time_sheet_verify_id_seq;
DROP TABLE IF EXISTS pay_period_id_seq;
DROP TABLE IF EXISTS pay_period_schedule_id_seq;
DROP TABLE IF EXISTS pay_period_schedule_user_id_seq;
DROP TABLE IF EXISTS pay_stub_entry_id_seq;
DROP TABLE IF EXISTS pay_stub_id_seq;
DROP TABLE IF EXISTS pay_stub_entry_account_link_id_seq;
DROP TABLE IF EXISTS pay_stub_entry_account_id_seq;
DROP TABLE IF EXISTS recurring_ps_amendment_id_seq;
DROP TABLE IF EXISTS recurring_ps_amendment_user_id_seq;
DROP TABLE IF EXISTS pay_stub_amendment_id_seq;
DROP TABLE IF EXISTS pay_stub_transaction_id_seq;
DROP TABLE IF EXISTS payroll_remittance_agency_event_id_seq;
DROP TABLE IF EXISTS exception_policy_id_seq;
DROP TABLE IF EXISTS premium_policy_department_id_seq;
DROP TABLE IF EXISTS round_interval_policy_id_seq;
DROP TABLE IF EXISTS holiday_policy_recurring_holiday_id_seq;
DROP TABLE IF EXISTS meal_policy_id_seq;
DROP TABLE IF EXISTS premium_policy_id_seq;
DROP TABLE IF EXISTS pay_code_id_seq;
DROP TABLE IF EXISTS pay_formula_policy_id_seq;
DROP TABLE IF EXISTS contributing_pay_code_policy_id_seq;
DROP TABLE IF EXISTS contributing_shift_policy_id_seq;
DROP TABLE IF EXISTS regular_time_policy_id_seq;
DROP TABLE IF EXISTS over_time_policy_id_seq;
DROP TABLE IF EXISTS schedule_policy_id_seq;
DROP TABLE IF EXISTS break_policy_id_seq;
DROP TABLE IF EXISTS holiday_policy_id_seq;
DROP TABLE IF EXISTS exception_policy_control_id_seq;
DROP TABLE IF EXISTS premium_policy_branch_id_seq;
DROP TABLE IF EXISTS policy_group_id_seq;
DROP TABLE IF EXISTS accrual_policy_id_seq;
DROP TABLE IF EXISTS accrual_policy_account_id_seq;
DROP TABLE IF EXISTS accrual_policy_milestone_id_seq;
DROP TABLE IF EXISTS policy_group_user_id_seq;
DROP TABLE IF EXISTS absence_policy_id_seq;
DROP TABLE IF EXISTS punch_control_id_seq;
DROP TABLE IF EXISTS punch_id_seq;
DROP TABLE IF EXISTS request_id_seq;
DROP TABLE IF EXISTS recurring_schedule_control_id_seq;
DROP TABLE IF EXISTS schedule_id_seq;
DROP TABLE IF EXISTS recurring_schedule_user_id_seq;
DROP TABLE IF EXISTS recurring_schedule_template_control_id_seq;
DROP TABLE IF EXISTS recurring_schedule_template_id_seq;
DROP TABLE IF EXISTS recurring_schedule_id_seq;
DROP TABLE IF EXISTS user_identification_id_seq;
DROP TABLE IF EXISTS user_generic_data_id_seq;
DROP TABLE IF EXISTS user_preference_id_seq;
DROP TABLE IF EXISTS user_title_id_seq;
DROP TABLE IF EXISTS user_default_company_deduction_id_seq;
DROP TABLE IF EXISTS user_wage_id_seq;
DROP TABLE IF EXISTS user_default_id_seq;
DROP TABLE IF EXISTS users_id_seq;
DROP TABLE IF EXISTS user_generic_status_id_seq;
DROP TABLE IF EXISTS user_deduction_id_seq;
DROP TABLE IF EXISTS user_report_data_id_seq;
DROP TABLE IF EXISTS bank_account_id_seq;
DROP TABLE IF EXISTS qualification_id_seq;
DROP TABLE IF EXISTS qualification_group_id_seq;
DROP TABLE IF EXISTS user_skill_id_seq;
DROP TABLE IF EXISTS user_education_id_seq;
DROP TABLE IF EXISTS user_license_id_seq;
DROP TABLE IF EXISTS user_language_id_seq;
DROP TABLE IF EXISTS user_membership_id_seq;
DROP TABLE IF EXISTS user_group_id_seq;
DROP TABLE IF EXISTS kpi_id_seq;
DROP TABLE IF EXISTS kpi_group_id_seq;
DROP TABLE IF EXISTS user_review_control_id_seq;
DROP TABLE IF EXISTS user_review_id_seq;
DROP TABLE IF EXISTS user_contact_id_seq;
DROP TABLE IF EXISTS user_setting_id_seq;
DROP TABLE IF EXISTS ethnic_group_id_seq;
DROP TABLE IF EXISTS legal_entity_id_seq;
DROP TABLE IF EXISTS payroll_remittance_agency_id_seq;
DROP TABLE IF EXISTS remittance_source_account_id_seq;
DROP TABLE IF EXISTS remittance_destination_account_id_seq;