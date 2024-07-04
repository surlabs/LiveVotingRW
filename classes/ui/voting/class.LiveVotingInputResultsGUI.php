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
use LiveVoting\votings\LiveVoting;
use LiveVoting\votings\LiveVotingPlayer;

abstract class LiveVotingInputResultsGUI
{
    /**
     * @var LiveVoting
     */
    protected LiveVoting $liveVoting;
    /**
     * @var LiveVotingPlayer
     */
    protected LiveVotingPlayer $player;
    const TYPE_SINGLE_VOTE = 1;
    const TYPE_FREE_INPUT = 2;
    const TYPE_RANGE = 3;
    const TYPE_CORRECT_ORDER = 4;
    const TYPE_FREE_ORDER = 5;
    const TYPE_NUMBER_RANGE = 6;
    const SINGLE_VOTE = 'SingleVote';
    const FREE_INPUT = 'FreeInput';
    const CORRECT_ORDER = 'CorrectOrder';
    const FREE_ORDER = 'FreeOrder';
    const NUMBER_RANGE = 'NumberRange';
    /**
     * @var array
     */
    protected static array $class_map
        = array(
            self::TYPE_SINGLE_VOTE   => self::SINGLE_VOTE,
            self::TYPE_FREE_INPUT    => self::FREE_INPUT,
            self::TYPE_CORRECT_ORDER => self::CORRECT_ORDER,
            self::TYPE_FREE_ORDER    => self::FREE_ORDER,
            self::TYPE_NUMBER_RANGE  => self::NUMBER_RANGE
        );
    /**
     * LiveVotingInputResultsGUI constructor.
     *
     * @param LiveVoting $liveVoting
     * @param LiveVotingPlayer $player
     */
    public function __construct(LiveVoting $liveVoting, LiveVotingPlayer $player)
    {
        $this->liveVoting = $liveVoting;
        $this->player = $player;
    }

    public function reset() :void
    {
        //TODO: Saaweel trabaja
    }

    /**
     * @throws ilCtrlException
     */
    public static function getInstance(LiveVoting $liveVoting) :void
    {
        //$class = self::getClassName($liveVoting->getVotingType());
    }

    /**
     * @throws ilCtrlException
     */
    public static function getClassName($type)
    {
        global $DIC;
        if (!isset(self::$class_map[$type])) {
            //throw new xlvoVotingManagerException('Type not available');
            $DIC->ctrl()->redirectByClass("ilObjLiveVoting", 'showTypeError');
            //TODO: Implementar showTypeError
        }

        return self::$class_map[$type];
    }
}
