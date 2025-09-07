-- DROP SCHEMA acceptance_grade;

CREATE SCHEMA acceptance_grade AUTHORIZATION postgres;

-- DROP SCHEMA class_reference;

CREATE SCHEMA class_reference AUTHORIZATION postgres;

-- DROP SCHEMA configure;

CREATE SCHEMA configure AUTHORIZATION postgres;

-- DROP SCHEMA production;

CREATE SCHEMA production AUTHORIZATION postgres;

-- DROP SCHEMA project;

CREATE SCHEMA project AUTHORIZATION postgres;

-- DROP SCHEMA public;

CREATE SCHEMA public AUTHORIZATION postgres;

-- DROP SCHEMA ui;

CREATE SCHEMA ui AUTHORIZATION postgres;


-- ui.chart_colors definition

-- Drop table

-- DROP TABLE ui.chart_colors;

CREATE TABLE ui.chart_colors (
	id bigserial NOT NULL,
	"label" varchar(255) NOT NULL,
	color varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT chart_colors_pkey PRIMARY KEY (id)
);


-- ui.clean_statuses definition

-- Drop table

-- DROP TABLE ui.clean_statuses;

CREATE TABLE ui.clean_statuses (
	"cleanstation_ID" varchar(255) NOT NULL,
	cleanstation_status varchar(255) NULL,
	door_status varchar(255) NULL,
	cylinder_status varchar(255) NULL,
	temperature int4 NULL,
	humidity int4 NULL,
	pressure_difference int4 NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT clean_statuses_pkey PRIMARY KEY ("cleanstation_ID")
);


-- ui.devices definition

-- Drop table

-- DROP TABLE ui.devices;

CREATE TABLE ui.devices (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	ap varchar(255) NULL,
	ip inet NULL,
	is_connected int2 NOT NULL DEFAULT '0'::smallint,
	connected_at timestamp(0) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT devices_pkey PRIMARY KEY (id)
);


-- ui.edge_configuration_types definition

-- Drop table

-- DROP TABLE ui.edge_configuration_types;

CREATE TABLE ui.edge_configuration_types (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	"validate" varchar(255) NULL,
	view_type varchar(255) NOT NULL,
	is_unique int2 NOT NULL DEFAULT '0'::smallint,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT edge_configuration_types_pkey PRIMARY KEY (id)
);


-- ui.failed_jobs definition

-- Drop table

-- DROP TABLE ui.failed_jobs;

CREATE TABLE ui.failed_jobs (
	id bigserial NOT NULL,
	uuid varchar(255) NOT NULL,
	"connection" text NOT NULL,
	queue text NOT NULL,
	payload text NOT NULL,
	"exception" text NOT NULL,
	failed_at timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CONSTRAINT failed_jobs_pkey PRIMARY KEY (id),
	CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid)
);


-- ui.history definition

-- Drop table

-- DROP TABLE ui.history;

CREATE TABLE ui.history (
	"work history" json NULL,
	"login history" json NULL
);


-- ui.jobs definition

-- Drop table

-- DROP TABLE ui.jobs;

CREATE TABLE ui.jobs (
	id bigserial NOT NULL,
	queue varchar(255) NOT NULL,
	payload text NOT NULL,
	attempts int2 NOT NULL,
	reserved_at int4 NULL,
	available_at int4 NOT NULL,
	created_at int4 NOT NULL,
	CONSTRAINT jobs_pkey PRIMARY KEY (id)
);
CREATE INDEX jobs_queue_index ON ui.jobs USING btree (queue);


-- ui.laser_dusts definition

-- Drop table

-- DROP TABLE ui.laser_dusts;

CREATE TABLE ui.laser_dusts (
	id bigserial NOT NULL,
	val_1 float8 NOT NULL,
	val_2 float8 NOT NULL,
	val_3 float8 NOT NULL,
	val_4 float8 NOT NULL,
	val_5 float8 NOT NULL,
	val_6 float8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	vehicle_id varchar(255) NULL,
	CONSTRAINT laser_dusts_pkey PRIMARY KEY (id)
);


-- ui.migrations definition

-- Drop table

-- DROP TABLE ui.migrations;

CREATE TABLE ui.migrations (
	id serial4 NOT NULL,
	migration varchar(255) NOT NULL,
	batch int4 NOT NULL,
	CONSTRAINT migrations_pkey PRIMARY KEY (id)
);


-- ui.missions definition

-- Drop table

-- DROP TABLE ui.missions;

CREATE TABLE ui.missions (
	guid varchar(255) NOT NULL,
	"name" varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT missions_pkey PRIMARY KEY (guid)
);


-- ui.mqtt_command_types definition

-- Drop table

-- DROP TABLE ui.mqtt_command_types;

CREATE TABLE ui.mqtt_command_types (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	typename varchar(255) NULL,
	is_mission int2 NOT NULL DEFAULT '0'::smallint,
	sender_type varchar(255) NOT NULL DEFAULT 'ui'::character varying,
	sender_name varchar(255) NOT NULL DEFAULT 'ui'::character varying,
	receiver_type varchar(255) NOT NULL DEFAULT 'acs'::character varying,
	receiver_name varchar(255) NOT NULL DEFAULT 'acs'::character varying,
	mission_type varchar(255) NULL,
	is_schedule int2 NOT NULL DEFAULT '0'::smallint,
	CONSTRAINT mqtt_command_types_pkey PRIMARY KEY (id)
);


-- ui.oauth_access_tokens definition

-- Drop table

-- DROP TABLE ui.oauth_access_tokens;

CREATE TABLE ui.oauth_access_tokens (
	id varchar(100) NOT NULL,
	user_id int8 NULL,
	client_id int8 NOT NULL,
	"name" varchar(255) NULL,
	scopes text NULL,
	revoked bool NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	expires_at timestamp(0) NULL,
	CONSTRAINT oauth_access_tokens_pkey PRIMARY KEY (id)
);
CREATE INDEX oauth_access_tokens_user_id_index ON ui.oauth_access_tokens USING btree (user_id);


-- ui.oauth_auth_codes definition

-- Drop table

-- DROP TABLE ui.oauth_auth_codes;

CREATE TABLE ui.oauth_auth_codes (
	id varchar(100) NOT NULL,
	user_id int8 NOT NULL,
	client_id int8 NOT NULL,
	scopes text NULL,
	revoked bool NOT NULL,
	expires_at timestamp(0) NULL,
	CONSTRAINT oauth_auth_codes_pkey PRIMARY KEY (id)
);
CREATE INDEX oauth_auth_codes_user_id_index ON ui.oauth_auth_codes USING btree (user_id);


-- ui.oauth_clients definition

-- Drop table

-- DROP TABLE ui.oauth_clients;

