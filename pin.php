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

use LiveVoting\Voting\LiveVotingParticipant;

require_once __DIR__ . '/../../../../../../../libs/composer/vendor/autoload.php';
/*require_once __DIR__ . "/vendor/autoload.php";*/
require_once "dir.php";

/*use LiveVoting\Conf\xlvoConf;
use LiveVoting\Context\InitialisationManager;
use LiveVoting\Context\Param\ParamManager;
use LiveVoting\Context\xlvoContext;
use LiveVoting\Pin\xlvoPin;
use srag\DIC\LiveVoting\DICStatic;*/

try {
    $pin = trim(filter_input(INPUT_GET, 'xlvo_pin'), "/");
    //TODO: Carga de bootstrap
    LiveVotingParticipant::getInstance()->setIdentifier(session_id())->setType(2);



} catch (Throwable $ex) {
    echo $ex->getMessage() . "<br /><br /><a href='/'>back</a>";
}
