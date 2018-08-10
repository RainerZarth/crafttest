<?php
/**
*Tuersteher plugin für Craft CMS 3.0
*
*schützt die Seiten vor unangemeldeten Besuchern
**/

namespace craft;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\Application;
use craft\web\Session;
use craft\web\View;
use craft\events\RegisterUrlRulesEvent;
use craft\models\UserGroup;
use craft\controllers\UsersControllers;
use craft\events\RegisterUserPermissionEvent;
use craft\services\UserPermissions;
use craft\events\RegisterCpAlertsEvent;
use craft\helpers\Cp;

use yii\base\Event;

class Tuersteher extends BasePlugin{

    /**
     * @var Tuersteher
     */
    public static $plugin;

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->registerUserRights();

        $this->registerEventListeners();

        Craft::info(
            Craft::t(
                'tuersteher',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }
    public $hasCpSettings = true;
    public $hasCpSection = false;

    protected function isGuest(): bool
    {
        return Craft::$app->getUser()->getIsGuest();
    }

    public function hasPermission():bool{
        return ($user->can('Betrachten')) !== null;
    }

    protected function registerUserRights{
        //hier wird die Berechtigung erstellt für dieUser
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions['Betrachten der Seiten'] = [
                    'Betrachten' => ['Betrachten' => 'Betrachter'],
            ];
        });
    }

    protected function registerEventListeners
    {
        // Dev Mode prüfen
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_ALERTS,
            function(RegisterCpAlertsEvent $event) {
                if (\Craft::$app->config->general->devMode) {
                    $event->alerts[] = \Craft::t('tuersteher', 'Dev Mode is enabled!');
                }
            }
        );

        // Handler: EVENT_AFTER_LOAD_PLUGINS
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_LOAD_PLUGINS,
            function () {
                // Only respond to non-console site requests
                $request = Craft::$app->getRequest();
                if ($request->getIsSiteRequest() && !$request->getIsConsoleRequest()) {
                    $this->handleSiteRequests();
                }
            }
        );
    }

    protected function handleSiteRequests(){
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function(Event $event) {
                if (!$this->hasPermission()) {
                    Craft::$app->getResponse()->redirect('login');
                }
            }
        );
    }

}
?>