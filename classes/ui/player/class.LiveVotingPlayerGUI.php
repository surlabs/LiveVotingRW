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

use LiveVoting\platform\LiveVotingConfig;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\UI\QuestionsResults\LiveVotingInputResultsGUI;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingPlayer;
use LiveVoting\votings\LiveVotingVoter;

/**
 * Class LiveVotingPlayerGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 *
 * @ilCtrl_isCalledBy LiveVotingPlayerGUI: ilUIPluginRouterGUI, LiveVotingFreeTextPlayerGUI, LiveVotingCorrectOrderPlayerGUI, LiveVotingSingleVotePlayerGUI
 */
class LiveVotingPlayerGUI
{
    /**
     * @var ilLiveVotingPlugin
     */
    private ilLiveVotingPlugin $plugin_object;

    /**
     * @var LiveVoting Object
     */
    private LiveVoting $live_voting;

    /**
     * @var ilTemplate Template for the framework surrounding the voting
     */
    private ilTemplate $voter_template;

    /**
     * @var ilTemplate Template for the voting itself
     */
    private ilTemplate $voting_template;

    /**
     * @throws LiveVotingException
     * @throws ilCtrlException
     */
    public function executeCommand(): void
    {
        global $DIC, $tpl;

        $this->setPluginObject(ilLiveVotingPlugin::getInstance());
        $param_manager = ParamManager::getInstance();

        $pin = $param_manager->getPin();

        if (empty($pin)) {
            $this->requestPin();
            return;
        }

        $this->setLiveVoting(LiveVoting::getLiveVotingFromPin($pin));

        $nextClass = $DIC->ctrl()->getNextClass();

        switch ($nextClass) {
            case '':
                if (!$this->getLiveVoting()->isAnonymous() && (is_null($DIC->user()) || $DIC->user()->getId() == 13 || $DIC->user()->getId() == 0)) {
                    $plugin_path = substr(ilLiveVotingPlugin::getInstance()->getDirectory(), 2);
                    $ilias_base_path = str_replace($plugin_path, '', ILIAS_HTTP_PATH);
                    $login_target = "{$ilias_base_path}goto.php?target=xlvo_1_pin_" . $pin;

                    $DIC->ctrl()->redirectToURL($login_target);
                } else {
                    $cmd = $DIC->ctrl()->getCmd("startVoterPlayer");
                    $this->{$cmd}();
                }

                break;
            default:
                require_once $DIC->ctrl()->lookupClassPath($nextClass);
                $gui = new $nextClass();

                $DIC->ctrl()->forwardCommand($gui);
                break;
        }
    }

    /**
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     * @throws LiveVotingException
     * @throws ilCtrlException|ilException
     */
    protected function startVoterPlayer(): void
    {
        $this->prepareFrameworkTemplate();
        $this->prepareVotingTemplate();

    }

