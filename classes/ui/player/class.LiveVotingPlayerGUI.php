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
 * Class LiveVotingPlayerUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 *
 * @ilCtrl_isCalledBy LiveVotingPlayerGUI: ilUIPluginRouterGUI
 */
class LiveVotingPlayerGUI
{
    public function executeCommand(): void
    {
        global $DIC;

        $cmd = $DIC->ctrl()->getCmd('index');

        $this->{$cmd}();
    }

    public function index(): void
    {
        dump("Cargar el input para meter el PIN");
        exit();
    }

    public function votingNotFound(): void
    {
        dump("Mensaje de error: No se encuentra la votación");
        exit();
    }

    public function votingOffline(): void
    {
        dump("Mensaje de error: El repositorio está offline");
        exit();
    }

    public function votingNeedLogin(): void
    {
        dump("Mensaje de error: El usuario no está logueado y la votacion no es anonima");
        exit();
    }

    public function startVoterPlayer(): void
    {
        dump("Cargar la vista de la votación");
        exit();
    }
}