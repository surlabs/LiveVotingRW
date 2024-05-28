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

/**
 * Class LiveVotingParticipant
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class LiveVotingParticipant implements LiveVotingUser
{

    private int $ilias_user_id;
    private int $internal_id;
    private bool $is_admin;
    private bool $is_anonymous;
    private LiveVotingLog $log;
    private LiveVotingParticipantResults $result;

    public function __construct(?int $ilias_user_id)
    {
        if (!is_null($ilias_user_id)) {
            $this->ilias_user_id = $ilias_user_id;
            $this->is_anonymous = false;
        } else {
            $this->ilias_user_id = 0;
            $this->is_anonymous = true;
            $this->is_admin = false;
        }
    }

    public function isAnonymous(): bool
    {
        return $this->is_anonymous;
    }

    public function isAdministrator(): bool
    {
        // TODO: Implement isAdministrator() method.
    }

    public function getResults(): LiveVotingParticipantResults
    {

    }
}