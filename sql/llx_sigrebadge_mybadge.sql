-- Copyright (C) 2024 Philippe GRAND <contact@atoo-net.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_sigrebadge_mybadge(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
	entity INT(11) NOT NULL DEFAULT 1, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	label varchar(255), 
	fk_soc integer,
	fk_product integer DEFAULT NULL, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL,
	date_validation datetime DEFAULT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer,
	fk_user_valid INT(11) DEFAULT NULL,
	codeuid varchar(8),
	prefix varchar(8) DEFAULT NULL, 
	import_key varchar(14), 
	model_pdf varchar(255),
	last_main_doc varchar(255) DEFAULT NULL, 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
