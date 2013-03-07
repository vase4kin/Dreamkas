Edit product story

Narrative:
In order to edit products to stocks
As a sales manager
I want to edit new products

Scenario: Editing product Ok
Given the user is on the order edit page
When the user edits 'Наименование23' in 'name' field
And the user edits 'Кефиромания' in 'vendor' field
And the user edits 'Страна' in 'vendorCountry' field
And the user edits '350' in 'purchasePrice' field
And the user edits '456456' in 'barcode' field
And the user selects 'unit' in 'unit' dropdown
And the user selects 'liter' in 'unit' dropdown
And the user selects 'kg' in 'unit' dropdown
And the user selects '1' in 'vat' dropdown
And the user selects '5' in 'vat' dropdown
And the user selects '10' in 'vat' dropdown
And the user edits '454545' in 'sku' field
And the user edits 'апапапап' in 'info' field
And the user clicks the cancel button

Scenario: Editing product Cancel
Given the user is on the order edit page
When the user edits 'Наименование23' in 'name' field
And the user edits 'Кефиромания' in 'vendor' field
And the user edits 'Страна' in 'vendorCountry' field
And the user edits '350' in 'purchasePrice' field
And the user edits '456456' in 'barcode' field
And the user selects 'unit' in 'unit' dropdown
And the user selects 'liter' in 'unit' dropdown
And the user selects 'kg' in 'unit' dropdown
And the user selects '1' in 'vat' dropdown
And the user selects '5' in 'vat' dropdown
And the user selects '10' in 'vat' dropdown
And the user edits '454545' in 'sku' field
And the user edits 'апапапап' in 'info' field
And the user clicks the edit button