CREATE TABLE ui.oauth_clients (
	id bigserial NOT NULL,
	user_id int8 NULL,
	"name" varchar(255) NOT NULL,
	secret varchar(100) NULL,
	provider varchar(255) NULL,
	redirect text NOT NULL,
	personal_access_client bool NOT NULL,
	password_client bool NOT NULL,
	revoked bool NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT oauth_clients_pkey PRIMARY KEY (id)
);
CREATE INDEX oauth_clients_user_id_index ON ui.oauth_clients USING btree (user_id);


-- ui.oauth_personal_access_clients definition

-- Drop table

-- DROP TABLE ui.oauth_personal_access_clients;

CREATE TABLE ui.oauth_personal_access_clients (
	id bigserial NOT NULL,
	client_id int8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT oauth_personal_access_clients_pkey PRIMARY KEY (id)
);


-- ui.oauth_refresh_tokens definition

-- Drop table

-- DROP TABLE ui.oauth_refresh_tokens;

CREATE TABLE ui.oauth_refresh_tokens (
	id varchar(100) NOT NULL,
	access_token_id varchar(100) NOT NULL,
	revoked bool NOT NULL,
	expires_at timestamp(0) NULL,
	CONSTRAINT oauth_refresh_tokens_pkey PRIMARY KEY (id)
);
CREATE INDEX oauth_refresh_tokens_access_token_id_index ON ui.oauth_refresh_tokens USING btree (access_token_id);


-- ui.password_resets definition

-- Drop table

-- DROP TABLE ui.password_resets;

CREATE TABLE ui.password_resets (
	email varchar(255) NOT NULL,
	"token" varchar(255) NOT NULL,
	created_at timestamp(0) NULL
);
CREATE INDEX password_resets_email_index ON ui.password_resets USING btree (email);


-- ui.permissions definition

-- Drop table

-- DROP TABLE ui.permissions;

CREATE TABLE ui.permissions (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	display_name varchar(255) NULL,
	description varchar(255) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT permissions_name_unique UNIQUE (name),
	CONSTRAINT permissions_pkey PRIMARY KEY (id)
);


-- ui.personal_access_tokens definition

-- Drop table

-- DROP TABLE ui.personal_access_tokens;

CREATE TABLE ui.personal_access_tokens (
	id bigserial NOT NULL,
	tokenable_type varchar(255) NOT NULL,
	tokenable_id int8 NOT NULL,
	"name" varchar(255) NOT NULL,
	"token" varchar(64) NOT NULL,
	abilities text NULL,
	last_used_at timestamp(0) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id),
	CONSTRAINT personal_access_tokens_token_unique UNIQUE (token)
);
CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON ui.personal_access_tokens USING btree (tokenable_type, tokenable_id);


-- ui.pollution_conditions definition

-- Drop table

-- DROP TABLE ui.pollution_conditions;

CREATE TABLE ui.pollution_conditions (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	color varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	display_name varchar(255) NULL,
	CONSTRAINT pollution_conditions_pkey PRIMARY KEY (id)
);


-- ui.projects definition

-- Drop table

-- DROP TABLE ui.projects;

CREATE TABLE ui.projects (
	"name" varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	id bigserial NOT NULL,
	is_deploy int2 NOT NULL DEFAULT '0'::smallint,
	CONSTRAINT projects_pkey PRIMARY KEY (id)
);


-- ui.roles definition

-- Drop table

-- DROP TABLE ui.roles;

CREATE TABLE ui.roles (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	display_name varchar(255) NULL,
	description varchar(255) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT roles_name_unique UNIQUE (name),
	CONSTRAINT roles_pkey PRIMARY KEY (id)
);


-- ui."room environment" definition

-- Drop table

-- DROP TABLE ui."room environment";

CREATE TABLE ui."room environment" (
	temperature float4 NULL,
	humidity float4 NULL,
	"pressure difference" float4 NULL
);


-- ui.users definition

-- Drop table

-- DROP TABLE ui.users;

CREATE TABLE ui.users (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	email varchar(255) NULL,
	email_verified_at timestamp(0) NULL,
	"password" varchar(255) NOT NULL,
	remember_token varchar(100) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	account varchar(255) NOT NULL,
	"enable" int2 NOT NULL DEFAULT '1'::smallint,
	has_carrier int2 NOT NULL DEFAULT '1'::smallint,
	CONSTRAINT users_email_unique UNIQUE (email),
	CONSTRAINT users_pkey PRIMARY KEY (id)
);


-- ui.vehicle_error_types definition

-- Drop table

-- DROP TABLE ui.vehicle_error_types;

CREATE TABLE ui.vehicle_error_types (
	id int8 NOT NULL,
	"name" varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT vehicle_error_types_pkey PRIMARY KEY (id)
);


-- ui.vertex_configuration_columns definition

-- Drop table

-- DROP TABLE ui.vertex_configuration_columns;

CREATE TABLE ui.vertex_configuration_columns (
	id serial4 NOT NULL,
	"name" varchar(255) NOT NULL,
	"validate" varchar(255) NULL DEFAULT NULL::character varying,
	view_type varchar(255) NOT NULL,
	is_unique int2 NOT NULL DEFAULT '0'::smallint,
	created_at timestamp NULL,
	updated_at timestamp NULL,
	CONSTRAINT vertex_configuration_columns_pkey PRIMARY KEY (id)
);


-- ui.vertex_types definition

-- Drop table

-- DROP TABLE ui.vertex_types;

CREATE TABLE ui.vertex_types (
	id bigserial NOT NULL,
	"name" varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT vertex_types_pkey PRIMARY KEY (id)
);


-- ui.mission_bookings definition

-- Drop table

-- DROP TABLE ui.mission_bookings;

