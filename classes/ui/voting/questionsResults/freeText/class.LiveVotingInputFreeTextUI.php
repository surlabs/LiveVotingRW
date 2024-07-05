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

use ilCtrlException;
use ilLiveVotingPlugin;
use ilObjLiveVotingGUI;
use ilTemplate;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingPlayer;

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
     * @throws \ilSystemStyleException
     * @throws \ilTemplateException
     */
    public function getHTML() :string
    {
        global $DIC;
        $button_states = $this->player->getButtonStates();

        $this->edit_mode = (array_key_exists('btn_categorize', $button_states) && $button_states['btn_categorize'] == 'true');
        $tpl = new ilTemplate(ilLiveVotingPlugin::getInstance()->getDirectory().'/templates/default/QuestionTypes/FreeInput/tpl.free_input_results.html', true, true);

        $categories = new LiveVotingChoicesCategoriesUI($this->player, $this->edit_mode);

    }


}
