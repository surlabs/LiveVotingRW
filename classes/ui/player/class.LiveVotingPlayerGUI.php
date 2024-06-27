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
use LiveVoting\votings\LiveVotingParticipant;

/**
 * Class LiveVotingPlayerUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 *
 * @ilCtrl_isCalledBy LiveVotingPlayerGUI: ilUIPluginRouterGUI
 */
class LiveVotingPlayerGUI
{
    /**
     * @var ilLiveVotingPlugin
     */
    protected ilLiveVotingPlugin $pl;

    public function executeCommand(): void
    {
        global $DIC;

        $this->pl = ilLiveVotingPlugin::getInstance();


        $cmd = $DIC->ctrl()->getCmd('index');

        $this->{$cmd}();
    }

    public function index(): void
    {
        try{
            $this->getHTML();
        } catch (LiveVotingException $e) {
            dump($e->getMessage());
            exit;
        }
    }

    public function requestPin(): void
    {
        dump("Cargar el input para meter el PIN");
        exit();
    }

    /**
     * @throws LiveVotingException
     * @throws ilTemplateException
     */
    protected function getHTML(): void
    {

        try {
            $tpl = new ilGlobalTemplate($this->pl->getDirectory() . "/templates/default/Voter/tpl.voter_player.html", true, true);
        } catch (ilSystemStyleException|ilTemplateException $e) {
            throw new LiveVotingException($e->getMessage());
        }

        $param_manager = ParamManager::getInstance();

        $pin = $param_manager->getPin();

        if (empty($pin)) {
            $this->requestPin();
            return;
        }


        $liveVoting = LiveVoting::getLiveVotingFromPin($pin);

        if (!$liveVoting) {
            dump("Mensaje de que el PIN no es válido");
            exit();
            return;
        }

        if (!$liveVoting->isOnline()) {
            dump("Mensaje de que el objeto LiveVoting no está disponible");
            exit();
            return;
        }

        if (!$liveVoting->isAnonymous() && LiveVotingParticipant::getInstance()->isPINUser()) {
            dump("Mensaje de que el usuario debe iniciar sesión para votar");
            exit();
            return;
        }

        dump($tpl);
        exit;

        if ($liveVoting->getFrozenBehaviour()) {
            $tpl->setVariable('TITLE', $this->txt('header_frozen'));
            $tpl->setVariable('DESCRIPTION', $this->txt('info_frozen'));
            $tpl->setVariable('COUNT', $this->manager->countVotings());
            $tpl->setVariable('POSITION', $this->manager->getVotingPosition());
            //$tpl->setVariable('PIN', xlvoPin::formatPin($this->manager->getVotingConfig()->getPin()));
            //$tpl->setVariable('GLYPH', GlyphGUI::get('pause'));
            echo $tpl->get();
            exit;
        }

     /*   switch ($this->manager->getPlayer()->getStatus(false)) {
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
        exit;*/
    }

    protected function txt(string $key): string
    {
        return $this->pl->txt($key);
    }
}