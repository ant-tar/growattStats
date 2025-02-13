growattStats.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'growattstats-panel-home',
            renderTo: 'growattstats-panel-home-div'
        }]
    });
    growattStats.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(growattStats.page.Home, MODx.Component);
Ext.reg('growattstats-page-home', growattStats.page.Home);