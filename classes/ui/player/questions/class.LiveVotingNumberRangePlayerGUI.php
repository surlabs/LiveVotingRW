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


use LiveVoting\platform\LiveVotingException;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\votings\LiveVotingPlayer;
use LiveVoting\votings\LiveVotingVote;



/**
 * Class LiveVotingNumberRangePlayerGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy LiveVotingNumberRangePlayerGUI: ilUIPluginRouterGUI, LiveVotingPlayerGUI
 * @ilCtrl_Calls LiveVotingNumberRangePlayerGUI: LiveVotingPlayerGUI, ilUIPluginRouterGUI
 */
class LiveVotingNumberRangePlayerGUI extends LiveVotingQuestionTypesUI
{

    const USER_SELECTED_NUMBER = 'qtype_6_user_selected_number';
    const SAVE_BUTTON_VOTE = 'qtype_6_voter_start_button_vote';
    const CLEAR_BUTTON = 'qtype_6_voter_clear';
    const SAVE_BUTTON_UNVOTE = 'qtype_6_voter_start_button_unvote';


    /**
     * @param LiveVotingPlayer $player
     *
     * @throws ilException
     */
    public function setManager(LiveVotingPlayer $player): void
    {

        if ($player === null) {
            throw new ilException('The manager must not be null.');
        }

        parent::setManager($player);
    }


    /**
     * @param bool $current
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    public function initJS(bool $current = false)
    {
        /*xlvoJs::getInstance()->api($this)->name(xlvoQuestionTypes::NUMBER_RANGE)->category('QuestionTypes')->addLibToHeader('bootstrap-slider.min.js')
            ->addSettings([
                "step" => $this->getStep()
            ])->init();*/


    }


    protected function clear()
    {

        /*$this->manager->unvoteAll();
        $this->afterSubmit();*/
    }


    /**
     *
     */
    protected function submit()
    {
        if ($this->manager === null) {
            throw new ilException('The NumberRange question got no voting manager! Please set one via setManager.');
        }

        //get all votes of the currents user
        // $votes = $this->manager->getVotesOfUser(false); TODO: ???

        //check if we voted or unvoted

        //we voted

        //filter the input and convert to int
        $filteredInput = filter_input(INPUT_POST, self::USER_SELECTED_NUMBER, FILTER_VALIDATE_INT);

        //check if the filter failed
        if ($filteredInput !== false && $filteredInput !== null) {
            //filter succeeded set value and store vote

            //validate user input
            if ($this->isVoteValid($this->getStart(), $this->getEnd(), $filteredInput)) {
                //vote
                $this->manager->inputOne([
                    'input'   => $filteredInput,
                    'vote_id' => '-1',
                ]);

                return;
            }
        }
    }


    /**
     * @return string
     * @throws ilCtrlException
     * @throws LiveVotingException
     * @throws ilTemplateException
     */
    public function getMobileHTML(): string
    {
        global $DIC;

        $template = new IlTemplate(ilLiveVotingPlugin::getInstance()->getDirectory().'/templates/default/QuestionTypes/NumberRange/tpl.number_range.html', true, true);
        $template->setVariable('ACTION', $DIC->ctrl()->getFormAction($this));
        $template->setVariable('SHOW_PERCENTAGE', (int) $this->getPlayer()->getActiveVotingObject()->isPercentage());

        /**
         * @var LiveVotingVote[] $userVotes
         */
        $userVotes = $this->getPlayer()->getVotesOfUser(false);
        $userVotes = array_values($userVotes);

        $template->setVariable('SLIDER_MIN', $this->getStart());
        $template->setVariable('SLIDER_MAX', $this->getEnd());
        $template->setVariable('SLIDER_STEP', $this->getStep());
        if (!empty($userVotes) && $userVotes[0] instanceof LiveVotingVote) {
            $user_has_voted = true;
            $value = (int) $userVotes[0]->getFreeInput();
        } else {
            $user_has_voted = false;
            $value = $this->getDefaultValue();
        }
        $template->setVariable('SLIDER_VALUE', $value);
        $template->setVariable('BTN_SAVE', ilLiveVotingPlugin::getInstance()->txt(self::SAVE_BUTTON_VOTE));
        $template->setVariable('BTN_CLEAR', ilLiveVotingPlugin::getInstance()->txt(self::CLEAR_BUTTON));

        if (!$user_has_voted) {
            $template->setVariable('BTN_RESET_DISABLED', 'disabled="disabled"');
        }

        return $template->get() . LiveVotingJs::getInstance()->name('NumberRange')->category('QuestionTypes/NumberRange')->getRunCode();
    }


    /**
     * @return int
     * @throws LiveVotingException
     */
    private function getDefaultValue(): int
    {
        return $this->snapToStep(($this->getStart() + $this->getEnd()) / 2);
    }


    /**
     * @param int $start
     * @param int $end
     * @param float $value
     *
     * @return bool
     * @throws LiveVotingException
     */
    private function isVoteValid(int $start,int $end,float $value): bool
    {
        return ($value >= $start && $value <= $end && $value == $this->snapToStep($value));
    }


    /**
     * @return int
     * @throws LiveVotingException
     */
    private function getStart(): int
    {
        return (int) $this->getPlayer()->getActiveVotingObject()->getStartRange();
    }


    /**
     * @return int
     * @throws LiveVotingException
     */
    private function getEnd(): int
    {
        return (int) $this->getPlayer()->getActiveVotingObject()->getEndRange();
    }


    /**
     * @return int
     * @throws LiveVotingException
     */
    public function getStep(): int
    {
        return (int) $this->getPlayer()->getActiveVotingObject()->getStepRange();
    }


    /**
     * @param float $value
     *
     * @return int
     * @throws LiveVotingException
     */
    private function snapToStep(float $value): int
    {
        return intval(ceil(($value - $this->getStart()) / $this->getStep()) * $this->getStep()) + $this->getStart();
    }
}