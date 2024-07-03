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
use ilButtonToSplitButtonMenuItemAdapter;
use ilCtrlException;
use ilException;
use ilGlyphGUI;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use iljQueryUtil;
use ilLinkButton;
use ilLiveVotingPlugin;
use ilObjLiveVotingGUI;
use ilSetting;
use ilSplitButtonGUI;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use JsonException;
use LiveVoting\legacy\LiveVotingQRModalGUI;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingPlayer;
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
                if (isset($this->liveVoting->getQuestions()[0])) {
                    $this->liveVoting->getPlayer()->prepareStart($this->liveVoting->getQuestions()[0]->getId());
                } else {
                    return $this->renderer->render($this->factory->messageBox()->failure($this->pl->txt("player_msg_no_start_2")));
                }

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
        $keyboard->active = $this->liveVoting->getPlayer()->isKeyboardActive();
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

    /**
     * @throws LiveVotingException
     */
    public function showVoting(){
        global $DIC;
        $liveVoting = $this->liveVoting;
        $liveVoting->regenerateOptionSorting();
        $liveVoting->getPlayer()->setStatus(LiveVotingPlayer::STAT_RUNNING);
        $liveVoting->getPlayer()->freeze();

        $param_manager = ParamManager::getInstance();

        if ($voting_id = $param_manager->getVoting()) {
            $liveVoting->getPlayer()->setActiveVoting($voting_id);
            $liveVoting->getPlayer()->save();
        }

        try {
            $this->initToolbarDuringVoting();
        } catch (JsonException|ilCtrlException $e) {
        }

        //TODO: Implementar este modal
         //$modal = xlvoQRModalGUI::getInstanceFromVotingConfig($this->manager->getVotingConfig())->getHTML();
         //$this->setContent($modal . $this->getPlayerHTML());
         //$this->handlePreview();

        $DIC->ui()->mainTemplate()->setVariable("PLAYER_CONTENT", "HOLA");
    }

    /**
     * @throws ilCtrlException
     * @throws JsonException
     */
    protected function initToolbarDuringVoting()
    {
        global $DIC;
        // Freeze
        $suspendButton = ilLinkButton::getInstance();
        $suspendButton->addCSSClass('btn-warning');
        $suspendButton->setCaption($DIC->ui()->renderer()->render($DIC->ui()->factory()->symbol()->glyph()->next()) . $this->pl->txt('freeze'), false);
        $suspendButton->setUrl('#');
        $suspendButton->setId('btn-freeze');
        $DIC->toolbar()->addButtonInstance($suspendButton);

        // Unfreeze
        $playButton = ilLinkButton::getInstance();
        $playButton->setPrimary(true);
        $playButton->setCaption($DIC->ui()->renderer()->render($DIC->ui()->factory()->symbol()->glyph()->next()) . $this->pl->txt('unfreeze'), false);
        $playButton->setUrl('#');
        $playButton->setId('btn-unfreeze');

        $split = ilSplitButtonGUI::getInstance();
        $split->setDefaultButton($playButton);
        foreach (array(10, 30, 90, 120, 180, 240, 300) as $seconds) {
            $cd = ilLinkButton::getInstance();
            $cd->setUrl('#');
            $cd->setCaption($seconds . ' ' . $this->pl->txt('player_seconds'), false);
            $cd->setOnClick("xlvoPlayer.countdown(event, $seconds);");
            $ilSplitButtonMenuItem = new ilButtonToSplitButtonMenuItemAdapter($cd);
            $split->addMenuItem($ilSplitButtonMenuItem);
        }

        $DIC->toolbar()->addStickyItem($split);

        // Hide
        $suspendButton = ilLinkButton::getInstance();
        $suspendButton->setCaption($this->pl->txt('hide_results'), false);
        $suspendButton->setUrl('#');
        $suspendButton->setId('btn-hide-results');
        $DIC->toolbar()->addButtonInstance($suspendButton);

        // Show
        $suspendButton = ilLinkButton::getInstance();
        $suspendButton->setCaption($this->pl->txt('show_results'), false);
        $suspendButton->setUrl('#');
        $suspendButton->setId('btn-show-results');
        $DIC->toolbar()->addButtonInstance($suspendButton);

        // Reset
        $suspendButton = ilLinkButton::getInstance();
        $suspendButton->setCaption(ilGlyphGUI::get('remove') . $this->pl->txt('reset'), false);
        $suspendButton->setUrl('#');
        $suspendButton->setId('btn-reset');
        $DIC->toolbar()->addButtonInstance($suspendButton);

        //
        //
        $DIC->toolbar()->addSeparator();
        //
        //
        $param_manager = ParamManager::getInstance();

        if (!$param_manager->isPpt()) {
            // PREV
            $suspendButton = ilLinkButton::getInstance();
            $suspendButton->setDisabled(true);
            $suspendButton->setUrl($DIC->ctrl()->getLinkTarget($this, 'previous'));
            $suspendButton->setCaption(ilGlyphGUI::get(ilGlyphGUI::PREVIOUS), false);
            $suspendButton->setId('btn-previous');
            $DIC->toolbar()->addButtonInstance($suspendButton);

            // NEXT
            $suspendButton = ilLinkButton::getInstance();
            $suspendButton->setDisabled(true);
            $suspendButton->setCaption(ilGlyphGUI::get(ilGlyphGUI::NEXT), false);
            $suspendButton->setUrl($DIC->ctrl()->getLinkTarget($this, 'next'));
            $suspendButton->setId('btn-next');
            $DIC->toolbar()->addButtonInstance($suspendButton);
        }

        // Votings
        if (!$param_manager->isPpt()) {
            $current_selection_list = $this->getVotingSelectionList();
            $DIC->toolbar()->addText($current_selection_list->getHTML());
        }

        $DIC->toolbar()->addSeparator();

        $player = $this->liveVoting->getPlayer();

        // Fullscreen
        if ($player->isFullScreen() && !$param_manager->isPpt()) {
            $suspendButton = ilLinkButton::getInstance();
            $suspendButton->setCaption(ilGlyphGUI::get('fullscreen'), false);
            $suspendButton->setUrl('#');
            $suspendButton->setId('btn-start-fullscreen');
            $DIC->toolbar()->addButtonInstance($suspendButton);

            $suspendButton = ilLinkButton::getInstance();
            $suspendButton->setCaption(ilGlyphGUI::get('resize-small'), false);
            $suspendButton->setUrl('#');
            $suspendButton->setId('btn-close-fullscreen');
            $DIC->toolbar()->addButtonInstance($suspendButton);
        }

        // END
        $suspendButton = ilLinkButton::getInstance();
        $suspendButton->setCaption(ilGlyphGUI::get('stop') . $this->pl->txt('terminate'), false);
        $suspendButton->setUrl($DIC->ctrl()->getLinkTarget(new ilObjLiveVotingGUI(), 'terminate'));
        $suspendButton->setId('btn-terminate');
        $DIC->toolbar()->addButtonInstance($suspendButton);
/*        if (false) {
            // PAUSE PULL
            $suspendButton = ilLinkButton::getInstance();
            $suspendButton->setCaption('Toogle Pulling', false);
            $suspendButton->setUrl('#');
            $suspendButton->setId('btn-toggle-pull');
            $DIC->toolbar()->addButtonInstance($suspendButton);
        }*/
    }

    /**
     * @param bool $async
     *
     * @return ilAdvancedSelectionListGUI
     * @throws ilCtrlException
     */
    protected function getVotingSelectionList(bool $async = true): ilAdvancedSelectionListGUI
    {
        global $DIC;
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setItemLinkClass('xlvo-preview');
        $current_selection_list->setListTitle($this->pl->txt('voting_list'));
        $current_selection_list->setId('xlvo_select');
        $current_selection_list->setTriggerEvent('xlvo_voting');
        $current_selection_list->setUseImages(false);
        /**
         * @var liveVoting[] $votings
         */
        foreach ($this->liveVoting->getQuestions() as $voting) {
            $id = $voting->getId();
            $t = $voting->getTitle();
            $DIC->ctrl()->setParameterByClass(ilObjLiveVotingGUI::class, 'xlvo_voting', $id);

            $target = $DIC->ctrl()->getLinkTarget(new ilObjLiveVotingGUI(), 'startPlayer');
            if ($async) {
                $current_selection_list->addItem($t, $id, $target, '', '', '', '', false, 'xlvoPlayer.open(' . $id . ')');
            } else {
                $current_selection_list->addItem($t, $id, $target);
            }
        }

        return $current_selection_list;
    }

    /**
     * @throws ilException
     * @throws ilTemplateException|LiveVotingException
     */
    public function getPlayerHTML(bool $inner = false): string
    {
        $liveVotingDisplayPlayerUI = new LiveVotingDisplayPlayerUI($this->liveVoting);

        return $liveVotingDisplayPlayerUI->getHTML($inner);
    }

}