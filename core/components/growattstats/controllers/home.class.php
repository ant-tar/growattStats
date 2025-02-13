<?php

/**
 * The home manager controller for growattStats.
 *
 */
class growattStatsHomeManagerController extends modExtraManagerController
{
    /** @var growattStats $growattStats */
    public $growattStats;


    /**
     *
     */
    public function initialize()
    {
        $this->growattStats = $this->modx->getService('growattStats', 'growattStats', MODX_CORE_PATH . 'components/growattstats/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['growattstats:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('growattstats');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->growattStats->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/growattstats.js');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->growattStats->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        growattStats.config = ' . json_encode($this->growattStats->config) . ';
        growattStats.config.connector_url = "' . $this->growattStats->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "growattstats-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="growattstats-panel-home-div"></div>';

        return '';
    }
}