export var ALayoutCache = function() {

};

ALayoutCache.layout_dic = {};

ALayoutCache.default_columns = null;

ALayoutCache.getDefaultColumn = function( layout_name ) {
	if ( !ALayoutCache.default_columns ) {
		ALayoutCache.default_columns = ALayoutCache.buildDefaultColumns();
	}

	if ( !Global.isSet( ALayoutCache.default_columns[layout_name] ) ) {
		return [
			{ label: $.i18n._( 'Name' ), value: 'name' }
		]; //Default Column setting
	}

	return ALayoutCache.default_columns[layout_name];

};

ALayoutCache.buildDefaultColumns = function() {
	var default_columns = {};

//	  default_columns['global_branch'] = [new ViewColumn({label:'Name',value:'name'})];
//	  default_columns['global_department'] = [new ViewColumn({label:'Name',value:'name'})];
//	  default_columns['global_job_title'] = [new ViewColumn({label:'Name',value:'name'})];
//	  default_columns['global_permission_control'] = [new ViewColumn({label:'Name',value:'name'})];

	default_columns['global_client_contact'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Status' ), value: 'status' }
	];

	default_columns['global_legal_entity'] = [
		{ label: $.i18n._( 'Name' ), value: 'legal_name' }
	];

	default_columns['global_job'] = [
		{ label: $.i18n._( 'Name' ), value: 'name' },
		{ label: $.i18n._( 'Code' ), value: 'manual_id' }
	];

	default_columns['global_job_item'] = [
		{ label: $.i18n._( 'Name' ), value: 'name' },
		{ label: $.i18n._( 'Code' ), value: 'manual_id' }
	];

	default_columns['global_client_payment'] = [
		{ label: $.i18n._( 'Number' ), value: 'display_number' },
		{ label: $.i18n._( 'Type' ), value: 'type' }
	];
	default_columns['global_option_column'] = [
		{ label: $.i18n._( 'Name' ), value: 'label' }
	];
	default_columns['global_absence'] = [
		{ label: $.i18n._( 'Name' ), value: 'name' },
		{ label: $.i18n._( 'Date' ), value: 'date_stamp' }
	];
	default_columns['global_timesheet'] = [
		{ label: $.i18n._( 'Time' ), value: 'punch_time' },
		{ label: $.i18n._( 'In/Out' ), value: 'status' },
		{ label: $.i18n._( 'Punch Type' ), value: 'type' }

	];
	default_columns['global_user'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Status' ), value: 'status' }
	];

	default_columns['global_message_user'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Status' ), value: 'status' }
	];

	default_columns['global_user_contact'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' }
	];
	default_columns['global_wage'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Type' ), value: 'type' },
		{ label: $.i18n._( 'Wage' ), value: 'wage' }
	];
	default_columns['global_log'] = [
		{ label: $.i18n._( 'Date' ), value: 'date' },
		{ label: $.i18n._( 'Action' ), value: 'action' },
		{ label: $.i18n._( 'Object' ), value: 'object' }
	];
	default_columns['global_bank_account'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Account Name' ), value: 'account' }
	];
	default_columns['global_tree_column'] = [
		{ label: '', value: 'id' },
		{ label: $.i18n._( 'Name' ), value: 'name' }
	];

	default_columns['global_sort_columns'] = [
		{ label: $.i18n._( 'Column Name' ), value: 'label' },
		{ label: $.i18n._( 'Sort' ), value: 'sort' }
	];
	default_columns['global_Pay_period'] = [
		{ label: $.i18n._( 'Start Date' ), value: 'start_date' },
		{ label: $.i18n._( 'End Date' ), value: 'end_date' },
		{ label: $.i18n._( 'Pay Period Schedule' ), value: 'pay_period_schedule' }

	];

	default_columns['global_user_skill'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Skill' ), value: 'qualification' }

	];

	default_columns['global_user_education'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Course' ), value: 'qualification' }

	];

	default_columns['global_user_membership'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Membership' ), value: 'qualification' }

	];

	default_columns['global_user_license'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'License' ), value: 'qualification' }

	];

	default_columns['global_job_applicant'] = [
		{ label: $.i18n._( 'First Name' ), value: 'first_name' },
		{ label: $.i18n._( 'Last Name' ), value: 'last_name' },
		{ label: $.i18n._( 'Status' ), value: 'status' }

	];

	default_columns['global_job_application'] = [
		{ label: $.i18n._( 'Job Vacancy' ), value: 'job_vacancy' },
		{ label: $.i18n._( 'Type' ), value: 'type' },
		{ label: $.i18n._( 'Status' ), value: 'status' }

	];

	default_columns['global_kpi'] = [
		{ label: $.i18n._( 'Name' ), value: 'name' },
		{ label: $.i18n._( 'Type' ), value: 'type' },
		{ label: $.i18n._( 'Status' ), value: 'status' }

	];

	default_columns['global_payroll_remittance_agency'] = [
		{ label: $.i18n._( 'Name' ), value: 'name' },
		{ label: $.i18n._( 'Legal Entity Name' ), value: 'legal_entity_legal_name' }
	];

	default_columns['global_kpi_review_control'] = [
		{ label: $.i18n._( 'Employee Name' ), value: 'user' },
		{ label: $.i18n._( 'Reviewer Name' ), value: 'reviewer_user' },
		{ label: $.i18n._( 'Start Date' ), value: 'start_date' },
		{ label: $.i18n._( 'End Date' ), value: 'end_date' }

	];

	default_columns['global_invoice'] = [
		{ label: $.i18n._( 'Client' ), value: 'client' },
		{ label: $.i18n._( 'Status' ), value: 'status' }

	];

	default_columns['global_client'] = [
		{ label: $.i18n._( 'Company Name' ), value: 'company_name' },
		{ label: $.i18n._( 'Groups' ), value: 'group' }

	];

	default_columns['global_transaction'] = [
		{ label: $.i18n._( 'Client' ), value: 'client' },
		{ label: $.i18n._( 'Status' ), value: 'status' }

	];

	default_columns['global_PayStubAccount'] = [
		{ label: $.i18n._( 'Name' ), value: 'name' },
		{ label: $.i18n._( 'Type' ), value: 'type' }
	];

	default_columns['global_simple_name'] = [
		{ label: 'Name', value: 'name' }
	];

	return default_columns;
};
