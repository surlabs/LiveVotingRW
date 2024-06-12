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

namespace LiveVoting\UI;

use ilLiveVotingPlugin;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;

/**
 * Class LiveVotingUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 */
class LiveVotingUI
{
    /**
     * @var ilLiveVotingPlugin
     */
    protected ilLiveVotingPlugin $pl;

/*
    public function executeCommand(): void
    {
        GLOBAL $DIC;
        $nextClass = $DIC->ctrl()->getNextClass();
        switch ($nextClass) {
            default:
                $cmd = $DIC->ctrl()->getCmd('showContent');
                $this->{$cmd}();
                break;
        }
    }*/

    /**
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     */
    public function showIndex(): string
    {
        $this->pl = ilLiveVotingPlugin::getInstance();
        $template = new ilTemplate($this->pl->getDirectory()."/templates/default/Player/tpl.start.html", true, true );
        $template->setVariable('PIN',"1234");

        $template->setVariable('QR-CODE', "1234");

        $template->setVariable('SHORTLINK', "TEST");
        $template->setVariable('MODAL', "TEST");
        $template->setVariable("ONLINE_TEXT", "TEST");
        $template->setVariable("ZOOM_TEXT", "TEST");
        return '<div>Hola'.$template->get().'</div>';
    }

}