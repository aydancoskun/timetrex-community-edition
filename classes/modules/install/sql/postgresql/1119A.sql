UPDATE permission SET name = 'view_form1099nec' WHERE name = 'view_form1099misc';
UPDATE payroll_remittance_agency_event SET type_id = 'F1099NEC' WHERE type_id = 'F1099MISC';
UPDATE user_report_data SET script = 'Form1099NecReport', data = replace( data, '"l7":', '"l1":') WHERE script = 'Form1099MiscReport';
UPDATE user_report_data SET name = 'Form 1099-NEC Report' WHERE script = 'Form1099NecReport' AND name = 'Form 1099-MISC Report' AND user_id IS NULL;