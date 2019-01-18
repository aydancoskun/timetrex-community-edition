ALTER TABLE remittance_source_account ADD COLUMN company_id UUID;
UPDATE remittance_source_account SET company_id = b.company_id FROM (SELECT id, company_id FROM legal_entity) AS b WHERE b.id = remittance_source_account.legal_entity_id;
ALTER TABLE remittance_source_account ALTER company_id SET NOT NULL;

ALTER TABLE station ADD COLUMN default_mode_flag integer DEFAULT 0;
ALTER TABLE station ALTER COLUMN mode_flag SET DATA TYPE bigint;
