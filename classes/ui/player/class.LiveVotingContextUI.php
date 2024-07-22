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

namespace LiveVoting\player;

use ilContextTemplate;

class LiveVotingContextUI implements ilContextTemplate
{
    /**
     * @return bool
     */
    public static function isSessionMainContext(): bool
    {
        return true;
    }

    /**
     * @param string $httpPath
     * @return string
     */
    public static function modifyHttpPath(string $httpPath): string
    {
        return $httpPath;
    }


    /**
     * @return bool
     */
    public static function supportsRedirects(): bool
    {
        return false;
    }


    /**
     * @return bool
     */
    public static function hasUser(): bool
    {
        return true;
    }


    /**
     * @return bool
     */
    public static function usesHTTP(): bool
    {
        return true;
    }


    /**
     * @return bool
     */
    public static function hasHTML(): bool
    {
        return true;
    }


    /**
     * @return bool
     */
    public static function usesTemplate(): bool
    {
        return true;
    }


    /**
     * @return bool
     */
    public static function initClient(): bool
    {
        return true;
    }


    /**
     * @return bool
     */
    public static function doAuthentication(): bool
    {
        return false;
    }


    /**
     * Check if persistent sessions are supported
     * false for context cli
     */
    public static function supportsPersistentSessions(): bool
    {
        return false;
    }


    /**
     * Check if push messages are supported, see #0018206
     *
     * @return bool
     */
    public static function supportsPushMessages(): bool
    {
        return false;
    }
}