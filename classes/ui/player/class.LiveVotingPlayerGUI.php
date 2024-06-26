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
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVoting;

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
        /*dump("Mensaje de error: El repositorio está offline");
        exit();*/
        try {
            $this->getHTML();
        } catch (Exception $e) {
            dump($e->getMessage());
            exit();
        }

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

    /**
     * @throws LiveVotingException
     */
    protected function getHTML()
    {
       // $tpl = new ilGlobalTemplate('default/Voter/tpl.inner_screen.html', true, true, 'Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting');

        $param_manager = ParamManager::getInstance();

        $pin = $param_manager->getPin();

        $liveVoting = LiveVoting::getLiveVotingFromPin($pin);

        dump($liveVoting->getFrozenBehaviour());exit;

        if ($liveVoting->getFrozenBehaviour()) {
            $tpl->setVariable('TITLE', $this->txt('header_frozen'));
            $tpl->setVariable('DESCRIPTION', $this->txt('info_frozen'));
            $tpl->setVariable('COUNT', $this->manager->countVotings());
            $tpl->setVariable('POSITION', $this->manager->getVotingPosition());
            $tpl->setVariable('PIN', xlvoPin::formatPin($this->manager->getVotingConfig()->getPin()));
            $tpl->setVariable('GLYPH', GlyphGUI::get('pause'));
            echo $tpl->get();
            exit;
        }

        switch ($this->manager->getPlayer()->getStatus(false)) {
            case xlvoPlayer::STAT_STOPPED:
                $tpl->setVariable('TITLE', $this->txt('header_stopped'));
                $tpl->setVariable('DESCRIPTION', $this->txt('info_stopped'));
                $tpl->setVariable('COUNT', $this->manager->countVotings());
                $tpl->setVariable('POSITION', $this->manager->getVotingPosition());
                $tpl->setVariable('PIN', xlvoPin::formatPin($this->manager->getVotingConfig()->getPin()));
                break;
            case xlvoPlayer::STAT_RUNNING:
                $tpl->setVariable('TITLE', $this->manager->getVoting()->getTitle());
                $tpl->setVariable('DESCRIPTION', $this->manager->getVoting()->getDescription());
                $tpl->setVariable('COUNT', $this->manager->countVotings());
                $tpl->setVariable('POSITION', $this->manager->getVotingPosition());
                $tpl->setVariable('PIN', xlvoPin::formatPin($this->manager->getVotingConfig()->getPin()));

                $xlvoQuestionTypesGUI = xlvoQuestionTypesGUI::getInstance($this->manager);
                if ($xlvoQuestionTypesGUI->isShowQuestion()) {
                    $tpl->setCurrentBlock('question_text');
                    $tpl->setVariable('QUESTION_TEXT', $this->manager->getVoting()->getQuestionForPresentation());
                    $tpl->parseCurrentBlock();
                }
                $tpl->setVariable('QUESTION', $xlvoQuestionTypesGUI->getMobileHTML());
                break;
            case xlvoPlayer::STAT_START_VOTING:
                $tpl->setVariable('TITLE', $this->txt('header_start'));
                $tpl->setVariable('DESCRIPTION', $this->txt('info_start'));
                $tpl->setVariable('GLYPH', GlyphGUI::get('pause'));
                break;
            case xlvoPlayer::STAT_END_VOTING:
                $tpl->setVariable('TITLE', $this->txt('header_end'));
                $tpl->setVariable('DESCRIPTION', $this->txt('info_end'));;
                $tpl->setVariable('GLYPH', GlyphGUI::get('stop'));
                break;
            case xlvoPlayer::STAT_FROZEN:
                $tpl->setVariable('TITLE', $this->txt('header_frozen'));
                $tpl->setVariable('DESCRIPTION', $this->txt('info_frozen'));
                $tpl->setVariable('COUNT', $this->manager->countVotings());
                $tpl->setVariable('POSITION', $this->manager->getVotingPosition());
                $tpl->setVariable('PIN', xlvoPin::formatPin($this->manager->getVotingConfig()->getPin()));
                $tpl->setVariable('GLYPH', GlyphGUI::get('pause'));
                break;
        }
        echo $tpl->get();
        exit;
    }
}