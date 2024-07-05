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
use LiveVoting\Display\Bar\xlvoBarGroupingCollectionGUI;
use LiveVoting\platform\LiveVotingDatabase;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingCategory;
use LiveVoting\votings\LiveVotingPlayer;

abstract class LiveVotingFreeTextCategoriesUI
{
    /**
     * @var bool $removable
     */
    private bool $removable = false;
    /**
     * @var array $categories
     */
    protected array $categories = [];

    /**
     * LiveVotingFreeTextCategoriesUI constructor.
     *
     * @param LiveVotingPlayer $player
     * @param bool $edit_mode
     * @throws LiveVotingException
     */
    public function __construct(LiveVotingPlayer $player, bool $edit_mode = false)
    {
        $this->setRemovable($edit_mode);

        $database = new LiveVotingDatabase();

        $categories = $database->select("rep_robj_xlvo_cat", array("id"), array(
            "voting_id" => $player->getActiveVoting(),
            "round_id" => $player->getRoundId()
        ));

        foreach ($categories as $category) {
            $bar_collection = new xlvoBarGroupingCollectionGUI();
            $bar_collection->setRemovable($this->isRemovable());

            $category = new LiveVotingCategory((int) $category["id"]);

            $this->categories[$category->getId()] = [
                "title" => $category->getTitle(),
                "votes" => $bar_collection
            ];
        }
    }

    /**
     * @return bool
     */
    public function isRemovable(): bool
    {
        return $this->removable;
    }


    /**
     * @param bool $removable
     */
    public function setRemovable(bool $removable): void
    {
        $this->removable = $removable;
    }
}
