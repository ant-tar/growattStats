growattStats.window.CreateItem = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'growattstats-item-window-create';
    }
    Ext.applyIf(config, {
        title: _('growattstats_item_create'),
        width: 550,
        autoHeight: true,
        url: growattStats.config.connector_url,
        action: 'mgr/item/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    growattStats.window.CreateItem.superclass.constructor.call(this, config);
};
Ext.extend(growattStats.window.CreateItem, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('growattstats_item_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('growattstats_item_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('growattstats_item_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('growattstats-item-window-create', growattStats.window.CreateItem);


growattStats.window.UpdateItem = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'growattstats-item-window-update';
    }
    Ext.applyIf(config, {
        title: _('growattstats_item_update'),
        width: 550,
        autoHeight: true,
        url: growattStats.config.connector_url,
        action: 'mgr/item/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    growattStats.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(growattStats.window.UpdateItem, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('growattstats_item_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('growattstats_item_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%',
            height: 150,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('growattstats_item_active'),
            name: 'active',
            id: config.id + '-active',
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('growattstats-item-window-update', growattStats.window.UpdateItem);