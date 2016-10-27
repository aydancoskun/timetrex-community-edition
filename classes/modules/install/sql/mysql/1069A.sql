ALTER TABLE schedule CHANGE start_time start_time timestamp DEFAULT 0 NOT NULL;
ALTER TABLE schedule CHANGE end_time end_time timestamp DEFAULT 0 NOT NULL;
ALTER TABLE recurring_schedule CHANGE start_time start_time timestamp DEFAULT 0 NOT NULL;
ALTER TABLE recurring_schedule CHANGE end_time end_time timestamp DEFAULT 0 NOT NULL;
