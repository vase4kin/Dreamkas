define(function(require) {
        //requirements
        var Tooltip_menu = require('blocks/tooltip/tooltip_menu/tooltip_menu'),
            CatalogSubcategoryModel = require('models/catalogSubcategory'),
            Tooltip_catalogSubcategoryForm = require('blocks/tooltip/tooltip_catalogSubcategoryForm/tooltip_catalogSubcategoryForm');

        return Tooltip_menu.extend({
            blockName: 'tooltip_catalogSubcategoryMenu',
            catalogSubcategoryModel: new CatalogSubcategoryModel(),
            templates: {
                content: require('tpl!blocks/tooltip/tooltip_catalogSubcategoryMenu/templates/content.html')
            },
            events: {
                'click .tooltip__editLink': function(e) {
                    e.preventDefault();
                    var block = this,
                        $target = $(e.target);

                    block.tooltip_catalogSubcategoryForm.show({
                        model: block.catalogSubcategoryModel,
                        collection: null,
                        $trigger: $target
                    });

                    block.hide();
                },
                'click .tooltip__removeLink': function(e) {
                    e.preventDefault();
                    var block = this,
                        $target = $(e.target);

                    if ($target.hasClass('preloader_rows')) {
                        return;
                    }

                    if (block.catalogSubcategoryModel.products && block.catalogSubcategoryModel.products.length) {
                        alert('Необходимо удалить все товары из подкатегории');
                        block.hide();
                    } else {
                        $target.addClass('preloader_rows');
                        block.catalogSubcategoryModel.destroy({
                            success: function() {
                                $target.removeClass('preloader_rows');
                                block.hide();
                            }
                        });
                    }
                }
            },
            initialize: function() {
                var block = this;

                Tooltip_menu.prototype.initialize.call(this);

                block.tooltip_catalogSubcategoryForm = $('[block="tooltip_catalogSubcategoryForm"]').data('tooltip_catalogSubcategoryForm') || new Tooltip_catalogSubcategoryForm({
                    model: block.catalogSubcategoryModel
                });
            },
            remove: function(){
                var block = this;

                block.tooltip_catalogSubcategoryForm.remove();

                Tooltip_menu.prototype.remove.call(block);
            }
        });
    }
);