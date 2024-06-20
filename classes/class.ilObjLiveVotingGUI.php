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

use LiveVoting\UI\LiveVotingChoicesUI;
use LiveVoting\UI\LiveVotingFreeInputUI;
use LiveVoting\UI\LiveVotingManageUI;
use LiveVoting\UI\LiveVotingRangeUI;
use LiveVoting\UI\LiveVotingSettingsUI;
use LiveVoting\UI\LiveVotingUI;

/**
 * Class ilObjLiveVotingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjLiveVotingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI, LiveVotingUI, LiveVotingChoicesUI, LiveVotingManageUI, LiveVotingSettingsUI, LiveVotingFreeInputUI, LiveVotingRangeUI
 * @ilCtrl_Calls      ilObjLiveVotingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI, LiveVotingUI, LiveVotingChoicesUI, LiveVotingManageUI, LiveVotingSettingsUI, LiveVotingFreeInputUI, LiveVotingRangeUI
 */
class ilObjLiveVotingGUI extends ilObjectPluginGUI
{

    public function getType(): string
    {
        return "xlvo";
    }

    public function getAfterCreationCmd(): string
    {
        return 'showContentAfterCreation';
    }

    public function getStandardCmd(): string
    {
        return 'showContent';
    }

    /**
     * @throws ilCtrlException
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;
        $DIC->help()->setScreenIdComponent(ilLiveVotingPlugin::PLUGIN_ID);
        //$cmd = $DIC->ctrl()->getCmd('showContent');
        $DIC->ui()->mainTemplate()->setPermanentLink(ilLiveVotingPlugin::PLUGIN_ID, $this->ref_id);

        $this->initHeaderAndLocator();

        switch ($cmd){
            case 'index':
                $this->showContent();
                break;
            case 'showContentAfterCreation':
            case 'editProperties':
            case 'manage':
            case 'selectType':
            case 'selectedChoices':
            case 'selectedFreeInput':
            case 'selectedRange':
            case 'updateProperties':
                $this->{$cmd}();
                break;
            case 'edit':
                $this->editQuestion();
                break;
        }
    }

    public function showContentAfterCreation(): void
    {
        global $DIC;
        $liveVotingUI = new LiveVotingUI();
        //$this->setSubTabs('tab_content', 'subtab_show');
        $this->tabs->activateTab("tab_content");

        try {
            $this->tpl->setContent($liveVotingUI->showIndex());
        } catch (ilSystemStyleException|ilTemplateException $e) {
            //TODO: Mostrar error
        }
    }

    public function showContent(): void
    {
        $liveVotingUI = new LiveVotingUI();
        $this->tabs->activateTab("tab_content");
        try {
            $this->tpl->setContent($liveVotingUI->showIndex());
        } catch (ilSystemStyleException|ilTemplateException $e) {
            //TODO: Mostrar error

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
            $this->tpl->setContent("Error de acceso");
            //TODO: Mostrar error


        } elseif (ilObjLiveVotingAccess::hasWriteAccess()) {
            $liveVotingManageUI = new LiveVotingManageUI();
            try {
/*                $DIC->toolbar()->addComponent($DIC->ui()->factory()->button()->primary($this->txt('voting_add'), $this->ctrl->getLinkTarget($this, "selectType")));
                $DIC->toolbar()->addComponent($DIC->ui()->factory()->button()->standard($this->txt('voting_reset_all'), $this->ctrl->getLinkTarget($this, "selectType")));*/

                $this->tpl->setContent($liveVotingManageUI->showManage($this));
            } catch (ilSystemStyleException|ilTemplateException $e) {
                //TODO: Mostrar error
            }


        }
        //$this->tpl->setContent("Contenido de la pestaña de edición");
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
        $saving_info = "";
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
            //$form = $liveVotingChoicesUI->getChoicesForm();

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
        $form = $liveVotingFreeInputUI->getChoicesForm();
        $saving_info = "";
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
            //$form = $liveVotingChoicesUI->getChoicesForm();

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
        $form = $liveVotingRangeUI->getChoicesForm();
        $saving_info = "";
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
            //$form = $liveVotingChoicesUI->getChoicesForm();

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
        }
        //TODO: Traer prev y next question para navegación



    }
}