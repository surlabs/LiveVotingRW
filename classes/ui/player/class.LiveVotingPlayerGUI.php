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
 * Class LiveVotingPlayerGUI
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

    /**
     * @var LiveVoting
     */
    protected LiveVoting $liveVoting;

    /**
     * @throws LiveVotingException
     */
    public function executeCommand(): void
    {
        global $DIC;

        $this->pl = ilLiveVotingPlugin::getInstance();
        $param_manager = ParamManager::getInstance();

        $pin = $param_manager->getPin();

        if (empty($pin)) {
            $this->requestPin();
            return;
        }

        $this->liveVoting = LiveVoting::getLiveVotingFromPin($pin);

        $cmd = $DIC->ctrl()->getCmd('index');

        $this->{$cmd}();
    }

    /**
     * @throws ilCtrlException
     * @throws ilTemplateException
     */
    public function index(): void
    {
        global $DIC;
        if($this->liveVoting->getId()>0){
            $DIC->ctrl()->redirect($this, 'startVoterPlayer');
        }


      /*  try{
            $this->getHTML();
        } catch (LiveVotingException $e) {
            dump($e->getMessage());
            exit;
        }*/
    }

    /**
     * @throws ilTemplateException
     * @throws ilSystemStyleException
     * @throws LiveVotingException
     */
    protected function startVoterPlayer()
    {
/*        global $DIC, $tpl;
        $this->initJsAndCss();
        $tpl_voter = new ilTemplate($this->pl->getDirectory() .'/templates/default/Voter/tpl.voter_player.html', true, false);
        $tpl->addCss($this->pl->getDirectory().'/templates/css/default.css');
        $tpl->setVariable("PLAYER_CONTENT", $tpl_voter->get());
        //$this->getHTML();
        */

    }

    /**
     * @throws LiveVotingException
     * @throws ilTemplateException
     */
    protected function initJsAndCss()
    {
        global $DIC, $tpl;
        $tpl->addCss($this->pl->getDirectory().'/templates/default/Voter/voter.css');
        $tpl->addCss($this->pl->getDirectory().'/templates/default/libs/bootstrap-slider.min.css');
        $tpl->addCss($this->pl->getDirectory().'/templates/default/QuestionTypes/NumberRange/number_range.css');

        iljQueryUtil::initjQueryUI();

        $t = array('player_seconds');




        //TODO: Implementar esto
        //xlvoJs::getInstance()->initMathJax();
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
            $tpl_inner = new ilTemplate($this->pl->getDirectory() . "/templates/default/Voter/tpl.inner_screen.html", true, true);
        } catch (ilSystemStyleException|ilTemplateException $e) {
            throw new LiveVotingException($e->getMessage());
        }



        if (!$this->liveVoting) {
            dump("Mensaje de que el PIN no es válido");
            exit();
            return;
        }

        if (!$this->liveVoting->isOnline()) {
            dump("Mensaje de que el objeto LiveVoting no está disponible");
            exit();
            return;
        }

        if (!$this->liveVoting->isAnonymous() && LiveVotingParticipant::getInstance()->isPINUser()) {
            dump("Mensaje de que el usuario debe iniciar sesión para votar");
            exit();
            return;
        }
        global $DIC, $tpl;

        if ($this->liveVoting->getFrozenBehaviour()) {
            $tpl_inner->setVariable('TITLE', $this->txt('header_frozen'));
            $tpl_inner->setVariable('DESCRIPTION', $this->txt('info_frozen'));
            $tpl_inner->setVariable('COUNT',$this->liveVoting->countQuestions());
            $tpl_inner->setVariable('POSITION', $this->liveVoting->getQuestionPosition());
            //$tpl->setVariable('PIN', xlvoPin::formatPin($this->manager->getVotingConfig()->getPin()));
            //$tpl->setVariable('GLYPH', GlyphGUI::get('pause'));
            //echo $tpl->get();
            dump($tpl->get());
            //exit;
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