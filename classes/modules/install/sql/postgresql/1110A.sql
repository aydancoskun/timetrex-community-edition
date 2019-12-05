DROP TABLE authentication;
CREATE TABLE authentication (
    session_id character varying(40) NOT NULL,
    type_id smallint NOT NULL,
    object_id uuid NOT NULL,
    end_point_id character varying(30),
    client_id character varying(30),
    idle_timeout integer DEFAULT 14400,
    ip_address character varying(45),
    user_agent character varying(40),
    created_date integer NOT NULL,
    updated_date integer,
    flags character varying
);