CREATE TABLE ui.mission_bookings (
	id bigserial NOT NULL,
	mission_id varchar(255) NOT NULL,
	schedule varchar(255) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	deleted_at timestamp(0) NULL,
	CONSTRAINT mission_bookings_pkey PRIMARY KEY (id),
	CONSTRAINT mission_bookings_mission_id_foreign FOREIGN KEY (mission_id) REFERENCES ui.missions(guid) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ui.permission_role definition

-- Drop table

-- DROP TABLE ui.permission_role;

CREATE TABLE ui.permission_role (
	permission_id int8 NOT NULL,
	role_id int8 NOT NULL,
	CONSTRAINT permission_role_pkey PRIMARY KEY (permission_id, role_id),
	CONSTRAINT permission_role_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES ui.permissions(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT permission_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES ui.roles(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ui.role_user definition

-- Drop table

-- DROP TABLE ui.role_user;

CREATE TABLE ui.role_user (
	role_id int8 NOT NULL,
	user_id int8 NOT NULL,
	CONSTRAINT role_user_pkey PRIMARY KEY (user_id, role_id),
	CONSTRAINT role_user_role_id_foreign FOREIGN KEY (role_id) REFERENCES ui.roles(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT role_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES ui.users(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ui.user_vehicle_mgmt definition

-- Drop table

-- DROP TABLE ui.user_vehicle_mgmt;

CREATE TABLE ui.user_vehicle_mgmt (
	vehicle_id varchar(256) NOT NULL,
	user_id int8 NOT NULL,
	CONSTRAINT user_vehicle_mgmt_pkey PRIMARY KEY (user_id, vehicle_id),
	CONSTRAINT user_vehicle_mgmt_user_id_foreign FOREIGN KEY (user_id) REFERENCES ui.users(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ui.vehicle_colors definition

-- Drop table

-- DROP TABLE ui.vehicle_colors;

CREATE TABLE ui.vehicle_colors (
	id bigserial NOT NULL,
	user_id int8 NOT NULL,
	vehicle_id varchar(256) NOT NULL,
	color varchar(7) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT vehicle_colors_pkey PRIMARY KEY (id),
	CONSTRAINT vehicle_colors_user_id_foreign FOREIGN KEY (user_id) REFERENCES ui.users(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ui.vertex_configuration_types definition

-- Drop table

-- DROP TABLE ui.vertex_configuration_types;

CREATE TABLE ui.vertex_configuration_types (
	id serial4 NOT NULL,
	vertex_type_id int8 NOT NULL,
	disabled int2 NOT NULL DEFAULT '0'::smallint,
	created_at timestamp NULL,
	updated_at timestamp NULL,
	is_default int2 NOT NULL DEFAULT '0'::smallint,
	vertex_configuration_column_id int8 NULL,
	CONSTRAINT vertex_configuration_types_pkey PRIMARY KEY (id),
	CONSTRAINT vertex_configuration_types_vertex_configuration_column_id_forei FOREIGN KEY (vertex_configuration_column_id) REFERENCES ui.vertex_configuration_columns(id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT vertex_configuration_types_vertex_type_id_foreign FOREIGN KEY (vertex_type_id) REFERENCES ui.vertex_types(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- ui.clean_areas definition

-- Drop table

-- DROP TABLE ui.clean_areas;

CREATE TABLE ui.clean_areas (
	id bigserial NOT NULL,
	start_goal_x float8 NOT NULL,
	start_goal_y float8 NOT NULL,
	end_goal_x float8 NOT NULL,
	end_goal_y float8 NOT NULL,
	"enable" int2 NOT NULL DEFAULT '1'::smallint,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	region_mgmt_id int8 NULL,
	vehicle_mgmt_id varchar(255) NULL,
	CONSTRAINT clean_areas_pkey PRIMARY KEY (id)
);


-- ui.edge_configurations definition

-- Drop table

-- DROP TABLE ui.edge_configurations;

CREATE TABLE ui.edge_configurations (
	id bigserial NOT NULL,
	edge_id int4 NOT NULL,
	"type" varchar(30) NOT NULL,
	"data" varchar(256) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT edge_configurations_pkey PRIMARY KEY (id)
);


-- ui.edges definition

-- Drop table

-- DROP TABLE ui.edges;

CREATE TABLE ui.edges (
	id serial4 NOT NULL,
	direction int2 NOT NULL DEFAULT '0'::smallint,
	weight int2 NOT NULL DEFAULT '1'::smallint,
	start_vertex_id int4 NOT NULL,
	end_vertex_id int4 NOT NULL,
	"enable" int2 NOT NULL DEFAULT '1'::smallint,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	region varchar(256) NULL,
	"name" varchar(36) NULL,
	region_mgmt_id int8 NOT NULL,
	is_deploy int2 NOT NULL DEFAULT '0'::smallint,
	CONSTRAINT edges_pkey PRIMARY KEY (id)
);


-- ui.elevator_mgmt_vertex definition

-- Drop table

-- DROP TABLE ui.elevator_mgmt_vertex;

CREATE TABLE ui.elevator_mgmt_vertex (
	vertex_id int8 NOT NULL,
	elevator_mgmt_elevator_id varchar(255) NOT NULL,
	CONSTRAINT elevator_mgmt_vertex_pkey PRIMARY KEY (vertex_id, elevator_mgmt_elevator_id)
);


-- ui.gas_samplings definition

-- Drop table

-- DROP TABLE ui.gas_samplings;

CREATE TABLE ui.gas_samplings (
	id bigserial NOT NULL,
	location_id int8 NOT NULL,
	average_volume numeric(10, 2) NOT NULL,
	cumulative_volume numeric(10, 2) NOT NULL,
	second_mark int2 NOT NULL,
	is_latest int2 NOT NULL DEFAULT '1'::smallint,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	remote_management_system_status_id int8 NULL,
	CONSTRAINT gas_samplings_pkey PRIMARY KEY (id)
);


-- ui.location_mission_queue definition

-- Drop table

-- DROP TABLE ui.location_mission_queue;

CREATE TABLE ui.location_mission_queue (
	location_id int8 NOT NULL,
	mission_queue_id int8 NOT NULL
);


-- ui.maps definition

-- Drop table

-- DROP TABLE ui.maps;

CREATE TABLE ui.maps (
	guid varchar(255) NOT NULL,
	"name" varchar(255) NOT NULL,
	origin_x float8 NOT NULL,
	origin_y float8 NOT NULL,
	resolution numeric(10, 2) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	region_mgmt_id int8 NULL,
	CONSTRAINT maps_pkey PRIMARY KEY (guid)
);


-- ui.mir_statuses definition

-- Drop table

-- DROP TABLE ui.mir_statuses;

CREATE TABLE ui.mir_statuses (
	id bigserial NOT NULL,
	device_id int8 NOT NULL,
	"position" jsonb NOT NULL,
	robot_model varchar(255) NOT NULL,
	mission_text varchar(255) NOT NULL,
	velocity jsonb NOT NULL,
	battery_percentage numeric(30, 15) NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	mission_queue_id int8 NULL,
	map_id varchar(255) NULL,
	location_id int8 NULL,
	state_text varchar(255) NULL,
	current_status varchar(255) NULL,
	vehicle_error_type_id int8 NULL,
	vehicle_error_message text NULL,
	initial_petri_count int4 NULL,
	remaining_petri_count int4 NULL,
	device_time timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
	state_id int2 NOT NULL DEFAULT '3'::smallint,
	CONSTRAINT mir_statuses_pkey PRIMARY KEY (id)
);


-- ui.mission_queues definition

-- Drop table

-- DROP TABLE ui.mission_queues;

CREATE TABLE ui.mission_queues (
	id int8 NOT NULL,
	finished timestamp(0) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	state varchar(255) NULL,
	mission_id varchar(255) NULL,
	started timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP,
	remark text NULL,
	start_location_id int8 NULL,
	end_location_id int8 NULL,
	deleted_at timestamp(0) NULL,
	CONSTRAINT mission_queues_pkey PRIMARY KEY (id)
);


-- ui.mqtt_commands definition

-- Drop table

-- DROP TABLE ui.mqtt_commands;

CREATE TABLE ui.mqtt_commands (
	id bigserial NOT NULL,
	vehicle_id varchar(255) NULL,
	obj_port_id varchar(255) NULL,
	send_command jsonb NULL,
	user_id int8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	command_id varchar(255) NOT NULL,
	is_completed int2 NOT NULL DEFAULT '0'::smallint,
	mqtt_command_type_id int8 NULL,
	laser_detection int2 NOT NULL DEFAULT '0'::smallint,
	device_name varchar(255) NULL,
	sweep_start_goal_x float8 NULL,
	sweep_start_goal_y float8 NULL,
	sweep_end_goal_x float8 NULL,
	sweep_end_goal_y float8 NULL,
	goal_x float8 NULL,
	goal_y float8 NULL,
	receive_command jsonb NULL,
	typename varchar(255) NULL,
	is_mission int2 NOT NULL DEFAULT '0'::smallint,
	sender_type varchar(255) NOT NULL DEFAULT 'ui'::character varying,
	sender_name varchar(255) NOT NULL DEFAULT 'ui'::character varying,
	receiver_type varchar(255) NOT NULL DEFAULT 'acs'::character varying,
	receiver_name varchar(255) NOT NULL DEFAULT 'acs'::character varying,
	region_mgmt_id int8 NULL,
	CONSTRAINT mqtt_commands_pkey PRIMARY KEY (id)
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    ui.mqtt_commands for each row execute function ui.notify_event();


-- ui.region_mgmt_user definition

-- Drop table

-- DROP TABLE ui.region_mgmt_user;

CREATE TABLE ui.region_mgmt_user (
	user_id int8 NOT NULL,
	region_mgmt_id int8 NOT NULL,
	is_write int2 NOT NULL DEFAULT '0'::smallint,
	is_read int2 NOT NULL DEFAULT '1'::smallint,
	CONSTRAINT region_mgmt_user_pkey PRIMARY KEY (region_mgmt_id, user_id)
);


-- ui.remote_management_system_statuses definition

-- Drop table

-- DROP TABLE ui.remote_management_system_statuses;

CREATE TABLE ui.remote_management_system_statuses (
	id bigserial NOT NULL,
	location_id int8 NULL,
	"status_A_time" int2 NULL,
	"status_B_time" int2 NULL,
	"status_C_time" int2 NULL,
	"status_D_time" int2 NULL,
	"status_E_time" int2 NULL,
	"status_F_time" int2 NULL,
	is_latest int2 NOT NULL DEFAULT '1'::smallint,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	procedure_step int2 NULL,
	r2_value int2 NULL,
	r3_value int2 NULL,
	total_time int2 NULL,
	"result" int2 NOT NULL DEFAULT '1'::smallint,
	mission_queue_id int8 NULL,
	start_time timestamp(0) NULL,
	sampling_point int2 NOT NULL DEFAULT '1'::smallint,
	CONSTRAINT remote_management_system_statuses_pkey PRIMARY KEY (id)
);


-- ui.turning_points definition

-- Drop table

-- DROP TABLE ui.turning_points;

CREATE TABLE ui.turning_points (
	id bigserial NOT NULL,
	clean_area_id int8 NOT NULL,
	x float8 NOT NULL,
	y float8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT turning_points_pkey PRIMARY KEY (id)
);


-- ui.undeploy_locations definition

-- Drop table

-- DROP TABLE ui.undeploy_locations;

CREATE TABLE ui.undeploy_locations (
	id bigserial NOT NULL,
	vertex_id int8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT undeploy_locations_pkey PRIMARY KEY (id)
);


-- ui.vertex_configurations definition

-- Drop table

-- DROP TABLE ui.vertex_configurations;

CREATE TABLE ui.vertex_configurations (
	id serial4 NOT NULL,
	vertex_id int4 NOT NULL,
	"type" varchar(30) NOT NULL,
	"data" varchar(255) NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT vertex_configurations_pkey PRIMARY KEY (id)
);


-- ui.vertices definition

-- Drop table

-- DROP TABLE ui.vertices;

CREATE TABLE ui.vertices (
	id serial4 NOT NULL,
	x float8 NOT NULL,
	y float8 NOT NULL,
	z float8 NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	tag varchar(255) NULL,
	vertex_type_id int2 NOT NULL DEFAULT '1'::smallint,
	region varchar(256) NULL,
	attach_vertex_id int4 NULL,
	"name" varchar(255) NULL,
	obj_uid varchar(255) NULL,
	region_mgmt_id int8 NULL,
	is_deploy int2 NOT NULL DEFAULT '0'::smallint,
	"enable" int2 NOT NULL DEFAULT '1'::smallint,
	theta int2 NULL,
	CONSTRAINT vertices_pkey PRIMARY KEY (id)
);


-- ui.clean_areas foreign keys

ALTER TABLE ui.clean_areas ADD CONSTRAINT clean_areas_region_mgmt_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.clean_areas ADD CONSTRAINT clean_areas_vehicle_mgmt_id_foreign FOREIGN KEY (vehicle_mgmt_id) REFERENCES configure.vehicle_mgmt(vehicle_id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.edge_configurations foreign keys

ALTER TABLE ui.edge_configurations ADD CONSTRAINT edge_configurations_edge_id_foreign FOREIGN KEY (edge_id) REFERENCES ui.edges(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.edges foreign keys

ALTER TABLE ui.edges ADD CONSTRAINT edges_end_vertex_id_foreign FOREIGN KEY (end_vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.edges ADD CONSTRAINT edges_region_mgmt_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.edges ADD CONSTRAINT edges_start_vertex_id_foreign FOREIGN KEY (start_vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.elevator_mgmt_vertex foreign keys

ALTER TABLE ui.elevator_mgmt_vertex ADD CONSTRAINT ui_elevator_mgmt_vertex_elevator_id_foreign FOREIGN KEY (elevator_mgmt_elevator_id) REFERENCES configure.elevator_mgmt(elevator_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.elevator_mgmt_vertex ADD CONSTRAINT ui_elevator_mgmt_vertex_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.gas_samplings foreign keys

ALTER TABLE ui.gas_samplings ADD CONSTRAINT gas_samplings_location_id_foreign FOREIGN KEY (location_id) REFERENCES production."location"(id) ON DELETE CASCADE;
ALTER TABLE ui.gas_samplings ADD CONSTRAINT gas_samplings_remote_management_system_status_id_foreign FOREIGN KEY (remote_management_system_status_id) REFERENCES ui.remote_management_system_statuses(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.location_mission_queue foreign keys

ALTER TABLE ui.location_mission_queue ADD CONSTRAINT location_mission_queue_location_id_foreign FOREIGN KEY (location_id) REFERENCES production."location"(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.location_mission_queue ADD CONSTRAINT location_mission_queue_mission_queue_id_foreign FOREIGN KEY (mission_queue_id) REFERENCES ui.mission_queues(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.maps foreign keys

ALTER TABLE ui.maps ADD CONSTRAINT maps_region_mgmt_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE SET NULL ON UPDATE SET NULL;


-- ui.mir_statuses foreign keys

ALTER TABLE ui.mir_statuses ADD CONSTRAINT mir_statuses_device_id_foreign FOREIGN KEY (device_id) REFERENCES ui.devices(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.mir_statuses ADD CONSTRAINT mir_statuses_location_id_foreign FOREIGN KEY (location_id) REFERENCES production."location"(id) ON DELETE SET NULL ON UPDATE SET NULL;
ALTER TABLE ui.mir_statuses ADD CONSTRAINT mir_statuses_mission_queue_id_foreign FOREIGN KEY (mission_queue_id) REFERENCES ui.mission_queues(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.mir_statuses ADD CONSTRAINT mir_statuses_vehicle_error_type_id_foreign FOREIGN KEY (vehicle_error_type_id) REFERENCES ui.vehicle_error_types(id) ON DELETE SET NULL;


-- ui.mission_queues foreign keys

ALTER TABLE ui.mission_queues ADD CONSTRAINT mission_queues_end_location_id_foreign FOREIGN KEY (end_location_id) REFERENCES production."location"(id) ON DELETE RESTRICT ON UPDATE RESTRICT;
ALTER TABLE ui.mission_queues ADD CONSTRAINT mission_queues_mission_id_foreign FOREIGN KEY (mission_id) REFERENCES ui.missions(guid) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.mission_queues ADD CONSTRAINT mission_queues_start_location_id_foreign FOREIGN KEY (start_location_id) REFERENCES production."location"(id) ON DELETE RESTRICT ON UPDATE RESTRICT;


-- ui.mqtt_commands foreign keys

ALTER TABLE ui.mqtt_commands ADD CONSTRAINT mqtt_commands_mqtt_command_type_id_foreign FOREIGN KEY (mqtt_command_type_id) REFERENCES ui.mqtt_command_types(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.mqtt_commands ADD CONSTRAINT mqtt_commands_region_mgmt_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.mqtt_commands ADD CONSTRAINT mqtt_commands_user_id_foreign FOREIGN KEY (user_id) REFERENCES ui.users(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.region_mgmt_user foreign keys

ALTER TABLE ui.region_mgmt_user ADD CONSTRAINT region_mgmt_user_region_mgmt_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.region_mgmt_user ADD CONSTRAINT region_mgmt_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES ui.users(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.remote_management_system_statuses foreign keys

ALTER TABLE ui.remote_management_system_statuses ADD CONSTRAINT remote_management_system_statuses_location_id_foreign FOREIGN KEY (location_id) REFERENCES production."location"(id) ON DELETE CASCADE;
ALTER TABLE ui.remote_management_system_statuses ADD CONSTRAINT remote_management_system_statuses_mission_queue_id_foreign FOREIGN KEY (mission_queue_id) REFERENCES ui.mission_queues(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.turning_points foreign keys

ALTER TABLE ui.turning_points ADD CONSTRAINT turning_points_clean_area_id_foreign FOREIGN KEY (clean_area_id) REFERENCES ui.clean_areas(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.undeploy_locations foreign keys

ALTER TABLE ui.undeploy_locations ADD CONSTRAINT undeploy_locations_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.vertex_configurations foreign keys

ALTER TABLE ui.vertex_configurations ADD CONSTRAINT vertex_configurations_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- ui.vertices foreign keys

ALTER TABLE ui.vertices ADD CONSTRAINT vertices_attach_vertex_id_foreign FOREIGN KEY (attach_vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.vertices ADD CONSTRAINT vertices_region_mgmt_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ui.vertices ADD CONSTRAINT vertices_vertex_type_id_foreign FOREIGN KEY (vertex_type_id) REFERENCES ui.vertex_types(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- project.project_deploy definition

-- Drop table

-- DROP TABLE project.project_deploy;

CREATE TABLE project.project_deploy (
	project_name varchar(100) NOT NULL,
	deploy_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	profile_log json NOT NULL,
	deploy_status int2 NULL,
	deploy_fail_desc text NULL,
	CONSTRAINT project_deploy_pk PRIMARY KEY (project_name)
);


-- project.project_mgmt definition

-- Drop table

-- DROP TABLE project.project_mgmt;

CREATE TABLE project.project_mgmt (
	project_name varchar(100) NOT NULL,
	profile json NOT NULL,
	CONSTRAINT project_mgmt_pk PRIMARY KEY (project_name)
);


-- production.action_note definition

-- Drop table

-- DROP TABLE production.action_note;

CREATE TABLE production.action_note (
	"location" varchar NULL,
	vertex varchar NULL,
	room_name varchar NULL,
	"Time" time NULL,
	building varchar NULL,
	action_value int4 NULL,
	organism_kind varchar NULL
);


-- production.clear_status definition

-- Drop table

-- DROP TABLE production.clear_status;

CREATE TABLE production.clear_status (
	converage_status float4 NULL,
	turn_points int2vector NULL,
	id bigserial NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT clear_status_pkey PRIMARY KEY (id)
);


-- production.door_status definition

-- Drop table

-- DROP TABLE production.door_status;

CREATE TABLE production.door_status (
	update_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	door_id varchar NOT NULL,
	door_status varchar NULL,
	CONSTRAINT door_status_pkey PRIMARY KEY (door_id)
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    production.door_status for each row execute function ui.notify_event();


-- production.elevator_status definition

-- Drop table

-- DROP TABLE production.elevator_status;

CREATE TABLE production.elevator_status (
	update_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	elevator_id varchar NOT NULL,
	elevator_door_status varchar NULL,
	elevator_status varchar NULL,
	elevator_position varchar NULL,
	elevator_authorization_state bool NULL DEFAULT false
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    production.elevator_status for each row execute function ui.notify_event();


-- production.station_status definition

-- Drop table

-- DROP TABLE production.station_status;

CREATE TABLE production.station_status (
	station_id varchar NOT NULL,
	station_status varchar NULL,
	CONSTRAINT station_status_pkey PRIMARY KEY (station_id)
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    production.station_status for each row execute function ui.notify_event();


-- production.sweep definition

-- Drop table

-- DROP TABLE production.sweep;

CREATE TABLE production.sweep (
	update_ts time NULL,
	clear_left float8 NULL,
	clear_right float8 NULL,
	detection_points float8 NULL,
	id bigserial NOT NULL,
	CONSTRAINT sweep_pkey PRIMARY KEY (id)
);


-- production.system_event definition

-- Drop table

-- DROP TABLE production.system_event;

CREATE TABLE production.system_event (
	receive_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	system_type varchar NULL,
	system_id varchar NULL,
	event_code varchar NULL,
	event_name varchar NULL,
	event_level varchar NOT NULL DEFAULT 'info'::character varying,
	"comment" varchar NULL
);


-- production.vehicle_event definition

-- Drop table

-- DROP TABLE production.vehicle_event;

CREATE TABLE production.vehicle_event (
	receive_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	system_type varchar NOT NULL DEFAULT 'AMR'::character varying,
	system_id varchar NULL,
	event_code varchar NULL,
	event_name varchar NULL,
	event_level varchar NOT NULL DEFAULT 'info'::character varying,
	vehicle_status varchar NULL,
	vehicle_location varchar NULL,
	vehicle_coordinate varchar NULL,
	vehicle_mission_uuid varchar NULL,
	"comment" varchar NULL
);


-- production.charger_status definition

-- Drop table

-- DROP TABLE production.charger_status;

CREATE TABLE production.charger_status (
	update_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	charging_station_id varchar NOT NULL,
	booking varchar NULL,
	booking_owner varchar NULL,
	charging_vehicle_id varchar NULL,
	charging_station_status varchar NULL
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    production.charger_status for each row execute function ui.notify_event();


-- production."location" definition

-- Drop table

-- DROP TABLE production."location";

CREATE TABLE production."location" (
	build varchar NULL,
	floors int4 NULL,
	room varchar NULL,
	vertex_name varchar NULL,
	vertex_id int8 NULL,
	id bigserial NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	device_name varchar(255) NULL,
	x numeric(15, 2) NULL,
	y numeric(15, 2) NULL,
	x_px float8 NULL,
	y_px float8 NULL,
	bar_code varchar(255) NULL,
	map_id varchar(255) NULL,
	CONSTRAINT location_pkey PRIMARY KEY (id)
);


-- production.micro_organism definition

-- Drop table

-- DROP TABLE production.micro_organism;

CREATE TABLE production.micro_organism (
	bar_code varchar NULL,
	device_name varchar NULL,
	organism_kind varchar NULL,
	organism_value int4 NULL,
	room_name varchar NULL,
	id bigserial NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	location_id int8 NULL,
	"Time" timestamp(0) NULL,
	is_serious int2 NOT NULL DEFAULT '0'::smallint,
	score float8 NOT NULL DEFAULT '0'::double precision,
	color varchar(255) NULL,
	"source" int2 NOT NULL DEFAULT '1'::smallint,
	x numeric(8, 2) NULL,
	y numeric(8, 2) NULL,
	remote_management_system_status_id int8 NULL,
	CONSTRAINT micro_organism_pkey PRIMARY KEY (id)
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    production.micro_organism for each row execute function ui.notify_event();


-- production.parking_lot_status definition

-- Drop table

-- DROP TABLE production.parking_lot_status;

CREATE TABLE production.parking_lot_status (
	parking_lot_id varchar NOT NULL,
	parking_lot_status varchar NOT NULL DEFAULT 'EMPTY'::character varying,
	booking_vehicle_id varchar NULL,
	occupied_vehicle_id varchar NULL,
	update_ts timestamp NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table Triggers

create trigger device_trigger after
insert
    or
delete
    or
update
    on
    production.parking_lot_status for each row execute function ui.notify_event();


-- production.room_environment definition

-- Drop table

-- DROP TABLE production.room_environment;

CREATE TABLE production.room_environment (
	temperature float4 NULL,
	humidity float4 NULL,
	pressure_difference float4 NULL,
	room_name varchar NULL,
	test_points int4 NULL,
	region_mgmt_id int8 NOT NULL,
	id bigserial NOT NULL,
	created_at timestamp(0) NULL,
	updated_at timestamp(0) NULL,
	CONSTRAINT room_environment_pkey PRIMARY KEY (id)
);


-- production.vehicle_status definition

-- Drop table

-- DROP TABLE production.vehicle_status;

CREATE TABLE production.vehicle_status (
	update_ts time NULL,
	vehicle_id varchar NULL,
	vehicle_type varchar NULL,
	vehicle_location varchar(255) NULL,
	water_box_status varchar NULL,
	spray_status varchar NULL,
	mopping_motor_status varchar NULL,
	air_laser_sensor_status int2vector NULL,
	depth_camera_status int2vector NULL,
	pipe_import_status int2 NULL,
	sweep_mode_status varchar NULL,
	vertex_id int8 NULL,
	deploy_status int2 NULL,
	deploy_fail_reason text NULL
);


-- production.charger_status foreign keys

ALTER TABLE production.charger_status ADD CONSTRAINT charger_status_fk FOREIGN KEY (charging_station_id) REFERENCES configure.charger_mgmt(charging_station_id) ON DELETE CASCADE ON UPDATE CASCADE;


-- production."location" foreign keys

ALTER TABLE production."location" ADD CONSTRAINT location_map_id_foreign FOREIGN KEY (map_id) REFERENCES ui.maps(guid) ON DELETE CASCADE;
ALTER TABLE production."location" ADD CONSTRAINT location_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- production.micro_organism foreign keys

ALTER TABLE production.micro_organism ADD CONSTRAINT micro_organism_location_id_foreign FOREIGN KEY (location_id) REFERENCES production."location"(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE production.micro_organism ADD CONSTRAINT micro_organism_remote_management_system_status_id_foreign FOREIGN KEY (remote_management_system_status_id) REFERENCES ui.remote_management_system_statuses(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- production.parking_lot_status foreign keys

ALTER TABLE production.parking_lot_status ADD CONSTRAINT parking_lot_status_booking_vehicle_id_fk FOREIGN KEY (booking_vehicle_id) REFERENCES configure.vehicle_mgmt(vehicle_id);
ALTER TABLE production.parking_lot_status ADD CONSTRAINT parking_lot_status_occupied_vehicle_id_fk FOREIGN KEY (occupied_vehicle_id) REFERENCES configure.vehicle_mgmt(vehicle_id);


-- production.room_environment foreign keys

ALTER TABLE production.room_environment ADD CONSTRAINT room_environment_newtable_region_id_foreign FOREIGN KEY (region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE CASCADE ON UPDATE CASCADE;


-- production.vehicle_status foreign keys

ALTER TABLE production.vehicle_status ADD CONSTRAINT vehicle_status_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE SET NULL ON UPDATE SET NULL;

-- configure.acceptance_grades definition

-- Drop table

-- DROP TABLE configure.acceptance_grades;

CREATE TABLE configure.acceptance_grades (
	id serial4 NOT NULL,
	organism_kind varchar NULL,
	"action" int4 NULL,
	warn int4 NULL,
	"general" int4 NULL,
	normal int4 NULL,
	created_at timestamp NULL,
	updated_at timestamp NULL,
	is_default int2 NOT NULL DEFAULT '0'::smallint,
	grade bpchar(1) NOT NULL DEFAULT 'A'::bpchar,
	CONSTRAINT acceptance_grades_pkey PRIMARY KEY (id)
);


-- configure.charger_mgmt definition

-- Drop table

-- DROP TABLE configure.charger_mgmt;

CREATE TABLE configure.charger_mgmt (
	charging_station_id varchar NOT NULL,
	charging_station_location varchar NULL,
	prefer_vehicle varchar NOT NULL,
	CONSTRAINT charging_station_mgmt_pk PRIMARY KEY (charging_station_id)
);


-- configure.parameter_mgmt definition

-- Drop table

-- DROP TABLE configure.parameter_mgmt;

CREATE TABLE configure.parameter_mgmt (
	"key" varchar NOT NULL,
	value varchar NOT NULL,
	module_id varchar NULL,
	update_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- configure.scheduled_mission definition

-- Drop table

-- DROP TABLE configure.scheduled_mission;

CREATE TABLE configure.scheduled_mission (
	create_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	mission_id varchar(256) NOT NULL,
	mission_type varchar NOT NULL,
	system_id varchar NOT NULL,
	years varchar NULL DEFAULT '*'::character varying,
	"month" varchar NULL DEFAULT '*'::character varying,
	week varchar NULL DEFAULT '*'::character varying,
	"day" varchar NULL DEFAULT '*'::character varying,
	hours varchar NULL DEFAULT '*'::character varying,
	minutes varchar NULL DEFAULT '*'::character varying,
	"parameter" varchar NULL
);


-- configure.station_scheduler definition

-- Drop table

-- DROP TABLE configure.station_scheduler;

CREATE TABLE configure.station_scheduler (
	"index" int4 NULL,
	station_id varchar NULL,
	vertex_name varchar NULL,
	station_group varchar NULL,
	is_load bool NOT NULL DEFAULT false,
	id bigserial NOT NULL,
	CONSTRAINT station_scheduler_pkey PRIMARY KEY (id)
);


-- configure.vendor_mgmt definition

-- Drop table

-- DROP TABLE configure.vendor_mgmt;

CREATE TABLE configure.vendor_mgmt (
	vendor varchar(256) NOT NULL,
	vendor_vat varchar(256) NOT NULL,
	vendor_support text NULL,
	deleted_at timestamp(0) NULL,
	CONSTRAINT vendor_mgmt_pk PRIMARY KEY (vendor)
);
CREATE UNIQUE INDEX vendor_mgmt_vendor_vat_uindex ON configure.vendor_mgmt USING btree (vendor_vat);


-- configure.object_mgmt definition

-- Drop table

-- DROP TABLE configure.object_mgmt;

CREATE TABLE configure.object_mgmt (
	obj_uid varchar(256) NOT NULL,
	obj_id varchar(256) NOT NULL,
	create_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	update_ts timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	object_class varchar(256) NOT NULL,
	vendor varchar(256) NOT NULL,
	CONSTRAINT object_mgmt_pkey PRIMARY KEY (obj_uid),
	CONSTRAINT object_mgmt_uk UNIQUE (obj_id),
	CONSTRAINT object_mgmt_vendor_mgmt_vendor_fk FOREIGN KEY (vendor) REFERENCES configure.vendor_mgmt(vendor) ON DELETE CASCADE ON UPDATE CASCADE
);


-- configure.object_port_mgmt definition

-- Drop table

-- DROP TABLE configure.object_port_mgmt;

CREATE TABLE configure.object_port_mgmt (
	obj_id varchar(256) NOT NULL,
	obj_port_id varchar(256) NOT NULL,
	obj_port_index int4 NULL,
	prefer_obj_port_id varchar NULL,
	CONSTRAINT object_port_mgmt_pk PRIMARY KEY (obj_port_id),
	CONSTRAINT object_port_mgmt_object_mgmt_obj_id_fk FOREIGN KEY (obj_id) REFERENCES configure.object_mgmt(obj_id) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE UNIQUE INDEX object_port_mgmt_obj_port_id_uindex ON configure.object_port_mgmt USING btree (obj_port_id);


-- configure.vehicle_mgmt definition

-- Drop table

-- DROP TABLE configure.vehicle_mgmt;

CREATE TABLE configure.vehicle_mgmt (
	vehicle_id varchar(256) NOT NULL,
	carrier_class varchar(256) NULL,
	slot_num int4 NULL,
	battery_threshold_full int4 NOT NULL DEFAULT 80,
	battery_threshold_high int4 NOT NULL DEFAULT 50,
	battery_threshold_low int4 NOT NULL DEFAULT 20,
	macaddr macaddr NULL,
	ipaddr cidr NULL,
	vehicle_type varchar NOT NULL,
	width_meter float8 NOT NULL DEFAULT 1.3,
	length_meter float8 NOT NULL DEFAULT 0.9,
	chargeable int2 NOT NULL DEFAULT '0'::smallint,
	load_unload_timeout_min int4 NOT NULL DEFAULT 3000,
	"group" varchar NULL,
	assign_switch varchar NULL,
	low_speed float8 NULL,
	normal_speed float8 NULL,
	high_speed float8 NULL,
	front_safe_distance_value float8 NULL,
	signal_tower_sound_state varchar(255) NOT NULL DEFAULT 'normal'::character varying,
	color varchar(255) NOT NULL,
	stoppable_second int2 NOT NULL DEFAULT '5'::smallint,
	position_x float8 NULL,
	position_y float8 NULL,
	theta int2 NULL,
	CONSTRAINT vehicle_mgmt_pk PRIMARY KEY (vehicle_id),
	CONSTRAINT vehicle_mgmt_object_mgmt_obj_id_fk FOREIGN KEY (vehicle_id) REFERENCES configure.object_mgmt(obj_id) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE UNIQUE INDEX vehicle_mgmt_ip_uindex ON configure.vehicle_mgmt USING btree (ipaddr);
CREATE UNIQUE INDEX vehicle_mgmt_mac_uindex ON configure.vehicle_mgmt USING btree (macaddr);


-- configure.elevator_mgmt definition

-- Drop table

-- DROP TABLE configure.elevator_mgmt;

CREATE TABLE configure.elevator_mgmt (
	elevator_id varchar NOT NULL,
	vertex_name varchar NULL,
	prefer_vehicle varchar NULL,
	"enable" bool NULL DEFAULT true,
	macaddr macaddr NULL,
	ipaddr cidr NULL,
	CONSTRAINT elevator_mgmt_pkey PRIMARY KEY (elevator_id),
	CONSTRAINT elevator_mgmt_elevator_id_foreign FOREIGN KEY (elevator_id) REFERENCES configure.object_mgmt(obj_id) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT elevator_mgmt_fk FOREIGN KEY (elevator_id) REFERENCES configure.object_mgmt(obj_id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- configure.equipment_mgmt definition

-- Drop table

-- DROP TABLE configure.equipment_mgmt;

CREATE TABLE configure.equipment_mgmt (
	equipment_id varchar(256) NOT NULL,
	"enable" bool NULL DEFAULT true,
	macaddr macaddr NULL,
	ipaddr cidr NULL,
	CONSTRAINT equipment_mgmt_pk PRIMARY KEY (equipment_id),
	CONSTRAINT equipment_mgmt_object_mgmt_obj_id_fk FOREIGN KEY (equipment_id) REFERENCES configure.object_mgmt(obj_id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- configure.door_mgmt definition

-- Drop table

-- DROP TABLE configure.door_mgmt;

CREATE TABLE configure.door_mgmt (
	door_id varchar NOT NULL,
	edge_name varchar NULL,
	"enable" bool NULL DEFAULT true,
	prefer_vehicle varchar NULL,
	door_status varchar NULL,
	macaddr macaddr NOT NULL,
	ipaddr cidr NOT NULL,
	edge_id int8 NULL,
	CONSTRAINT door_mgmt_pkey PRIMARY KEY (door_id)
);


-- configure.parking_lot_mgmt definition

-- Drop table

-- DROP TABLE configure.parking_lot_mgmt;

CREATE TABLE configure.parking_lot_mgmt (
	parking_lot_id varchar NOT NULL,
	vertex_name varchar NULL,
	prefer_vehicle varchar NULL,
	"attribute" varchar NULL,
	"enable" bool NULL DEFAULT true,
	vertex_id int8 NULL
);


-- configure.region_mgmt definition

-- Drop table

-- DROP TABLE configure.region_mgmt;

CREATE TABLE configure.region_mgmt (
	region varchar(256) NOT NULL,
	created_at timestamp(0) NULL,
	img_height int2 NULL,
	img_width int2 NULL,
	mm int4 NOT NULL DEFAULT 0,
	updated_at timestamp(0) NULL,
	project_name varchar(255) NULL,
	undeploy_background_file_md5 varchar(255) NULL,
	edit_user_id int8 NULL,
	edited_at timestamp(0) NULL,
	resolution float8 NOT NULL DEFAULT '1'::double precision,
	origin_x float8 NOT NULL DEFAULT '0'::double precision,
	origin_y float8 NOT NULL DEFAULT '0'::double precision,
	yaw int2 NULL,
	route_id int8 NULL,
	origin_start_direction int2 NOT NULL DEFAULT '1'::smallint,
	undeploy_route_file_md5 varchar(32) NULL,
	deploy_background_file_md5 varchar(32) NULL,
	deploy_route_file_md5 varchar(32) NULL,
	id bigserial NOT NULL,
	x_px int4 NULL,
	y_px int4 NULL,
	project_id int8 NULL,
	cad_width int2 NULL,
	cad_height int2 NULL,
	cleanliness_grade varchar(1) NULL,
	floors int4 NULL,
	floor_region_mgmt_id int8 NULL,
	CONSTRAINT region_mgmt_pkey PRIMARY KEY (id)
);


-- configure.station_mgmt definition

-- Drop table

-- DROP TABLE configure.station_mgmt;

CREATE TABLE configure.station_mgmt (
	station_id varchar NOT NULL,
	vertex_name varchar NULL,
	device_name varchar NULL,
	station_group varchar NULL,
	"enable" bool NULL DEFAULT true,
	bypass bool NULL DEFAULT false,
	vertex_id int8 NULL,
	CONSTRAINT station_mgmt_pkey PRIMARY KEY (station_id)
);


-- configure.door_mgmt foreign keys

ALTER TABLE configure.door_mgmt ADD CONSTRAINT door_mgmt_edge_id_foreign FOREIGN KEY (edge_id) REFERENCES ui.edges(id) ON DELETE SET NULL ON UPDATE SET NULL;
ALTER TABLE configure.door_mgmt ADD CONSTRAINT door_mgmt_fk FOREIGN KEY (door_id) REFERENCES configure.object_mgmt(obj_id) ON DELETE CASCADE ON UPDATE CASCADE;


-- configure.parking_lot_mgmt foreign keys

ALTER TABLE configure.parking_lot_mgmt ADD CONSTRAINT parking_lot_mgmt_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE SET NULL ON UPDATE SET NULL;


-- configure.region_mgmt foreign keys

ALTER TABLE configure.region_mgmt ADD CONSTRAINT region_mgmt_edit_user_id_foreign FOREIGN KEY (edit_user_id) REFERENCES ui.users(id) ON DELETE SET NULL ON UPDATE SET NULL;
ALTER TABLE configure.region_mgmt ADD CONSTRAINT region_mgmt_floor_region_id_foreign FOREIGN KEY (floor_region_mgmt_id) REFERENCES configure.region_mgmt(id) ON DELETE SET NULL ON UPDATE SET NULL;
ALTER TABLE configure.region_mgmt ADD CONSTRAINT region_mgmt_project_id_foreign FOREIGN KEY (project_id) REFERENCES ui.projects(id) ON UPDATE CASCADE;


-- configure.station_mgmt foreign keys

ALTER TABLE configure.station_mgmt ADD CONSTRAINT station_mgmt_vertex_id_foreign FOREIGN KEY (vertex_id) REFERENCES ui.vertices(id) ON DELETE SET NULL ON UPDATE SET NULL;


-- class_reference.equipment_class definition

-- Drop table

-- DROP TABLE class_reference.equipment_class;

CREATE TABLE class_reference.equipment_class (
	equipment_class varchar(256) NOT NULL,
	"enable" int2 NOT NULL DEFAULT '1'::smallint,
	"name" varchar(30) NULL,
	CONSTRAINT equipment_class_mgmt_pk PRIMARY KEY (equipment_class)
);


-- class_reference.object_class definition

-- Drop table

-- DROP TABLE class_reference.object_class;

CREATE TABLE class_reference.object_class (
	object_class varchar(256) NOT NULL,
	"enable" int2 NOT NULL DEFAULT '1'::smallint,
	"name" varchar(30) NULL
);

-- acceptance_grade.contanct_particles definition

-- Drop table

-- DROP TABLE acceptance_grade.contanct_particles;

CREATE TABLE acceptance_grade.contanct_particles (
	"action" int4 NULL,
	warn int4 NULL,
	"general" int4 NULL,
	normal int4 NULL
);


-- acceptance_grade.falling_particles definition

-- Drop table

-- DROP TABLE acceptance_grade.falling_particles;

CREATE TABLE acceptance_grade.falling_particles (
	"action" int4 NULL,
	warn int4 NULL,
	"general" int4 NULL,
	normal int4 NULL
);


-- acceptance_grade.micropaticle_particles definition

-- Drop table

-- DROP TABLE acceptance_grade.micropaticle_particles;

CREATE TABLE acceptance_grade.micropaticle_particles (
	"action" int4 NULL,
	warn int4 NULL,
	"general" int4 NULL,
	normal int4 NULL
);


-- acceptance_grade.suspended_particles definition

-- Drop table

-- DROP TABLE acceptance_grade.suspended_particles;

CREATE TABLE acceptance_grade.suspended_particles (
	"action" int4 NULL,
	warn int4 NULL,
	"general" int4 NULL,
	normal int4 NULL
);