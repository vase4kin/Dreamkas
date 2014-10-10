define(function(require, exports, module) {
    //requirements
	var Modal_stockMovement = require('blocks/modal/stockMovement/stockMovement');

    return Modal_stockMovement.extend({
		id: 'modal_supplierReturn',
		formId: 'form_supplierReturn',
		Model: require('resources/supplierReturn/model'),
		Form: require('blocks/form/supplierReturn/supplierReturn'),
		Form_products: require('blocks/form/stockMovementProducts/supplierReturn/supplierReturn'),
		addTitle: 'Возврат поставщику',
		editTitle: 'Редактирование возврата',
		removeCaption: 'Удалить возврат'
    });
});