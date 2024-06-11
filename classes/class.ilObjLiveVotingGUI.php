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

use LiveVoting\UI\LiveVotingUI;

/**
 * Class ilObjLiveVotingGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilObjLiveVotingGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI, LiveVotingUI
 * @ilCtrl_Calls      ilObjLiveVotingGUI: ilObjectCopyGUI, ilPermissionGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI, LiveVotingUI
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
            case 'showContent':
            case 'showContentAfterCreation':
            case 'editProperties':
            case 'updateProperties':
                $this->{$cmd}();
                break;
        }
    }

    public function showContentAfterCreation(): void
    {
        global $DIC;
        $liveVotingUI = new LiveVotingUI();

        $this->tpl->setContent($liveVotingUI->showContent());
    }

    /**
     * @throws ilCtrlException
     */
    public function showContent(): void
    {
        $liveVotingUI = new LiveVotingUI();
        $this->setSubTabs('tab_content', 'subtab_show');
        $this->tpl->setContent($liveVotingUI->showContent());
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
        global $DIC;

        $this->tabs->addTab('tab_content', $this->txt('tab_content'), $DIC->ctrl()->getLinkTarget($this, 'showContent'));
        $this->addInfoTab();
        parent::setTabs();
    }

    /**
     * @throws ilCtrlException
     */
    protected function setSubTabs($tab, $active_subtab = null): void
    {
        global $DIC;
        $this->tabs->activateTab($tab);
        if($tab == 'tab_content'){
            $this->tabs->addSubTab('subtab_show', $this->txt('subtab_show'), $this->ctrl->getLinkTarget($this, "index"));
/*            if (ilObjLiveVotingAccess::hasWriteAccess()) {
                self::dic()->tabs()->addSubTab(self::SUBTAB_EDIT, self::plugin()->translate(self::SUBTAB_EDIT), self::dic()->ctrl()
                    ->getLinkTargetByClass(xlvoVotingGUI::class, xlvoVotingGUI::CMD_STANDARD));
            }*/
            //TODO: Implementación de hasWriteAccess()
        }


        if($active_subtab){
            $this->tabs->activateSubTab($active_subtab);

        }

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
}