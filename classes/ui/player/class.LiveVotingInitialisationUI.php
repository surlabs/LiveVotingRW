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

namespace LiveVoting\player;

use ilGlobalTemplate;
use ILIAS\DI\Exceptions\Exception;
use ilIniFile;
use ilLiveVotingPlugin;
use ilSetting;
use LiveVoting\Context\Initialisation\Version\v7\xlvoBasicInitialisation;
use LiveVoting\Context\Initialisation\Version\v7\xlvoStyleDefinition;
use LiveVoting\Context\xlvoILIAS;
use LiveVoting\Context\xlvoInitialisation;
use LiveVoting\platform\LiveVotingContext;

class LiveVotingInitialisationUI
{
    const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    /**
     * @var ilIniFile
     */
    protected ilIniFile $iliasIniFile;

    /**
     * @var ilSetting
     */
    protected ilSetting $settings;

    /**
     * @throws Exception
     */
    protected function _construct($context = null)
    {
        if($context){
            LiveVotingContext::setContext($context);
        }

        //$this->bootstrapApp();
    }

    /**
     * @param int|null $context
     * @return LiveVotingInitialisationUI
     */
    public static function init(int $context = null): LiveVotingInitialisationUI
    {
        return new self($context);
    }

/*    private function bootstrapApp()
    {
        //bootstrap ILIAS

        //file_put_contents( "/var/www/html/ilias/tmp" . "/" . "debug.txt", "v7/bootstrapApp" . "\n",FILE_APPEND);

        $this->initDependencyInjection();
        $this->setCookieParams();
        $this->removeUnsafeCharacters();
        $this->loadIniFile();
        $this->requireCommonIncludes();
        $this->initErrorHandling();
        $this->determineClient();
        $this->initHTTPServices($GLOBALS["DIC"]);
        $this->loadClientIniFile();
        $this->initDatabase();
        $this->initLog(); //<-- required for ilCtrl error messages
        $this->initSessionHandler();
        $this->initSettings();  //required
        $this->initLocale();
        $this->buildHTTPPath();
        $this->initCore();
        $this->initUser();
        $this->initLanguage();
        $this->initTree();
        $this->initComponentService($GLOBALS["DIC"]);
        $this->initControllFlow();
        $this->initAccessHandling();
        $this->initObjectDefinition();
        $this->initAccess();
        $this->initAppEventHandler();
        $this->initMail();
        $this->initFilesystem();
        $this->initResourceStorage();
        $this->initGlobalScreen($GLOBALS["DIC"]);
        $this->initTemplate();
        $this->initTabs();
        $this->initNavigationHistory();
        $this->initHelp();
        xlvoInitialisation::initUIFramework(self::dic()->dic());
    }*/

    /**
     * Remove unsafe characters from GET
     */
    protected function removeUnsafeCharacters()
    {
        // Remove unsafe characters from GET parameters.
        // We do not need this characters in any case, so it is
        // feasible to filter them everytime. POST parameters
        // need attention through ilUtil::stripSlashes() and similar functions)
        if (is_array($_GET)) {
            foreach ($_GET as $k => $v) {
                // \r\n used for IMAP MX Injection
                // ' used for SQL Injection
                $_GET[$k] = str_replace(array(
                    "\x00",
                    "\n",
                    "\r",
                    "\\",
                    "'",
                    '"',
                    "\x1a",
                ), "", $v);

                // this one is for XSS of any kind
                $_GET[$k] = strip_tags($_GET[$k]);
            }
        }
    }

    private function initTemplate()
    {
        /*$styleDefinition = new xlvoStyleDefinition();
        $this->makeGlobal('styleDefinition', $styleDefinition);*/

        /*$ilias = new xlvoILIAS();
        $this->makeGlobal("ilias", $ilias);*/
        //TODO: Implementar esto si finalmente se usa.

        $tpl = new ilGlobalTemplate("tpl.main.html", true, true, "Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting", "DEFAULT", true);

        //$participant
    }

}