    private function prepareFrameworkTemplate(): void
    {
        global $DIC;

        $DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getDirectory() . '/templates/default/Voter/voter.css', '');
        $DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getDirectory() . '/templates/default/libs/bootstrap-slider.min.css', '');
        $DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getDirectory() . '/templates/default/QuestionTypes/NumberRange/number_range.css', '');
    }

    /**
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     * @throws ilCtrlException|LiveVotingException
     * @throws ilException
     */
    private function prepareVotingTemplate(): void
    {
        global $DIC;

        $tpl_voter_player = new ilTemplate($this->getPluginObject()->getDirectory() . '/templates/default/Voter/tpl.voter_player.html', false, false);

        $this->setVoterPlayerTemplate($tpl_voter_player);

        $DIC->ui()->mainTemplate()->addCss($this->getPluginObject()->getDirectory() . '/templates/default/default.css');

        iljQueryUtil::initjQueryUI();

        LiveVotingJs::getInstance()->initMathJax();

        $t = array('player_seconds');

        $delay = LiveVotingConfig::get('request_frequency');
        if (is_numeric($delay)) {
            $delay = ((float) $delay);
        } else {
            $delay = 1;
        }
        $delay *= 1000;

        $mathJaxSetting = new ilSetting("MathJax");
        $settings = array(
            'use_mathjax' => (bool) $mathJaxSetting->get("enable"),
            'debug'       => false,
            'delay'       => $delay,
        );


        LiveVotingJs::getInstance()->name('Main')->init()->setRunCode();
        LiveVotingJs::getInstance()->api($this, array(IlUIPluginRouterGUI::class))->addSettings($settings)->name('Voter')->addTranslations($t)->init()->setRunCode();
        LiveVotingJs::getInstance()->api($this)->name('FreeInput')->category('QuestionTypes/FreeInput')->init();
        LiveVotingJs::getInstance()->api($this)->name('CorrectOrder')->category('QuestionTypes/CorrectOrder')->init();

        $DIC->ui()->mainTemplate()->addCss(ilLiveVotingPlugin::getInstance()->getDirectory()."/templates/customUI/MultiLineNewInputGUI/css/multi_line_new_input_gui.css");
        $DIC->ui()->mainTemplate()->addJavaScript(ilLiveVotingPlugin::getInstance()->getDirectory()."/templates/customUI/MultiLineNewInputGUI/js/multi_line_new_input_gui.js");
        //LiveVotingJs::getInstance()->api($this)->addLibToHeader(ilLiveVotingPlugin::getInstance()->getDirectory(). '/templates/js/QuestionTypes/FreeInput/xlvoFreeInput.js', true)->setInitCode();


        //Show voting template
        $this->showVotingTemplate();

       // $this->fillVotingTemplate();

/*        //TODO: Este foreach parece necesario. Pendiente implementar.
        foreach (LiveVotingQuestionTypesUI::getActiveTypes() as $type) {
            xlvoQuestionTypesGUI::getInstance($this->manager, $type)->initJS($type == $this->manager->getVoting()->getVotingType());
        }*/

        $player = $this->live_voting->getPlayer();
        //$question = $player->getActiveVotingObject();

        LiveVotingQuestionTypesUI::getInstance($player)->initJS();


    }

    /**
     * @throws ilTemplateException
     */
    public function showVotingTemplate(): void
    {
        global $DIC;

        $DIC->ui()->mainTemplate()->setVariable("PLAYER_CONTENT", $this->getVoterPlayerTemplate()->get());

        $DIC->ui()->mainTemplate()->fillCssFiles();
        $DIC->ui()->mainTemplate()->fillJavaScriptFiles();
        $DIC->ui()->mainTemplate()->fillOnLoadCode();
        $DIC->ui()->mainTemplate()->printToStdout('DEFAULT', false, true);



        //echo $DIC->ui()->mainTemplate()->get();
        //echo $this->getFrameworkTemplate()->get();
        //echo $this->getVotingTemplate()->get();
        //exit;
    }

    /**
     * @throws ilSystemStyleException
     * @throws ilTemplateException
     * @throws LiveVotingException
     * @throws ilException
     */
    public function getHTML(): void
    {
        $tpl_voting = new ilTemplate($this->getPluginObject()->getDirectory() . '/templates/default/Voter/tpl.inner_screen.html', true, true);
        $this->setVotingTemplate($tpl_voting);

        if ($this->getLiveVoting()->getFrozenBehaviour()) {
            $this->getVotingTemplate()->setVariable('TITLE', $this->txt('voter_header_frozen'));
            $this->getVotingTemplate()->setVariable('DESCRIPTION', $this->txt('voter_info_frozen'));
            $this->getVotingTemplate()->setVariable('COUNT', (string)$this->getLiveVoting()->countQuestions());
            $this->getVotingTemplate()->setVariable('POSITION', (string)$this->getLiveVoting()->getQuestionPosition());
            $this->getVotingTemplate()->setVariable('PIN', $this->getLiveVoting()->getPin());
            $this->getVotingTemplate()->setVariable('GLYPH', "pause");
            echo $this->getVotingTemplate()->get();
            exit();
        } else {
            switch ($this->getLiveVoting()->getPlayer()->getStatus()) {
                case LiveVotingPlayer::STAT_STOPPED:
                    $this->getVotingTemplate()->setVariable('TITLE', $this->txt('voter_header_stopped'));
                    $this->getVotingTemplate()->setVariable('DESCRIPTION', $this->txt('voter_info_stopped'));
                    $this->getVotingTemplate()->setVariable('COUNT', (string)$this->getLiveVoting()->countQuestions());
                    $this->getVotingTemplate()->setVariable('POSITION', (string)$this->getLiveVoting()->getQuestionPosition());
                    $this->getVotingTemplate()->setVariable('PIN', $this->getLiveVoting()->getPin());
                    break;
                case LiveVotingPlayer::STAT_RUNNING:
                    $this->getVotingTemplate()->setVariable('TITLE', $this->getLiveVoting()->getPlayer()->getActiveVotingObject()->getTitle());
                    $this->getVotingTemplate()->setVariable('DESCRIPTION', $this->getLiveVoting()->getPlayer()->getActiveVotingObject()->getQuestion());
                    $this->getVotingTemplate()->setVariable('COUNT', (string)$this->getLiveVoting()->countQuestions());
                    $this->getVotingTemplate()->setVariable('POSITION', (string)$this->getLiveVoting()->getQuestionPosition());
                    $this->getVotingTemplate()->setVariable('PIN', $this->getLiveVoting()->getPin());

                    $xlvoQuestionTypesGUI = LiveVotingQuestionTypesUI::getInstance($this->getLiveVoting()->getPlayer());
                    if ($xlvoQuestionTypesGUI->isShowQuestion()) {
                        $this->getVotingTemplate()->setCurrentBlock('question_text');
                        $this->getVotingTemplate()->setVariable('QUESTION_TEXT', $this->live_voting->getPlayer()->getActiveVotingObject()->getQuestion());
                        $this->getVotingTemplate()->parseCurrentBlock();
                    }
                    $this->getVotingTemplate()->setVariable('QUESTION', $xlvoQuestionTypesGUI->getMobileHTML());
                    break;
                case LiveVotingPlayer::STAT_START_VOTING:
                    $this->getVotingTemplate()->setVariable('TITLE', $this->txt('voter_header_start'));
                    $this->getVotingTemplate()->setVariable('DESCRIPTION', $this->txt('voter_info_start'));
                    $this->getVotingTemplate()->setVariable('GLYPH', "Glyph start_voting");
                    break;
                case LiveVotingPlayer::STAT_END_VOTING:
                    $this->getVotingTemplate()->setVariable('TITLE', $this->txt('voter_header_end'));
                    $this->getVotingTemplate()->setVariable('DESCRIPTION', $this->txt('voter_info_end'));;
                    $this->getVotingTemplate()->setVariable('GLYPH', "Glyph end_voting");
                    break;
                case LiveVotingPlayer::STAT_FROZEN:
                    $this->getVotingTemplate()->setVariable('TITLE', $this->txt('voter_header_frozen'));
                    $this->getVotingTemplate()->setVariable('DESCRIPTION', $this->txt('voter_info_frozen'));
                    $this->getVotingTemplate()->setVariable('COUNT', (string)$this->getLiveVoting()->countQuestions());
                    $this->getVotingTemplate()->setVariable('POSITION', (string)$this->getLiveVoting()->getQuestionPosition());
                    $this->getVotingTemplate()->setVariable('PIN', $this->getLiveVoting()->getPin());
                    $this->getVotingTemplate()->setVariable('GLYPH', "Glyph frozen");
                    break;
            }
            echo $this->getVotingTemplate()->get();
            exit();
        }
    }

    public function requestPin(): void
    {
        dump("Cargar el input para meter el PIN");
        exit();
    }

    protected function txt(string $key): string
    {
        return $this->getPluginObject()->txt($key);
    }

    /*
     * Getters and Setters
     */

    public function getPluginObject(): ilLiveVotingPlugin
    {
        return $this->plugin_object;
    }

    public function setPluginObject(ilLiveVotingPlugin $plugin_object): void
    {
        $this->plugin_object = $plugin_object;
    }

    public function getLiveVoting(): LiveVoting
    {
        return $this->live_voting;
    }

    public function setLiveVoting(LiveVoting $live_voting): void
    {
        $this->live_voting = $live_voting;
    }

    public function getVoterPlayerTemplate(): ilTemplate
    {
        return $this->voter_template;
    }

    public function setVoterPlayerTemplate(ilTemplate $framework_template): void
    {
        $this->voter_template = $framework_template;
    }

    public function getVotingTemplate(): ilTemplate
    {
        return $this->voting_template;
    }

    public function setVotingTemplate(ilTemplate $voting_template): void
    {
        $this->voting_template = $voting_template;
    }

    /**
     * @throws LiveVotingException
     * @throws Exception
     */
    protected function getVotingData(): void
    {
        $showAttendees = $this->live_voting->isShowAttendees();

        if($showAttendees) {
            LiveVotingVoter::register($this->live_voting->getPlayer()->getId());
        }

        LiveVotingJs::sendResponse($this->live_voting->getPlayer()->getPlayerDataForVoter());
    }
}