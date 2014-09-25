Meta:
@sprint_41
@us_111.3

Narrative:
Как владелец,
Я хочу редактировать товарную позицию в чеке продажи,
Чтобы исправить ошибку

Scenario: Редактирование товарной позиции в чеке

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |

When пользователь нажимает на товарную позицию в чеке с названием 'pos-product1'

When пользователь* находится в модальном окне 'редактирования товарной позиции'
And пользователь* в модальном окне вводит данные
| elementName | value |
| sellingPrice | 145,67 |
| quantity | 3,67 |

Then пользователь* в модальном окне проверяет, что поле с именем 'itemPrice' имеет значение '534,61'

When пользователь* в модальном окне нажимает на кнопку сохранения

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 3,67 шт. | 534,61 |

Then пользователь проверяет, что чек получился на сумму '534,61'

Scenario: Добавление количества товарной позиции с помощью плюса

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |

When пользователь нажимает на товарную позицию в чеке с названием 'pos-product1'

When пользователь* находится в модальном окне 'редактирования товарной позиции'
And пользователь нажимает на кнопку плюсик чтобы увеличить количество товарной позиции на единицу

Then пользователь* в модальном окне проверяет, что поле с именем 'quantity' имеет значение '2,00'

When пользователь* в модальном окне нажимает на кнопку сохранения

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 2,0 шт. | 220,00 |

Then пользователь проверяет, что чек получился на сумму '220,00'

Scenario: Уменьшение количества товарной позиции с помощью минуса

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 1,0 шт. | 110,00 |

When пользователь нажимает на товарную позицию в чеке с названием 'pos-product1'

When пользователь* находится в модальном окне 'редактирования товарной позиции'
And пользователь* в модальном окне вводит данные
| elementName | value |
| quantity | 4,04 |
And пользователь нажимает на кнопку минус чтобы уменьшить количество товарной позиции на единицу

Then пользователь* в модальном окне проверяет, что поле с именем 'quantity' имеет значение '3,04'

When пользователь* в модальном окне нажимает на кнопку сохранения

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product1 | 3,04 шт. | 334,40 |

Then пользователь проверяет, что чек получился на сумму '334,40'

Scenario: Открытие добавляемого товара в чек на редактирование, если у него не указана цена продажи

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь с адресом электронной почты 's41u1111@lighthouse.pro' создает продукт с именем 'pos-product-with-sellingPrice', еденицами измерения 'шт.', штрихкодом 'post-barcode-selling-price', НДС '0', ценой закупки '100' и ценой продажи '' в группе с именем 'pos-group1'

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product-with-sellingPrice' в поле с именем 'autocomplete'

When пользователь* находится в модальном окне 'редактирования товарной позиции'
And пользователь* в модальном окне вводит данные
| elementName | value |
| sellingPrice | 150,00 |

Then пользователь* в модальном окне проверяет, что поле с именем 'itemPrice' имеет значение '150,00'

When пользователь* в модальном окне нажимает на кнопку сохранения

Then пользователь проверяет, что коллекция добавленных продуктов в чек содержит следующие конкретные данные
| name | quantity | price |
| pos-product-with-sellingPrice | 1,0 шт. | 150,00 |

Then пользователь проверяет, что чек получился на сумму '150,00'

Scenario: Проверка заголовка окна редактирования товарной позиции в чеке

Meta:

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'
And пользователь нажимает на товарную позицию в чеке с названием 'pos-product1'

When пользователь* находится в модальном окне 'редактирования товарной позиции'

Then пользователь* в модальном окне проверяет, что заголовок равен 'Редактирование позиции в чеке'

Scenario: Проверка редактируемых данных товарной позиции в чеке

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'
And пользователь нажимает на товарную позицию в чеке с названием 'pos-product1'

When пользователь* находится в модальном окне 'редактирования товарной позиции'

Then пользователь* в модальном окне проверяет поля
| elementName | value |
| name | pos-product1 |
| barcode | post-barcode-1 |

Scenario: Удаление товарной позиции из чека

Meta:
@smoke

GivenStories: precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь* находится на странице 'выбранной кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'autocomplete'
And пользователь нажимает на товарную позицию в чеке с названием 'pos-product1'

When пользователь* находится в модальном окне 'редактирования товарной позиции'
And пользователь* в модальном окне нажимает на кнопку удаления
And пользователь* в модальном окне подтверждает удаление

Then пользователь проверяет, что коллекция товарных позиций в чеке пуста