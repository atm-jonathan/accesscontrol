-- Copyright (C) 2024  Philippe GRAND <contact@atoo-net.com>
ALTER TABLE llx_c_sigrebadge_boxes_def ADD UNIQUE INDEX uk_boxes_def (file, entity, note);
