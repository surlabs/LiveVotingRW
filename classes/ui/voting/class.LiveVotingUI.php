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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilLiveVotingPlugin;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use LiveVoting\legacy\LiveVotingQRModalGUI;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;

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
    private ilLiveVotingPlugin $pl;

    /**
     * @var LiveVoting
     */
    private LiveVoting $liveVoting;
    /**
     * @var Renderer
     */
    private Renderer $renderer;
    /**
     * @var Factory $factory
     */
    private Factory $factory;

    /**
     * LiveVotingUI constructor.
     */
    public function __construct(LiveVoting $liveVoting)
    {
        global $DIC;

        $this->pl = ilLiveVotingPlugin::getInstance();
        $this->liveVoting = $liveVoting;
        $this->renderer = $DIC->ui()->renderer();
        $this->factory = $DIC->ui()->factory();
    }

    public function executeCommand(): void
    {
        GLOBAL $DIC;

        $cmd = $DIC->ctrl()->getCmd('showIndex');

        $this->{$cmd}();
    }

    /**
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     * @throws LiveVotingException
     */
    public function showIndex(): string
    {
        if ($this->liveVoting->isOnline()) {
            if (!empty($this->liveVoting->getQuestions())) {
                $param_manager = ParamManager::getInstance();

                $template = new ilTemplate($this->pl->getDirectory() . "/templates/default/Player/tpl.start.html", true, true);

                $template->setVariable('PIN', $this->liveVoting->getPin());

                $template->setVariable('QR-CODE', $this->liveVoting->getQRCode($param_manager->getRefId(), 180));

                $template->setVariable('SHORTLINK', $this->liveVoting->getShortLink($param_manager->getRefId()));

                $template->setVariable('MODAL', LiveVotingQRModalGUI::getInstanceFromLiveVoting($this->liveVoting)->getHTML());
                $template->setVariable("ONLINE_TEXT", vsprintf($this->pl->txt("start_online"), [0]));
                $template->setVariable("ZOOM_TEXT", $this->pl->txt("start_zoom"));

                return '<div>' . $template->get() . '</div>';
            } else {
                return $this->renderer->render($this->factory->messageBox()->failure($this->pl->txt("player_msg_no_start_2")));
            }
        } else {
            return $this->renderer->render($this->factory->messageBox()->failure($this->pl->txt("player_msg_no_start_1")));
        }
    }

}