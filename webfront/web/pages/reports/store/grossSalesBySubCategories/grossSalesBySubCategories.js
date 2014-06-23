define(function(require, exports, module) {
    //requirements
    var Page = require('kit/core/page.deprecated'),
        currentUserModel = require('models/currentUser.inst'),
        GrossSalesBySubCategoriesCollection = require('collections/grossSalesBySubCategories');

    require('jquery');

    return Page.extend({
        __name__: module.id,
        params: {
            storeId: null,
            groupId: null,
            categoryId: null
        },
        partials: {
            '#content': require('ejs!./content.html')
        },
        permissions: function(){
            return !LH.isReportsAllow(['grossSalesBySubCategories']);
        },
        models: {
        },
        collections: {
            grossSalesBySubCategories: function(){
                var page = this;

                var grossSalesBySubCategoriesCollection = new GrossSalesBySubCategoriesCollection();

                grossSalesBySubCategoriesCollection.storeId = page.params.storeId;
                grossSalesBySubCategoriesCollection.categoryId = page.params.categoryId;

                return grossSalesBySubCategoriesCollection;
            }
        },
        initialize: function(){
            var page = this;

            page.collections = _.transform(page.collections, function(result, collection, collectionName) {
                result[collectionName] = typeof collection === 'function' ? collection.call(page) : collection
            });

            $.when(page.collections.grossSalesBySubCategories.fetch()).done(function(){
                page.render();
            });
        }
    });
});