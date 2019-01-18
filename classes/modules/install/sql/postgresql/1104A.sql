ALTER TABLE company_deduction ADD COLUMN description character varying;

--Hire date used begin day epoch.
ALTER TABLE users ADD COLUMN tmp_hire_date DATE;
-- This will skip users without a user_preference row due to not being able to use a LEFT JOIN. So update those in the next query.
UPDATE users as a SET tmp_hire_date = c.final_hire_date FROM (
	SELECT a.id, a.user_name, a.first_name, a.last_name, a.hire_date, to_timestamp(a.hire_date),
		   CASE WHEN ( timezone(b.time_zone, to_timestamp(a.hire_date) :: TIMESTAMPTZ) - timezone(b.time_zone, to_timestamp(a.hire_date) :: TIMESTAMPTZ) :: DATE ) <= INTERVAL '12 hours'
					 THEN ( timezone(b.time_zone, to_timestamp(a.hire_date) :: TIMESTAMPTZ) )::date
					 ELSE ( timezone(b.time_zone, to_timestamp(a.hire_date) :: TIMESTAMPTZ) + ( timezone(b.time_zone, to_timestamp(a.hire_date) :: TIMESTAMPTZ) - timezone(b.time_zone, to_timestamp(a.hire_date) :: TIMESTAMPTZ) :: DATE ) ) :: DATE END
					 AS final_hire_date,
		   a.updated_by,
		   b.time_zone
	FROM users AS a
	LEFT JOIN user_preference AS b ON ( a.updated_by = b.user_id )
	 ) AS c WHERE a.id = c.id AND a.hire_date IS NOT NULL;
UPDATE users as a SET tmp_hire_date = to_timestamp(a.hire_date)::DATE WHERE tmp_hire_date is NULL AND a.hire_date IS NOT NULL;
ALTER TABLE users ALTER COLUMN hire_date SET DATA TYPE date USING ( CASE WHEN hire_date IS NOT NULL THEN to_timestamp(hire_date)::date ELSE NULL END );
UPDATE users SET hire_date = tmp_hire_date;
ALTER TABLE users DROP COLUMN tmp_hire_date;

--Termination date used all of the begin, middle and end day epoch, so we need to adjust it differently than hire_date.
ALTER TABLE users ADD COLUMN tmp_termination_date DATE;
-- This will skip users without a user_preference row due to not being able to use a LEFT JOIN. So update those in the next query.
UPDATE users as a SET tmp_termination_date = c.final_termination_date FROM (
	SELECT a.id, a.user_name, a.first_name, a.last_name, a.termination_date, to_timestamp(a.termination_date),
			timezone(b.time_zone, to_timestamp(a.termination_date) :: TIMESTAMPTZ) :: DATE AS final_termination_date,
		   a.updated_by,
		   b.time_zone
	FROM users AS a
	LEFT JOIN user_preference AS b ON ( a.updated_by = b.user_id )
	 ) AS c WHERE a.id = c.id AND a.termination_date IS NOT NULL;
UPDATE users as a SET tmp_termination_date = to_timestamp(a.termination_date)::DATE WHERE tmp_termination_date is NULL AND a.termination_date IS NOT NULL;
ALTER TABLE users ALTER COLUMN termination_date SET DATA TYPE date USING ( CASE WHEN termination_date IS NOT NULL THEN to_timestamp(termination_date)::date ELSE NULL END );
UPDATE users SET termination_date = tmp_termination_date;
ALTER TABLE users DROP COLUMN tmp_termination_date;

-- Birth Dates were using middle day epoch mostly, so we shouldn't need to do any rounding to find midnight.
ALTER TABLE users ADD COLUMN tmp_birth_date DATE;
-- This will skip users without a user_preference row due to not being able to use a LEFT JOIN. So update those in the next query.
UPDATE users as a SET tmp_birth_date = c.final_birth_date FROM (
	SELECT a.id, a.user_name, a.first_name, a.last_name, a.birth_date, to_timestamp(a.birth_date),
			timezone(b.time_zone, to_timestamp(a.birth_date) :: TIMESTAMPTZ) :: DATE AS final_birth_date,
		   a.updated_by,
		   b.time_zone
	FROM users AS a
	LEFT JOIN user_preference AS b ON ( a.updated_by = b.user_id )
	 ) AS c WHERE a.id = c.id AND a.birth_date IS NOT NULL AND a.birth_date != 0;
UPDATE users as a SET tmp_birth_date = to_timestamp(a.birth_date)::DATE WHERE tmp_birth_date is NULL AND a.birth_date IS NOT NULL AND a.birth_date != 0;
ALTER TABLE users ALTER COLUMN birth_date SET DATA TYPE date USING ( CASE WHEN birth_date IS NOT NULL THEN to_timestamp(birth_date)::date ELSE NULL END );
UPDATE users SET birth_date = tmp_birth_date;
ALTER TABLE users DROP COLUMN tmp_birth_date;