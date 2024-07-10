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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use LiveVoting\platform\LiveVotingConfig;
use LiveVoting\platform\LiveVotingException;


/**
 * Class ilLiveVotingConfigGUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_IsCalledBy  ilLiveVotingConfigGUI: ilObjComponentSettingsGUI
 */
class ilLiveVotingConfigGUI extends ilPluginConfigGUI
{
    const REWRITE_RULE_VOTE = "RewriteRule ^/?vote(/\\w*)? /Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/pin.php?xlvo_pin=$1 [L]";
    const REWRITE_RULE_PRESENTER = "RewriteRule ^/?presenter(/\\w*)(/\\w*)(/\\w*)?(/\\w*)? /Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/presenter.php?xlvo_pin=$1&xlvo_puk=$2&xlvo_voting=$3&xlvo_ppt=$4 [L]";

    protected Factory $factory;
    protected Renderer $renderer;
    protected \ILIAS\Refinery\Factory $refinery;
    protected ilCtrlInterface $control;
    protected ilGlobalTemplateInterface $tpl;
    protected $request;

    /**
     * @throws ilException
     * @throws ilCtrlException
     * @throws LiveVotingException
     */
    public function performCommand(string $cmd): void
    {
        global $DIC;
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
        $this->refinery = $DIC->refinery();
        $this->control = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->request = $DIC->http()->request();

        switch ($cmd) {
            case "configure":
                LiveVotingConfig::load();
                $this->control->setParameterByClass('ilLiveVotingConfigGUI', 'cmd', 'configure');
                $form_action = $this->control->getLinkTargetByClass("ilLiveVotingConfigGUI", "configure");
                $rendered = $this->renderForm($form_action, $this->buildForm());
                break;
            default:
                throw new ilException("command not defined");

        }

        $this->tpl->setContent($rendered);
    }

    /**
     * @throws LiveVotingException
     */
    private function buildForm(): array
    {
        $shortlink_vote = $this->factory->input()->field()->text(
            $this->plugin_object->txt('config_allow_shortlink_link'), $this->plugin_object->txt('config_allow_shortlink_link_info')
        )->withValue((string) LiveVotingConfig::get("allow_shortlink_link"))->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                LiveVotingConfig::set('allow_shortlink_link', $v);
            }
        ));

        $base_url_vote = $this->factory->input()->field()->text(
            $this->plugin_object->txt('config_base_url'), $this->plugin_object->txt('config_base_url_info')
        )->withValue((string) LiveVotingConfig::get("base_url"))->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                LiveVotingConfig::set('allow_shortlink_link', $v);
            }
        ));

        $use_shortlink_vote = $this->factory->input()->field()->optionalGroup(array(
            $shortlink_vote, $base_url_vote
        ), $this->plugin_object->txt('config_allow_shortlink'), $this->plugin_object->txt('config_allow_shortlink_info') . '<br><br><span class="label label-default">' . self::REWRITE_RULE_VOTE . '</span><br><br>'
        );

        $shortlink_presenter = $this->factory->input()->field()->text(
            $this->plugin_object->txt('config_allow_shortlink_link_presenter'), $this->plugin_object->txt('config_allow_shortlink_link_presenter_info')
        )->withValue((string) LiveVotingConfig::get("allow_shortlink_link_presenter"))->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                LiveVotingConfig::set('allow_shortlink_link_presenter', $v);
            }
        ));

        $use_shortlink_presenter = $this->factory->input()->field()->optionalGroup(array(
            $shortlink_presenter
        ), $this->plugin_object->txt('config_allow_shortlink_presenter'), $this->plugin_object->txt('config_allow_shortlink_presenter_info') . '<br><br><span class="label label-default">' . self::REWRITE_RULE_PRESENTER . '</span><br><br>'
        );

        $request_frequency = $this->factory->input()->field()->numeric(
            $this->plugin_object->txt('config_request_frequency'), $this->plugin_object->txt('config_request_frequency_info')
        )->withValue(LiveVotingConfig::get("request_frequency") != "" ? (int) LiveVotingConfig::get("request_frequency") : 1)->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                LiveVotingConfig::set('request_frequency', $v);
            }
        ));

        $api_type = $this->factory->input()->field()->select(
            $this->plugin_object->txt('config_api_type'),
            array(
                1 => "JSON",
                2 => "XML"
            )
        )->withValue(LiveVotingConfig::get("api_type") != "" ? (int) LiveVotingConfig::get("api_type") : 1)->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                LiveVotingConfig::set('api_type', $v);
            }
        ));

        $result_api = $this->factory->input()->field()->optionalGroup(array(
            $api_type
        ), $this->plugin_object->txt('config_result_api'), $this->plugin_object->txt('config_result_api_info')
        );

        $global_cache_enabled = $this->factory->input()->field()->checkbox(
            $this->plugin_object->txt('config_use_global_cache'), $this->plugin_object->txt('config_use_global_cache_info')
        )->withValue((boolean) LiveVotingConfig::get("use_global_cache"))->withAdditionalTransformation($this->refinery->custom()->transformation(
            function ($v) {
                LiveVotingConfig::set('use_global_cache', $v ? "1" : "0");
            }
        ));

        return array(
            $use_shortlink_vote,
            $use_shortlink_presenter,
            $request_frequency,
            $result_api,
            $global_cache_enabled
        );
    }

    private function renderForm(string $form_action, array $sections): string
    {
        $form = $this->factory->input()->container()->form()->standard(
            $form_action,
            $sections
        );

        $saving_info = "";

        if ($this->request->getMethod() == "POST") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();
            if ($result) {
                $saving_info = $this->save();
            }
        }

        return $saving_info . $this->renderer->render($form);
    }

    public function save(): string
    {
        LiveVotingConfig::save();
        return $this->renderer->render($this->factory->messageBox()->success($this->plugin_object->txt('config_msg_success')));
    }
}