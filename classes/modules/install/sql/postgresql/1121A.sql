CREATE TABLE idempotent_request (
    id uuid      NOT NULL,
    idempotent_key uuid NOT NULL,
    user_id uuid      NOT NULL,
    status_id integer DEFAULT 10 NOT NULL,
    request_date timestamp(3) with time zone DEFAULT now() NOT NULL,
    request_method TEXT NOT NULL,
    request_body JSON NOT NULL,
    request_uri TEXT NOT NULL,
    response_code INT DEFAULT NULL,
    response_body JSON DEFAULT NULL,
    response_date timestamp(3) with time zone DEFAULT NULL
);
CREATE UNIQUE INDEX  idempotent_request_idempotent_key ON idempotent_request( idempotent_key );
