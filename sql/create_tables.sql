DROP TABLE IF EXISTS "user";
CREATE TABLE "user" (
         "id" bytea NOT NULL,
         "external_id" character varying(255) NOT NULL,
         "created_at" timestamptz(0) NOT NULL,
         CONSTRAINT "external_id" UNIQUE ("external_id"),
         CONSTRAINT "user_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "transaction";
CREATE TABLE "transaction" (
        "id" bytea NOT NULL,
        "action" character varying(255) NOT NULL,
        "amount" numeric(36,2) NOT NULL,
        "created_at" timestamptz(0) NOT NULL,
        "user_id" bytea NOT NULL,
        "credit_id" bytea NOT NULL,
        "request_id" bytea NOT NULL,
        CONSTRAINT "transaction_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE INDEX "idx_723705d1427eb8a5" ON "transaction" USING btree ("request_id");
CREATE INDEX "idx_723705d1a76ed395" ON "transaction" USING btree ("user_id");
CREATE INDEX "idx_723705d1ce062ff9" ON "transaction" USING btree ("credit_id");
CREATE INDEX "user_created_at" ON "transaction" USING btree ("user_id", "created_at");
CREATE INDEX "user_credit" ON "transaction" USING btree ("user_id", "credit_id");



DROP TABLE IF EXISTS "credit";
CREATE TABLE "credit" (
       "id" bytea NOT NULL,
       "amount" numeric(36,2) NOT NULL,
       "priority" integer NOT NULL,
       "type" character varying(255) NOT NULL,
       "note" character varying(255),
       "expired_at" timestamptz(0),
       "usable" boolean,
       "fully_used_at" timestamptz(0),
       "expired_amount" numeric(36,2) NOT NULL,
       "created_at" timestamptz(0) NOT NULL,
       "user_id" bytea NOT NULL,
       CONSTRAINT "credit_pkey" PRIMARY KEY ("id")
) WITH (oids = false);
CREATE INDEX "idx_1cc16efea76ed395" ON "credit" USING btree ("user_id");
CREATE INDEX "usable_priority_expiration" ON "credit" USING btree ("usable", "priority", "expired_at");
CREATE INDEX "usable_user_priority_expiration" ON "credit" USING btree ("usable", "user_id", "priority", "expired_at");





DROP TABLE IF EXISTS "request";
CREATE TABLE "request" (
       "id" bytea NOT NULL,
       "request_id" character varying(255) NOT NULL,
       "amount" numeric(36,2) NOT NULL,
       "operation" character varying(255) NOT NULL,
       "created_at" timestamptz(0) NOT NULL,
       "data" json NOT NULL,
       "user_id" bytea NOT NULL,
       CONSTRAINT "request_pkey" PRIMARY KEY ("id"),
       CONSTRAINT "request_user" UNIQUE ("request_id", "user_id")
) WITH (oids = false);
CREATE INDEX "idx_3b978f9fa76ed395" ON "request" USING btree ("user_id");
