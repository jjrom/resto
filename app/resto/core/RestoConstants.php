<?php
/*
 * Copyright 2023 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

class RestoConstants
{
    // [IMPORTANT] Starting resto 7.x, default routes are defined in RestoRouter class

    // resto version
    const VERSION = '9.3.0';

    /* ============================================================
     *              NEVER EVER TOUCH THESE VALUES
     * ============================================================*/

    // admin user identifier
    const ADMIN_USER_ID = '100';

    // Group identifier for administrator group
    const GROUP_ADMIN_ID = '0';

    // Group identifier for default group (every user is in default group)
    const GROUP_DEFAULT_ID = '100';

    // Separator for hashtags identifiers - should be the same as iTag
    const ITAG_SEPARATOR = ':';

    // Separator for hashtags identifiers - should be the same as iTag
    const CONCEPT_SEPARATOR = '::';

    /* ============================================================ */

    /* ============================================================
     *              DEFINE GROUPS HERE
     * ============================================================*/
    
     const GROUP_SNAPPLANET_ADMIN_ID = '11';

}
