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

namespace LiveVoting\legacy;

use ilLiveVotingPlugin;
use ilModalGUI;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;

/**
 * Class xlvoQRModalGUI
 *
 * @package LiveVoting\Player\QR
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class LiveVotingQRModalGUI extends ilModalGUI
{

    /**
     * @param LiveVoting $liveVoting
     *
     * @return LiveVotingQRModalGUI
     * @throws LiveVotingException
     */
    public static function getInstanceFromLiveVoting(LiveVoting $liveVoting): LiveVotingQRModalGUI
    {
        LiveVotingJs::getInstance()->name('Modal')->addSettings(array('id' => 'QRModal'))->category('Player')->init()->setRunCode();

        $ilModalGUI = new self();
        $ilModalGUI->setId('QRModal');
        $ilModalGUI->setHeading(vsprintf(ilLiveVotingPlugin::getInstance()->txt("player_pin"), [$liveVoting->getPin()]));

        $param_manager = ParamManager::getInstance();

        $modal_body = '<span class="label label-default xlvo-label-url resize">' . $liveVoting->getShortLink($param_manager->getRefId()) . '</span>'; //
        $modal_body .= '<img id="xlvo-modal-qr" src="' . $liveVoting->getQRCode($param_manager->getRefId(), 720) . '">';

        $ilModalGUI->setBody($modal_body);
        $ilModalGUI->setType(ilModalGUI::TYPE_LARGE);

        return $ilModalGUI;
    }
}
