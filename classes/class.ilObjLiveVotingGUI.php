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
 * info@surlabs.esr
 *
 */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use LiveVoting\legacy\LiveVotingResultsTableGUI;
use LiveVoting\platform\LiveVotingDatabase;
use LiveVoting\platform\LiveVotingException;
use LiveVoting\UI\LiveVotingChoicesUI;
use LiveVoting\UI\LiveVotingCorrectOrderUI;
use LiveVoting\UI\LiveVotingFreeInputUI;
use LiveVoting\UI\LiveVotingManageUI;
use LiveVoting\UI\LiveVotingPrioritiesUI;
use LiveVoting\UI\LiveVotingRangeUI;
use LiveVoting\UI\LiveVotingResultsUI;
use LiveVoting\UI\LiveVotingSettingsUI;
use LiveVoting\UI\LiveVotingUI;
use LiveVoting\Utils\LiveVotingJs;
use LiveVoting\Utils\ParamManager;
use LiveVoting\votings\LiveVotingCategory;
use LiveVoting\votings\LiveVotingParticipant;
use LiveVoting\votings\LiveVotingPlayer;
use LiveVoting\votings\LiveVotingRound;
use LiveVoting\votings\LiveVotingVote;

/**
 * Class ilObjLiveVotingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjLiveVotingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI, LiveVotingUI, LiveVotingChoicesUI, LiveVotingManageUI, LiveVotingSettingsUI, LiveVotingFreeInputUI, LiveVotingRangeUI, LiveVotingPrioritiesUI, LiveVotingCorrectOrderUI, LiveVotingResultsUI
 * @ilCtrl_Calls      ilObjLiveVotingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI, LiveVotingUI, LiveVotingChoicesUI, LiveVotingManageUI, LiveVotingSettingsUI, LiveVotingFreeInputUI, LiveVotingRangeUI, LiveVotingPrioritiesUI, LiveVotingCorrectOrderUI, LiveVotingResultsUI
 */
