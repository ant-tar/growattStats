growattStats.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'growattstats-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('growattstats') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('growattstats_items'),
                layout: 'anchor',
                items: [{
                    html: _('growattstats_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'growattstats-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    growattStats.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(growattStats.panel.Home, MODx.Panel);
Ext.reg('growattstats-panel-home', growattStats.panel.Home);
