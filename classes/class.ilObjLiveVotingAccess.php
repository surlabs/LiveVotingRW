<?php
declare(strict_types=1);
/**
 * This file is part of the LiveVoting Repository Object plugin for ILIAS.
 * This plugin allows to create real time votings within ILIAS.
 *
 * The LiveVoting Repository Object plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "LiveVoting" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/LiveVoting
 *
 * If you need support, please contact the maintainer of this software at:
 * info@surlabs.es
 *
 */

/**
 * Class ilObjLiveVotingAccess
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class ilObjLiveVotingAccess extends ilObjectPluginAccess
{
    public static function hasWriteAccess($ref_id = null, $user_id = null): bool
    {
        return self::hasAccess('write', $ref_id, $user_id);
    }

    protected static function hasAccess(string $permission, $ref_id = null, $user_id = null): bool
    {
        global $DIC;
        $ref_id = (int)$ref_id ?: (int)$_GET['ref_id'];
        $user_id = $user_id ?: $DIC->user()->getId();

        return $DIC->access()->checkAccessOfUser($user_id, $permission, '', $ref_id);
    }

}