class ilObjLiveVotingGUI extends ilObjectPluginGUI
{
    private Factory $factory;
    private Renderer $renderer;
    public function __construct(int $a_ref_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, int $a_parent_node_id = 0)
    {
        global $DIC;

        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);

        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
    }

    public function getType(): string
    {
        return "xlvo";
    }

    public function getAfterCreationCmd(): string
    {
        return 'index';
    }

    public function getStandardCmd(): string
    {
        return 'index';
    }


    public function performCommand(string $cmd): void
    {
        global $DIC;
        $DIC->help()->setScreenIdComponent(ilLiveVotingPlugin::PLUGIN_ID);

        $DIC->ui()->mainTemplate()->setPermanentLink(ilLiveVotingPlugin::PLUGIN_ID, $this->ref_id);

        switch ($cmd){
            case 'index':
                $this->showContent();
                break;
            case 'editProperties':
            case 'startPlayer':
            case 'getPlayerData':
            case 'manage':
            case 'results':
            case 'selectType':
            case 'selectedChoices':
            case 'selectedFreeInput':
            case 'selectedCorrectOrder':
            case 'selectedPriorities':
            case 'selectedRange':
            case 'updateProperties':
            case 'confirmNewRound':
            case 'newRound':
            case 'changeRound':
            case 'applyFilter':
            case 'resetFilter':
            case 'apiCall':
                $this->{$cmd}();
                break;
            case 'edit':
                $this->editQuestion();
                break;
        }
    }

    public function showContent(): void
    {
        $liveVotingUI = new LiveVotingUI($this->object->getLiveVoting());

        $this->tabs->activateTab("tab_content");

        try {
            $this->tpl->setContent($liveVotingUI->showIndex());
        } catch (ilSystemStyleException|ilTemplateException $e) {
            $this->tpl->setContent($this->renderer->render($this->factory->messageBox()->failure($e->getMessage())));
        }
    }

    /**
     * @throws ilCtrlException|ilException
     */
    public function manage(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        if (!ilObjLiveVotingAccess::hasWriteAccess()) {
            $this->tpl->setContent($this->renderer->render($this->factory->messageBox()->failure($this->plugin->txt("permission_denied"))));
        } elseif (ilObjLiveVotingAccess::hasWriteAccess()) {
            $liveVotingManageUI = new LiveVotingManageUI();
            try {
                $this->tpl->setContent($liveVotingManageUI->showManage($this));
            } catch (ilSystemStyleException|ilTemplateException $e) {
                $this->tpl->setContent($this->renderer->render($this->factory->messageBox()->failure($e->getMessage())));
            }


        }
    }

    /**
     * @throws ilCtrlException
     * @throws LiveVotingException
     * @throws ilException
     */
    public function results(): void
    {
        $this->tabs->activateTab("tab_results");

        if (!ilObjLiveVotingAccess::hasWriteAccess()) {
            $this->tpl->setContent($this->renderer->render($this->factory->messageBox()->failure($this->plugin->txt("permission_denied"))));
        } else {
            $liveVotingResultsUI = new LiveVotingResultsUI($this->object->getLiveVoting());
            $this->tpl->setContent($liveVotingResultsUI->showResults($this));
        }
    }

    /**
     * @throws ilException
     * @throws LiveVotingException
     */
    public function selectedChoices(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        $liveVotingChoicesUI = new LiveVotingChoicesUI();
        $form = $liveVotingChoicesUI->getChoicesForm();
        if($DIC->http()->request()->getMethod() == "POST") {

            $id = $liveVotingChoicesUI->save($form->withRequest($DIC->http()->request())->getData());

            if($id !== 0){
                $DIC->ctrl()->setParameter($this, "question_id", $id);
                $DIC->ctrl()->setParameter($this, "show_success", true);
                $DIC->ctrl()->redirect($this, "edit");

            } else {
                $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($DIC->language()->txt("form_input_not_valid")));
                $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
            }
        } else {
            $this->tpl->setContent($DIC->ui()->renderer()->render($form));

        }
    }

    /**
     * @throws ilException
     * @throws LiveVotingException
     */
    public function selectedFreeInput(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        $liveVotingFreeInputUI = new LiveVotingFreeInputUI();
        $form = $liveVotingFreeInputUI->getFreeForm();
        if($DIC->http()->request()->getMethod() == "POST") {

            $id = $liveVotingFreeInputUI->save($form->withRequest($DIC->http()->request())->getData());

            if($id !== 0){
                $DIC->ctrl()->setParameter($this, "question_id", $id);
                $DIC->ctrl()->setParameter($this, "show_success", true);
                $DIC->ctrl()->redirect($this, "edit");

            } else {
                $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($DIC->language()->txt("form_input_not_valid")));
                $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
            }
        } else {
            $this->tpl->setContent($DIC->ui()->renderer()->render($form));
        }
    }

    /**
     * @throws ilException
     * @throws LiveVotingException
     */
    public function selectedCorrectOrder(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        $liveVotingCorrectOrderUI = new LiveVotingCorrectOrderUI();
        $form = $liveVotingCorrectOrderUI->getCorrectOrderForm();
        if($DIC->http()->request()->getMethod() == "POST") {

            $id = $liveVotingCorrectOrderUI->save($form->withRequest($DIC->http()->request())->getData());

            if($id !== 0){
                $DIC->ctrl()->setParameter($this, "question_id", $id);
                $DIC->ctrl()->setParameter($this, "show_success", true);
                $DIC->ctrl()->redirect($this, "edit");

            } else {
                $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($DIC->language()->txt("form_input_not_valid")));
                $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
            }
        } else {
            $this->tpl->setContent($DIC->ui()->renderer()->render($form));

        }
    }

    /**
     * @throws ilException
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    public function selectedPriorities(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        $liveVotingPrioritiesUI = new LiveVotingPrioritiesUI();
        $form = $liveVotingPrioritiesUI->getPrioritiesForm();
        if($DIC->http()->request()->getMethod() == "POST") {

            $id = $liveVotingPrioritiesUI->save($form->withRequest($DIC->http()->request())->getData());

            if($id !== 0){
                $DIC->ctrl()->setParameter($this, "question_id", $id);
                $DIC->ctrl()->setParameter($this, "show_success", true);
                $DIC->ctrl()->redirect($this, "edit");

            } else {
                $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($DIC->language()->txt("form_input_not_valid")));
                $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
            }
        } else {
            $this->tpl->setContent($DIC->ui()->renderer()->render($form));

        }
    }

    /**
     * @throws ilException
     * @throws LiveVotingException
     */
    public function selectedRange(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_manage");

        $liveVotingRangeUI = new LiveVotingRangeUI();
        $form = $liveVotingRangeUI->getRangeForm();
        if($DIC->http()->request()->getMethod() == "POST") {

            $id = $liveVotingRangeUI->save($form->withRequest($DIC->http()->request())->getData());

            if($id !== 0){
                $DIC->ctrl()->setParameter($this, "question_id", $id);
                $DIC->ctrl()->setParameter($this, "show_success", true);
                $DIC->ctrl()->redirect($this, "edit");

            } else {
                $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->failure($DIC->language()->txt("form_input_not_valid")));
                $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
            }
        } else {
            $this->tpl->setContent($DIC->ui()->renderer()->render($form));

        }
    }

    protected function initHeaderAndLocator(): void
    {
        global $DIC;
        $this->setTitleAndDescription();
        if(!$this->getCreationMode()) {
            $DIC->ui()->mainTemplate()->setTitle($this->object->getTitle());
            $DIC->ui()->mainTemplate()->setTitleIcon(IlObject::_getIcon($this->object->getId()));
            //$DIC->ui()->saveParameterByClass("CLASE RESULTS, PARÁMETRO round_id");

            //TODO: Aquí hay un if en el original que comprueba si el parámetro baseClass es igual a la clase GUI de administración.
            //$this->setTabs();

            //TODO: Comprobación de permisos
        } else {
            $DIC->ui()->mainTemplate()->setTitle(ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id)));
            //TODO: Añadir icono?
        }
        $this->setLocator();
    }

    /**
     * @throws ilCtrlException
     */
    protected function setTabs(): void
    {
        $this->tabs->addTab("tab_content", $this->lng->txt("tab_content"), $this->ctrl->getLinkTarget($this, "index"));
        $this->tabs->addTab("tab_manage", $this->plugin->txt("tab_manage"), $this->ctrl->getLinkTarget($this, "manage"));
        $this->tabs->addTab("tab_results", $this->plugin->txt("tab_results"), $this->ctrl->getLinkTarget($this, "results"));
        $this->tabs->addTab("info_short", $this->lng->txt('info_short'), $this->ctrl->getLinkTargetByClass(array(
            get_class($this),
            "ilInfoScreenGUI",
        ), "showSummary"));

        if ($this->checkPermissionBool("write")) {
            $this->tabs->addTab("tab_edit", $this->plugin->txt("tab_edit"), $this->ctrl->getLinkTarget($this, "editProperties"));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs->addTab("perm_settings", $this->lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass(array(
                get_class($this),
                "ilPermissionGUI",
            ), "perm"));
        }
    }

    /**
     * Add sub tabs and activate the forwarded sub tab in the parameter.
     *
     * @param string $tab
     * @param string $active_sub_tab
     * @throws ilCtrlException
     */
    protected function setSubTabs(string $tab, string $active_sub_tab): void
    {
        if($tab == 'tab_content'){
            $this->tabs->addSubTab("subtab_show",
                $this->plugin->txt('subtab_show'),
                $this->ctrl->getLinkTarget($this, "index")
            );
            $this->tabs->addSubTab("subtab_edit",
                $this->plugin->txt('subtab_edit'),
                $this->ctrl->getLinkTarget($this, "content")
            );

//            if (ilObjLiveVotingAccess::hasWriteAccess()) {
//                $this->tabs->addSubTab("subtab_edit",
//                    $this->plugin->txt('subtab_edit'),
//                    $this->ctrl->getLinkTargetByClass("LiveVotingUI", "showContent")
//                );
//            }
        }

        $this->tabs->activateSubTab($active_sub_tab);
    }

