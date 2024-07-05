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
use ilException;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLiveVotingPlugin;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\questions\LiveVotingQuestion;
use LiveVoting\questions\LiveVotingQuestionOption;
use LiveVoting\UI\QuestionsResults\LiveVotingInputResultsGUI;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingVoter;

/**
 * Class LiveVotingDisplayPlayerUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilObjLiveVotingGUI: ilObjPluginGUI
 * @ilCtrl_IsCalledBy  LiveVotingUI: ilUIPluginRouterGUI
 */
class LiveVotingDisplayPlayerUI
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
     * @var ilTemplate
     */
    protected ilTemplate $tpl;
    /**
     * @var int
     */
    protected int $answer_count = 64;
    /**
     * @var ParamManager
     */
    protected ParamManager $manager;


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

        try {
            $this->tpl = new ilTemplate($this->pl->getDirectory() . "/templates/default/Player/tpl.player.html", true, true);
            $DIC->ui()->mainTemplate()->addCss($this->pl->getDirectory() . '/templates/default/default.css');
        } catch (ilSystemStyleException|ilTemplateException $e) {
            //TODO: Mostrar error
        }

    }

    /**
     * @param bool $inner
     * @return string
     * @throws ilTemplateException
     * @throws ilException
     * @throws LiveVotingException
     */
    public function getHTML(bool $inner = false): string
    {
        $this->render();
        $open = '<div id="xlvo-display-player" class="display-player panel panel-primary">';
        $close = '</div>';

        if ($inner) {
            return $this->tpl->get();
        } else {
            return $open . $this->tpl->get() . $close;
        }
    }

    /**
     * @throws ilException
     * @throws LiveVotingException
     */
    protected function render()
    {

        $player = $this->liveVoting->getPlayer();
        $question = $player->getActiveVotingObject();


        $xlvoInputResultGUI = LiveVotingInputResultsGUI::getInstance($player);

        if ($player->isShowResults()) {
            //add result view to player
            $this->tpl->setVariable('OPTION_CONTENT', $xlvoInputResultGUI->getHTML());
        } else {
            //add options to player
            $xlvoOptions = LiveVotingQuestionOption::loadAllOptionsByVotingId($question->getId());

            foreach ($xlvoOptions as $item) {
                $this->addOption($item);
            }
        }

        $this->tpl->setVariable('TITLE', $question->getTitle());
        $this->tpl->setVariable('QUESTION', $question->getQuestion());
        $this->tpl->setVariable('VOTING_ID', $this->liveVoting->getId());
        $this->tpl->setVariable('OBJ_ID', $this->liveVoting->getId());
        $this->tpl->setVariable('FROZEN', $player->isFrozen());
        $this->tpl->setVariable('PIN', $this->liveVoting->getPin());
        if ($this->liveVoting->isShowAttendees()) {
            $this->tpl->setCurrentBlock('attendees');
            $this->tpl->setVariable('ONLINE_TEXT', vsprintf(ilLiveVotingPlugin::getInstance()->txt("start_online"), [LiveVotingVoter::countVoters($player->getId())]));
            $this->tpl->parseCurrentBlock();
        }
        if ($player->isCountDownRunning()) {
            $this->tpl->setCurrentBlock('countdown');
            $cd = $player->remainingCountDown();
            $this->tpl->setVariable('COUNTDOWN', $cd . ' ' . $this->pl->txt('player_seconds'));
            $this->tpl->setVariable('COUNTDOWN_CSS', $player->getCountdownClassname());
            $this->tpl->parseCurrentBlock();
        }

        //parse votes block
        $this->tpl->setVariable('VOTERS_TEXT', vsprintf(ilLiveVotingPlugin::getInstance()->txt("player_voters_description"), [LiveVotingVoter::countVoters($player->getId())]));

        $this->tpl->setVariable('COUNT', $this->liveVoting->countQuestions());
        $this->tpl->setVariable('POSITION', $this->liveVoting->getQuestionPosition());
    }

    /**
     * @throws ilTemplateException
     * @throws LiveVotingException
     */
    protected function addOption(LiveVotingQuestionOption $option)
    {
        if ($option->getType() == LiveVotingQuestion::QUESTION_TYPES_IDS["FreeText"]) {
            return;
        }


        $player = $this->liveVoting->getPlayer();
        $question = $player->getActiveVotingObject();

        if ($option->getType() == LiveVotingQuestion::QUESTION_TYPES_IDS["NumberRange"]) {
            $columnWith = 6; //because of bootstrap grid 12 = 100%, 6 = 50% therefore 2 columns
            $percentage = (int) $question->isPercentage() === 1 ? ' %' : '';

            $this->tpl->setCurrentBlock('option2');
            $this->tpl->setVariable('OPTION_LETTER', ilLiveVotingPlugin::getInstance()->txt('qtype_6_range_start'));
            $this->tpl->setVariable('OPTION_COL', $columnWith);
            $this->tpl->setVariable('OPTION_TEXT', "{$question->getStartRange()}{$percentage}");
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('option2');
            $this->tpl->setVariable('OPTION_LETTER', ilLiveVotingPlugin::getInstance()->txt('qtype_6_range_end'));
            $this->tpl->setVariable('OPTION_COL', $columnWith);
            $this->tpl->setVariable('OPTION_TEXT', "{$question->getEndRange()}{$percentage}");
            $this->tpl->parseCurrentBlock();

            return;
        }

        $this->answer_count++;
        $this->tpl->setCurrentBlock('option');
        $this->tpl->setVariable('OPTION_LETTER', $option->getCipher());
        $this->tpl->setVariable('OPTION_COL', $question->getComputedColums());
        $this->tpl->setVariable('OPTION_TEXT', $option->getText());
        $this->tpl->parseCurrentBlock();
    }



}