define(function(require, exports, module) {
    //requirements
    var Page = require('kit/core/page'),
        SuppliersCollection = require('collections/suppliers'),
        Form_order = require('blocks/form/form_order/form_order');

    require('jquery');

    return Page.extend({
        __name__: module.id,
        partials: {
            '#content': require('tpl!./content.html')
        },
        permissions: function() {
            return !LH.isAllow('orders', 'POST');
        },
        initialize: function() {
            var page = this;

            page.collections = {
                suppliers: new SuppliersCollection()
            };

            $.when(page.collections.suppliers.fetch()).done(function() {
                page.render();
            });
        },
        render: function(){
            var page = this;

            Page.prototype.render.apply(page, arguments);

            page.blocks = {
                form_order: new Form_order()
            }
        }
    });
});