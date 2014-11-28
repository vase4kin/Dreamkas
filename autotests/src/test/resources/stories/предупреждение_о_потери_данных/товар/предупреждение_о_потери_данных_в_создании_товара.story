Meta:
@us_130

Narrative:
Как владелец магазина,
Я хочу видеть предупреждение, закрывая форму приёмки, оприходования, списания, возврата поставщику, создания товара, создания поставщика, создания магазина, о том, что введённые данные не будут сохранены,
Чтобы точно понимать последствия своих действий

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story

Scenario: Появление сообщения о потери данных при создании товара при изменении поля наименование

Meta:
@smoke

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение 'text' в поле с именем 'name'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что текст алерта гласит 'Изменения не будут сохранены. Отменить изменения?'

Scenario: Появление сообщения о потери данных при создании товара при изменении поля еденица измерения

Meta:

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение 'text' в поле с именем 'unit'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что текст алерта гласит 'Изменения не будут сохранены. Отменить изменения?'

Scenario: Появление сообщения о потери данных при создании товара при изменении поля штрикод

Meta:

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение 'text' в поле с именем 'barcode'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что текст алерта гласит 'Изменения не будут сохранены. Отменить изменения?'

Scenario: Появление сообщения о потери данных при создании товара при изменении поля НДС

Meta:

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение '10%' в поле с именем 'vat'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что текст алерта гласит 'Изменения не будут сохранены. Отменить изменения?'

Scenario: Отсутствия сообщения о потери данных при создании товара при изменении поля НДС и возвращении старого значения

Meta:

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение '10%' в поле с именем 'vat'
And пользователь* вводит значение 'Не облагается' в поле с именем 'vat'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что алерта не появилось

Scenario: Появление сообщения о потери данных при создании товара при изменении поля цена закупки

Meta:

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение 'text' в поле с именем 'purchasePrice'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что текст алерта гласит 'Изменения не будут сохранены. Отменить изменения?'

Scenario: Появление сообщения о потери данных при создании товара при изменении поля цена продажи

Meta:

GivenStories: precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу группы с названием 'user-group1'
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку создать товар
And пользователь* находится в модальном окне 'создания товара'
And пользователь* вводит значение 'text' в поле с именем 'sellingPrice'

Then пользователь закрывает модальное окно по кнопке крестик и проверяет, что текст алерта гласит 'Изменения не будут сохранены. Отменить изменения?'