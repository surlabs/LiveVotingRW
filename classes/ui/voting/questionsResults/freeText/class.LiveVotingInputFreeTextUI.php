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
namespace LiveVoting\UI\QuestionsResults;

use ilException;
use ilLiveVotingPlugin;
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use LiveVoting\Display\Bar\xlvoBarFreeInputsGUI;
use LiveVoting\Exceptions\xlvoPlayerException;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\questions\LiveVotingQuestionOption;
use LiveVoting\UI\Voting\Bar\LiveVotingBarFreeTextUI;
use LiveVoting\UI\Voting\Bar\LiveVotingBarGroupingCollectionUI;
use LiveVoting\votings\LiveVotingPlayer;
use LiveVoting\votings\LiveVotingVote;

abstract class LiveVotingInputFreeTextUI extends LiveVotingInputResultsGUI
{
    /**
     * @var bool
     */
    protected bool $edit_mode = false;
    /**
     * LiveVotingInputFreeTextUI constructor.
     *
     * @param LiveVotingPlayer $player
     */
    public function __construct(LiveVotingPlayer $player)
    {
        parent::__construct($player);
    }

    /**
     * @throws ilSystemStyleException
     * @throws ilTemplateException
     * @throws LiveVotingException
     * @throws ilException
     */
    public function getHTML() :string
    {
        global $DIC;
        $button_states = $this->player->getButtonStates();

        $this->edit_mode = (array_key_exists('btn_categorize', $button_states) && $button_states['btn_categorize'] == 'true');
        $tpl = new ilTemplate(ilLiveVotingPlugin::getInstance()->getDirectory().'/templates/default/QuestionTypes/FreeInput/tpl.free_input_results.html', true, true);

        $categories = new LiveVotingFreeTextCategoriesUI($this->player, $this->edit_mode);

        $bars = new LiveVotingBarGroupingCollectionUI();
        $bars->setRemovable($this->edit_mode);
        $bars->setShowTotalVotes(true);

        $option = $this->player->getActiveVotingObject()->getFirstOption();

        $votes = LiveVotingVote::getVotesOfOption($this->player->getActiveVotingObject()->getId(), $this->player->getRoundId());



        foreach ($votes as $vote){
            if ($cat_id = $vote->getFreeInputCategory()) {
                try {
                    $categories->addBar(new LiveVotingBarFreeTextUI($this->player->getActiveVoting(), $vote), $cat_id);
                } catch (LiveVotingException $e) {
                    if ($e->getCode() == 3) {
                        $bars->addBar(new xlvoBarFreeInputsGUI($this->manager->getVoting(), $vote));
                    }
                }
            } else {
                $bars->addBar(new xlvoBarFreeInputsGUI($this->manager->getVoting(), $vote));
            }
        }

        $bars->setTotalVotes(count($votes));

        $tpl->setVariable('ANSWERS', $bars->getHTML());
        $tpl->setVariable('CATEGORIES', $categories->getHTML());
        if ($this->edit_mode) {
            $tpl->setVariable('LABEL_ADD_CATEGORY', self::plugin()->translate('btn_add_category'));
            $tpl->setVariable('PLACEHOLDER_ADD_CATEGORY', self::plugin()->translate('category_title'));
            $tpl->setVariable('LABEL_ADD_ANSWER', self::plugin()->translate('btn_add_answer'));
            $tpl->setVariable('PLACEHOLDER_ADD_ANSWER', self::plugin()->translate('voter_answer'));
            $tpl->setVariable('BASE_URL', self::dic()->ctrl()->getLinkTargetByClass(xlvoPlayerGUI::class, xlvoPlayerGUI::CMD_API_CALL, "", true));
        }

        return $tpl->get();

    }


}
