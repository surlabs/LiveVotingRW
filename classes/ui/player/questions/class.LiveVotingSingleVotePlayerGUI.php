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
use LiveVoting\questions\LiveVotingQuestionOption;
use LiveVoting\UI\Player\CustomUI\HiddenInputGUI\HiddenInputGUI;
use LiveVoting\UI\Player\CustomUI\MultiLineNewInputGUI;
use LiveVoting\UI\Player\CustomUI\TextAreaInputGUI\TextAreaInputGUI;
use LiveVoting\UI\Voting\Bar\LiveVotingBarMovableUI;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\Utils\LiveVotingUtils;
use LiveVoting\votings\LiveVotingVote;



/**
 * Class LiveVotingSingleVotePlayerGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy LiveVotingSingleVotePlayerGUI: ilUIPluginRouterGUI, LiveVotingPlayerGUI
 * @ilCtrl_Calls LiveVotingSingleVotePlayerGUI: LiveVotingPlayerGUI, ilUIPluginRouterGUI
 */
class LiveVotingSingleVotePlayerGUI extends LiveVotingQuestionTypesUI
{

    const BUTTON_TOGGLE_PERCENTAGE = 'toggle_percentage';


    /**
     * @param bool $current
     * @throws ilCtrlException
     */
    public function initJS(bool $current = false)
    {
       
    }


    /**
     *
     */
    protected function submit()
    {
        $this->manager->vote($_GET['option_id']);
    }


    /**
     * @return array
     */
    public function getButtonInstances(): array
    {
        if (!$this->getPlayer()->isShowResults()) {
            return array();
        }
        $states = $this->getButtonsStates();
        $t = ilLinkButton::getInstance();
        $t->setId(self::BUTTON_TOGGLE_PERCENTAGE);
        if (in_array(self::BUTTON_TOGGLE_PERCENTAGE,$states)) {
            $t->setCaption(' %', false);
        } else {
            $t->setCaption(ilGlyphGUI::get('close'), false);
        }

        return array($t);
    }


    /**
     * @param $button_id
     * @param $data
     * @throws LiveVotingException
     */
    public function handleButtonCall($button_id, $data)
    {
        $states = $this->getButtonsStates();
        $this->saveButtonState($button_id, !$states[$button_id]);
    }


    /**
     * @return string
     * @throws LiveVotingException
     * @throws ilTemplateException
     * @throws ilCtrlException
     * @throws ilSystemStyleException
     */
    public function getMobileHTML(): string
    {
        global $DIC;
        $tpl = new ilTemplate( ilLiveVotingPlugin::getInstance()->getDirectory().'/templates/default/QuestionTypes/SingleVote/tpl.single_vote.html', false, true);
        $answer_count = 64;
        foreach ($this->getPlayer()->getActiveVotingObject()->getOptions() as $xlvoOption) {
            $answer_count++;
            $DIC->ctrl()->setParameter($this, 'option_id', $xlvoOption->getId());
            $tpl->setCurrentBlock('option');
            $tpl->setVariable('TITLE', $xlvoOption->getText());
            $tpl->setVariable('LINK', $DIC->ctrl()->getLinkTarget($this, 'submit'));
            $tpl->setVariable('OPTION_LETTER', chr($answer_count));
            if ($this->player->hasUserVotedForOption($xlvoOption->getId())) {
                $tpl->setVariable('BUTTON_STATE', 'btn-primary');
                $tpl->setVariable('ACTION', ilLiveVotingPlugin::getInstance()->txt('qtype_1_unvote'));
            } else {
                $tpl->setVariable('BUTTON_STATE', 'btn-default');
                $tpl->setVariable('ACTION', ilLiveVotingPlugin::getInstance()->txt('qtype_1_vote'));
            }
            $tpl->parseCurrentBlock();
        }

        return $tpl->get() . LiveVotingJs::getInstance()->name('SingleVote')->category('QuestionTypes/SingleVote')->getRunCode();
    }
}