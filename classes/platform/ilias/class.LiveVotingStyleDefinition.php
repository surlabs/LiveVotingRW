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

namespace LiveVoting\platform\ilias;

class LiveVotingStyleDefinition
{

    /**
     * @var LiveVotingSkin
     */
    protected LiveVotingSkin $skin;


    /**
     * xlvoStyleDefinition constructor.
     */
    public function __construct()
    {
        $this->skin = new LiveVotingSkin();
    }


    /**
     * @return LiveVotingSkin
     */
    public function getSkin(): LiveVotingSkin
    {
        return $this->skin;
    }


    /**
     * @param $style_id
     * @return string
     */
    public function getImageDirectory($style_id): string
    {
        return '';
    }
}
