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

use ilAdvancedSelectionListGUI;
use ilCtrlException;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use iljQueryUtil;
use ilLinkButton;
use ilLiveVotingPlugin;
use ilObjLiveVotingGUI;
use ilSetting;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use JsonException;
use LiveVoting\legacy\LiveVotingQRModalGUI;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;
use stdClass;

/**
 * Class LiveVotingUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 * @ilCtrl_IsCalledBy  LiveVotingUI: ilUIPluginRouterGUI
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
     * @throws ilCtrlException
     * @throws JsonException
     */
    public function showIndex(): string
    {
        global $DIC;

        if ($this->liveVoting->isOnline()) {
            if (!empty($this->liveVoting->getQuestions())) {
                // TODO: Refactor this code to remove deprecated button
                $b = ilLinkButton::getInstance();
                $b->setCaption($this->pl->txt('player_start_voting'), false);
                $b->addCSSClass('xlvo-preview');
                $b->setUrl($DIC->ctrl()->getLinkTargetByClass("ilObjLiveVotingGUI", "startPlayer"));
                $b->setId('btn-start-voting');
                $b->setPrimary(true);
                $DIC->toolbar()->addButtonInstance($b);

                $current_selection_list = $this->getQuestionSelectionList(false);
                $DIC->toolbar()->addText($current_selection_list->getHTML());

                // TODO: Refactor this code to remove deprecated button
                $b2 = ilLinkButton::getInstance();
                $b2->setCaption($this->pl->txt('player_start_voting_and_unfreeze'), false);
                $b2->addCSSClass('xlvo-preview');
                $b2->setUrl($DIC->ctrl()->getLinkTargetByClass("ilObjLiveVotingGUI", "startPlayerAnUnfreeze"));
                $b2->setId('btn-start-voting-unfreeze');
                $DIC->toolbar()->addButtonInstance($b2);

                $template = new ilTemplate($this->pl->getDirectory() . "/templates/default/Player/tpl.start.html", true, true);

                $template->setVariable('PIN', $this->liveVoting->getPin());

                $param_manager = ParamManager::getInstance();

                $template->setVariable('QR-CODE', $this->liveVoting->getQRCode($param_manager->getRefId(), 180));

                $template->setVariable('SHORTLINK', $this->liveVoting->getShortLink($param_manager->getRefId()));

                $template->setVariable('MODAL', LiveVotingQRModalGUI::getInstanceFromLiveVoting($this->liveVoting)->getHTML());
                $template->setVariable("ONLINE_TEXT", vsprintf($this->pl->txt("start_online"), [0]));
                $template->setVariable("ZOOM_TEXT", $this->pl->txt("start_zoom"));

                $js = LiveVotingJs::getInstance()->addSetting("base_url", $DIC->ctrl()->getLinkTargetByClass("ilObjLiveVotingGUI", "", "", true))->name('Player')->init();

                if ($this->liveVoting->isShowAttendees()) {
                    $js->call('updateAttendees');
                    $template->touchBlock('attendees');
                }

                $js->call('handleStartButton');

                return '<div>' . $template->get() . '</div>';
            } else {
                return $this->renderer->render($this->factory->messageBox()->failure($this->pl->txt("player_msg_no_start_2")));
            }
        } else {
            return $this->renderer->render($this->factory->messageBox()->failure($this->pl->txt("player_msg_no_start_1")));
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function getQuestionSelectionList($async = true): ilAdvancedSelectionListGUI
    {
        global $DIC;

        // TODO: Refactor this code to remove deprecated selection list
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setItemLinkClass('xlvo-preview');
        $current_selection_list->setListTitle($this->pl->txt('player_voting_list'));
        $current_selection_list->setId('xlvo_select');
        $current_selection_list->setTriggerEvent('xlvo_voting');
        $current_selection_list->setUseImages(false);


        foreach ($this->liveVoting->getQuestions() as $question) {
            $id = $question->getId();
            $title = $question->getTitle();

            $DIC->ctrl()->setParameterByClass("ilObjLiveVotingGUI", "xlvo_voting", $id);

            $target = $DIC->ctrl()->getLinkTargetByClass("ilObjLiveVotingGUI", "startPlayer");
            if ($async) {
                $current_selection_list->addItem($title, (string) $id, $target, '', '', '', '', false, 'xlvoPlayer.open(' . $id . ')');
            } else {
                $current_selection_list->addItem($title, (string) $id, $target);
            }
        }

        return $current_selection_list;
    }

    /**
     * @param LiveVoting $liveVoting
     * @param ilObjLiveVotingGUI $parent
     * @throws ilCtrlException
     */
    public function initJsAndCss(ilObjLiveVotingGUI $parent) :void
    {
        global $DIC;
        $mathJaxSetting = new ilSetting("MathJax");
        $settings = array(
            'status_running' => 1,
            'identifier'     => 'xvi',
            'use_mathjax'    => (bool) $mathJaxSetting->get("enable"),
            'debug'          => false
        );

        //LiveVotingJS::getInstance()->initMathJax();
        //TODO: Implementar initMathJax


        $keyboard = new stdClass();
        $keyboard->active = $this->liveVoting->isKeyboardActive();
        if ($keyboard->active) {
            $keyboard->toggle_results = 9;
            $keyboard->toggle_freeze = 32;
            $keyboard->previous = 37;
            $keyboard->next = 39;
        }
        $settings['keyboard'] = $keyboard;

        $param_manager = ParamManager::getInstance();

        $settings['xlvo_ppt'] = $param_manager->isPpt();

        iljQueryUtil::initjQuery();


        LiveVotingJS::getInstance()->addLibToHeader('screenfull.js');
        LiveVotingJS::getInstance()->ilias($parent)->addSettings($settings)->name('Player')->addTranslations(array(
            'voting_confirm_reset',
        ))->init()->setRunCode();

        //xlvoJs::getInstance()->ilias($this)->name('PPT')->init()->setRunCode();



        $DIC->ui()->mainTemplate()->addCss($this->pl->getDirectory() . '/templates/css/player.css');
        $DIC->ui()->mainTemplate()->addCss($this->pl->getDirectory() . '/templates/css/bar.css');

        /* xlvoFreeInputResultsGUI::addJsAndCss();
         xlvoCorrectOrderResultsGUI::addJsAndCss();
         xlvoFreeOrderResultsGUI::addJsAndCss();
         xlvoNumberRangeResultsGUI::addJsAndCss();
         xlvoSingleVoteResultsGUI::addJsAndCss();*/
    }


}