/*    protected function triageCmdClass($next_class, $cmd): void
    {
        switch($next_class){
            default:
                if (strcasecmp($_GET['baseClass'], ilAdministrationGUI::class) == 0) {
                    $this->viewObject();
                    return;
                }
                if (!$cmd) {
                    $cmd = $this->getStandardCmd();
                }
                if ($this->getCreationMode()) {
                    $this->$cmd();
                } else {
                    $this->performCommand($cmd);
                }
                break;
        }
    }*/

    public function getObjId(): int
    {
        return $this->object->getId();
    }

    /**
     * @throws ilCtrlException
     */
    public function editProperties(): void
    {
        global $DIC;
        $this->tabs->activateTab("tab_edit");

        $liveVotingSettingsUI = new LiveVotingSettingsUI($this->getRefId());

        $sections = $liveVotingSettingsUI->initPropertiesForm();
        $form_action = $DIC->ctrl()->getLinkTargetByClass(ilObjLiveVotingGUI::class, "editProperties");
        $rendered = $liveVotingSettingsUI->renderForm($form_action, $sections);

        $this->tpl->setContent($rendered);
    }

    /**
     * @throws LiveVotingException
     * @throws ilException
     */
    public function editQuestion(): void
    {
        global $DIC;
        //TODO: COMPROBACIÓN DE PERMISOS
        $this->tabs->activateTab("tab_manage");

        $question = $this->object->getLiveVoting()->getQuestionById((int) $_GET['question_id']);
        switch($question->getQuestionType()) {
            case "Choices":
                $liveVotingChoicesUI = new LiveVotingChoicesUI($question->getId());
                $form = $liveVotingChoicesUI->getChoicesForm();
                $saving_info = "";
                if($DIC->http()->request()->getMethod() == "POST") {

                    $id = $liveVotingChoicesUI->save($form->withRequest($DIC->http()->request())->getData(), $question->getId());

                    if($id !== 0){
                        $liveVotingChoicesUI = new LiveVotingChoicesUI($id);
                        $form = $liveVotingChoicesUI->getChoicesForm();

                        $DIC->ctrl()->setParameter($this, "question_id", $id);
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_updated')));
                        $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));
                    } else {
                        $this->tpl->setContent($DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
                    }

                } else {
                    if(isset($_GET['show_success'])){
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_created')));
                    }
                    $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));

                }
                break;
            case "FreeText":
                $liveVotingFreeInputUI = new LiveVotingFreeInputUI($question->getId());
                $form = $liveVotingFreeInputUI->getFreeForm();
                $saving_info = "";
                if($DIC->http()->request()->getMethod() == "POST") {

                    $id = $liveVotingFreeInputUI->save($form->withRequest($DIC->http()->request())->getData(), $question->getId());

                    if($id !== 0){
                        $liveVotingFreeInputUI = new LiveVotingFreeInputUI($id);
                        $form = $liveVotingFreeInputUI->getFreeForm();

                        $DIC->ctrl()->setParameter($this, "question_id", $id);
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_updated')));
                        $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));
                    } else {
                        $this->tpl->setContent($DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
                    }

                } else {
                    if(isset($_GET['show_success'])){
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_created')));
                    }
                    $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));

                }
                break;
            case "NumberRange":
                $liveVotingRangeUI = new LiveVotingRangeUI($question->getId());
                $form = $liveVotingRangeUI->getRangeForm();
                $saving_info = "";
                if($DIC->http()->request()->getMethod() == "POST") {

                    $id = $liveVotingRangeUI->save($form->withRequest($DIC->http()->request())->getData(), $question->getId());

                    if($id !== 0){
                        $liveVotingRangeUI = new LiveVotingRangeUI($id);
                        $form = $liveVotingRangeUI->getRangeForm();

                        $DIC->ctrl()->setParameter($this, "question_id", $id);
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_updated')));
                        $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));
                    } else {
                        $this->tpl->setContent($DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
                    }

                } else {
                    if(isset($_GET['show_success'])){
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_created')));
                    }
                    $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));

                }
                break;
            case "Priorities":
                $liveVotingPrioritiesUI = new LiveVotingPrioritiesUI($question->getId());
                $form = $liveVotingPrioritiesUI->getPrioritiesForm();
                $saving_info = "";
                if($DIC->http()->request()->getMethod() == "POST") {

                    $id = $liveVotingPrioritiesUI->save($form->withRequest($DIC->http()->request())->getData(), $question->getId());

                    if($id !== 0){
                        $liveVotingPrioritiesUI = new LiveVotingPrioritiesUI($id);
                        $form = $liveVotingPrioritiesUI->getPrioritiesForm();

                        $DIC->ctrl()->setParameter($this, "question_id", $id);
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_updated')));
                        $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));
                    } else {
                        $this->tpl->setContent($DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
                    }

                } else {
                    if(isset($_GET['show_success'])){
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_created')));
                    }
                    $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));

                }
                break;
            case "CorrectOrder":
                $liveVotingCorrectOrderUI = new LiveVotingCorrectOrderUI($question->getId());
                $form = $liveVotingCorrectOrderUI->getCorrectOrderForm();
                $saving_info = "";
                if($DIC->http()->request()->getMethod() == "POST") {

                    $id = $liveVotingCorrectOrderUI->save($form->withRequest($DIC->http()->request())->getData(), $question->getId());

                    if($id !== 0){
                        $liveVotingCorrectOrderUI = new LiveVotingCorrectOrderUI($id);
                        $form = $liveVotingCorrectOrderUI->getCorrectOrderForm();

                        $DIC->ctrl()->setParameter($this, "question_id", $id);
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_updated')));
                        $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));
                    } else {
                        $this->tpl->setContent($DIC->ui()->renderer()->render($form->withRequest($DIC->http()->request())));
                    }

                } else {
                    if(isset($_GET['show_success'])){
                        $saving_info = $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->success($this->plugin->txt('msg_success_voting_created')));
                    }
                    $this->tpl->setContent($saving_info.$DIC->ui()->renderer()->render($form));

                }
                break;
        }
        //TODO: Traer prev y next question para navegación

    }


    /**
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    protected function startPlayer():void
    {

        $this->tabs->activateTab("tab_content");

        $liveVotingUI = new LiveVotingUI($this->object->getLiveVoting());
        $liveVotingUI->initJsAndCss($this);
        $liveVotingUI->showVoting();

    }

    /**
     * @throws LiveVotingException
     */
    protected function getPlayerData()
    {

        $liveVotingPlayer = LiveVotingPlayer::loadFromObjId($this->object->getLiveVoting()->getId());

        $liveVotingPlayer->attend();

        $param_manager = ParamManager::getInstance();

        //Set Active Voting of Presenter via URL - bot don't save it - PLLV-272
        if ($param_manager->getVoting() > 0) {
            $this->object->getLiveVoting()->getPlayer()->setActive($param_manager->getVoting());
        }

        $liveVotingUI = new LiveVotingUI($this->object->getLiveVoting());

        try {
            $results = array(
                'player' => $this->object->getLiveVoting()->getPlayer()->getPlayerData(),
                'player_html' => $liveVotingUI->getPlayerHTML(true),
                //'buttons_html' => $this->getButtonsHTML(),
                'buttons_html' => ""
            );

            LiveVotingJs::sendResponse($results);

        } catch (LiveVotingException|ilTemplateException|ilException $e) {
            $this->tpl->setContent($this->renderer->render($this->factory->messageBox()->failure($e->getMessage())));
        }

        //xlvoJsResponse::getInstance($results)->send();
    }

    public function confirmNewRound(): void
    {
        global $DIC;

        $this->tabs->activateTab("tab_results");

        $conf = new ilConfirmationGUI();
        $conf->setFormAction($this->ctrl->getFormAction($this));
        $conf->setHeaderText($this->plugin->txt('common_confirm_new_round'));
        $conf->setConfirm($this->plugin->txt("common_new_round"), "newRound");
        $conf->setCancel($this->plugin->txt('common_cancel'), "results");
        $DIC->ui()->mainTemplate()->setContent($conf->getHTML());
    }

    /**
     * @throws LiveVotingException
     * @throws ilCtrlException
     */
    public function newRound(): void
    {
        global $DIC;

        $obj_id = $this->object->getLiveVoting()->getId();
        $lastRound = LiveVotingRound::getLatestRound($obj_id);
        $newRound = new LiveVotingRound();
        $newRound->setObjId($obj_id);
        $newRound->setRoundNumber($lastRound->getRoundNumber() + 1);
        $newRound->save();

        $DIC->ctrl()->setParameter($this, 'round_id', LiveVotingRound::getLatestRound($obj_id)->getId());
        $_SESSION['onscreen_message'] = array('type' => 'success', 'msg' => $this->plugin->txt("common_new_round_created"));
        $DIC->ctrl()->redirect($this, "results");
    }

    /**
     * @throws ilCtrlException
     */
    public function changeRound(): void
    {
        global $DIC;

        $round = $_POST['round_id'];
        $DIC->ctrl()->setParameter($this, 'round_id', $round);
        $DIC->ctrl()->redirect($this, "results");
    }

    /**
     * @throws ilException
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    public function applyFilter(): void
    {
        global $DIC;

        $table = new LiveVotingResultsTableGUI($this, "results");
        LiveVOtingResultsUI::buildFilters($table, (int) $_GET['round_id'], $this->object->getLiveVoting()->getQuestions());
        $table->initFilter();
        $table->writeFilterToSession();
        $DIC->ctrl()->redirect($this, "results");
    }

    /**
     * @throws ilException
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    public function resetFilter(): void
    {
        global $DIC;

        $table = new LiveVotingResultsTableGUI($this, "results");
        LiveVOtingResultsUI::buildFilters($table, (int) $_GET['round_id'], $this->object->getLiveVoting()->getQuestions());
        $table->initFilter();
        $table->resetFilter();
        $DIC->ctrl()->redirect($this, "results");
    }


    /**
     * @throws LiveVotingException
     */
    protected function apiCall(): void
    {
        $return_value = true;

        $liveVoting = $this->object->getLiveVoting();

        switch ($_POST['call']) {
            case 'toggle_freeze':
                $param_manager = ParamManager::getInstance();
                $liveVoting->getPlayer()->toggleFreeze($param_manager->getVoting());
                break;
            case 'toggle_results':
                $liveVoting->getPlayer()->toggleResults();
                break;
            case 'reset':
                $liveVoting->getPlayer()->reset();
                break;
            case 'next':
                $liveVoting->getPlayer()->nextQuestion();
                break;
            case 'previous':
                $liveVoting->getPlayer()->previousQuestion();
                break;
            case 'open':
                $liveVoting->getPlayer()->open((int) $_POST["xvi"]);
                break;
            case 'countdown':
                $liveVoting->getPlayer()->startCountDown((int) $_POST['seconds']);
                break;
            case 'input':
                global $DIC;
                LiveVotingParticipant::getInstance()->setIdentifier($DIC->user()->getId())->setType(1);
                $this->input(['input' => $_POST['input']]);
                break;
            case 'add_vote':
                $vote = new LiveVotingVote();
                $user = LiveVotingParticipant::getInstance();
                $vote->setUserId((int) $user->getIdentifier());
                $vote->setUserIdType(1);
                $vote->setVotingId($liveVoting->getPlayer()->getActiveVoting());
                $options = $liveVoting->getPlayer()->getActiveVotingObject()->getOptions();
                $var=array_values($options);
                $option = array_shift($var);
                $vote->setOptionId($option->getId());
                $vote->setType(2);
                $vote->setStatus(1);
                $vote->setFreeInput($_POST['input']);
                $vote->setRoundId(LiveVotingRound::getLatestRoundId($liveVoting->getId()));
                $vote->save();

                $return_value = ['vote_id' => $vote->getId()];

                break;
            case 'remove_vote':
                $vote = new LiveVotingVote((int) $_POST['vote_id']);
                $vote->delete();
                break;
            case 'add_category':
                $category = new LiveVotingCategory();
                $category->setTitle($_POST['title']);
                $category->setVotingId($liveVoting->getPlayer()->getActiveVoting());
                $category->setRoundId($liveVoting->getPlayer()->getRoundId());
                $category->save();
                $return_value = ['category_id' => $category->getId()];
                break;
            case 'remove_category':
                $database = new LiveVotingDatabase();

                $database->update('rep_robj_xlvo_vote_n', array(
                    "free_input_category" => 0
                ), array(
                    "voting_id" => $liveVoting->getPlayer()->getActiveVoting(),
                    "round_id" => $liveVoting->getPlayer()->getRoundId(),
                    "free_input_category" => $_POST['category_id']
                ));

                $database->delete('rep_robj_xlvo_cat', array(
                    "id" => $_POST['category_id']
                ));

                break;
            case 'change_category':
                $vote = new LiveVotingVote((int) $_POST['vote_id']);

                $database = new LiveVotingDatabase();

                $votes = $database->select('rep_robj_xlvo_vote_n', array('id'), array(
                    'voting_id'           => $vote->getVotingId(),
                    'round_id'            => $vote->getRoundId(),
                    'free_input'          => $vote->getFreeInput(),
                    'free_input_category' => $vote->getFreeInputCategory()
                ));

                foreach ($votes as $vote) {
                    $vote = new LiveVotingVote($vote['id']);
                    $vote->setFreeInputCategory((int) $_POST['category_id']);
                    $vote->save();
                }

                break;
            default:
                $return_value = false;
                break;
        }

        LiveVotingJs::sendResponse($return_value);
    }



    /**
     * @throws LiveVotingException
     */
    private function input(array $array): void
    {
        $liveVoting = $this->object->getLiveVoting();

        foreach ($array as $item) {
            $vote = new LiveVotingVote((int) $item['vote_id']);
            $user = LiveVotingParticipant::getInstance();

            if ($user->getType() == 1) {
                $vote->setUserId((int) $user->getIdentifier());
                $vote->setUserIdType(0);
            } else {
                $vote->setUserIdentifier($user->getIdentifier());
                $vote->setUserIdType(1);
            }

            $vote->setVotingId($liveVoting->getPlayer()->getActiveVoting());
            $options = $liveVoting->getPlayer()->getActiveVotingObject()->getOptions();
            $var=array_values($options);
            $option = array_shift($var);
            $vote->setOptionId($option->getId());
            $vote->setType(2);
            $vote->setStatus(1);
            $vote->setFreeInput($item['input']);
            $vote->setRoundId(LiveVotingRound::getLatestRoundId($liveVoting->getId()));
            $vote->save();
            if (!$liveVoting->getPlayer()->getActiveVotingObject()->isMultiFreeInput()) {
                $liveVoting->getPlayer()->unvoteAll($vote->getId());
            }
        }

        if ($liveVoting->isVotingHistory()) {
            LiveVotingVote::createHistoryObject(LiveVotingParticipant::getInstance(), $liveVoting->getPlayer()->getActiveVotingObject()->getId(), $liveVoting->getPlayer()->getRoundId());
        }
    }
}