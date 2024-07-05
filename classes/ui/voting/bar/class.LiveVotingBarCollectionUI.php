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
use ilSystemStyleException;
use ilTemplate;
use ilTemplateException;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingPlayer;

abstract class LiveVotingBarCollectionUI
{
    /**
     * @var ilTemplate
     */
    protected ilTemplate $tpl;
    /**
     * @var int
     */
    protected int $total_votes = 0;
    /**
     * @var bool
     */
    protected bool $show_total_votes = false;
    /**
     * @var int
     */
    protected int $total_voters = 0;
    /**
     * @var bool
     */
    protected bool $show_total_voters = false;

    /**
     * @throws ilSystemStyleException
     * @throws ilTemplateException
     */
    public function __construct()
    {
        $this->tpl = new ilTemplate(ilLiveVotingPlugin::getInstance()->getDirectory().'/templates/default/QuestionTypes/FreeInput/tpl.free_input_results.html', true, true);
    }



}
