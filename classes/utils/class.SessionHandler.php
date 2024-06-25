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
namespace LiveVoting\Session;

use ilLiveVotingPlugin;

/**
 * Class xlvoSessionHandler
 *
 * @package LiveVoting\Session
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */

//TODO: Revisar si esta clase tiene sentido
class SessionHandler
{
    const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;


    /**
     * @param string $save_path
     * @param string $sessionid
     *
     * @return bool
     */
    public function open(string $save_path, string $sessionid): bool
    {
        return true;
    }


    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }


    /**
     * @param string $sessionid
     *
     * @return string
     */
    public function read(string $sessionid): string
    {
        return '';
    }


    /**
     * @param string $sessionid
     * @param string $sessiondata
     *
     * @return bool
     */
    public function write(string $sessionid, string $sessiondata): bool
    {
        return true;
    }


    /**
     * @param int $sessionid
     *
     * @return bool
     */
    public function destroy($sessionid): bool
    {
        return true;
    }


    /**
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc(int $maxlifetime): bool
    {
        return true;
    }
}
