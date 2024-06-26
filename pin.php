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

use ILIAS\DI\Container;

use LiveVoting\platform\ilias\LiveVotingContext;
use LiveVoting\platform\LiveVotingConfig;
use LiveVoting\player\LiveVotingInitialisationUI;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingParticipant;
require_once __DIR__ . '/../../../../../../../libs/composer/vendor/autoload.php';
/*require_once __DIR__ . "/vendor/autoload.php";*/
require_once "dir.php";




try {
    $pin = trim(filter_input(INPUT_GET, 'xlvo_pin'), "/");

    LiveVotingInitialisationUI::init();
    ilInitialisation::initILIAS();
    LiveVotingContext::setContext(1);

    LiveVotingParticipant::getInstance()->setIdentifier(session_id())->setType(2);
    global $DIC;
    dump($DIC);
    exit;
    $DIC->ctrl()->setTargetScript(LiveVotingConfig::getFullApiUrl());

   /* if(!empty($pin)){
        if(LiveVoting::getObjIdFromPin($pin)){
            $param_manager = ParamManager::getInstance();
            $DIC->ctrl()->redirectByClass([ilUIPluginRouterGUI::class, xlvoVoter2GUI::class], xlvoVoter2GUI::CMD_START_VOTER_PLAYER);
        }
    } else {

    }*/



} catch (Throwable $ex) {
    echo $ex->getMessage() . "<br /><br /><a href='/'>back</a>";
}
