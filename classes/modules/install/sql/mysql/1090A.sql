DROP TABLE hierarchy_tree;

ALTER TABLE user_group ADD COLUMN parent_id integer NOT NULL DEFAULT 0;
UPDATE user_group SET parent_id = ( SELECT parent_id FROM user_group_tree WHERE user_group.id = user_group_tree.object_id );
DROP TABLE user_group_tree;

ALTER TABLE qualification_group ADD COLUMN parent_id integer NOT NULL DEFAULT 0;
UPDATE qualification_group SET parent_id = ( SELECT parent_id FROM qualification_group_tree WHERE qualification_group.id = qualification_group_tree.object_id );
DROP TABLE qualification_group_tree;

ALTER TABLE kpi_group ADD COLUMN parent_id integer NOT NULL DEFAULT 0;
UPDATE kpi_group SET parent_id = ( SELECT parent_id FROM kpi_group_tree WHERE kpi_group.id = kpi_group_tree.object_id );
DROP TABLE kpi_group_tree;