Meta:
@us_128

Narrative:
При первом использовании Dreamkas,
Я хочу полностью создать приёмку товаров в магазине,
Чтобы не отвлекаться от работы на предварительное заполнение справочников

Scenario: Проверка наличия кнопки создать магазин при создании приемки если нет ни одного магазина в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'кнопка 'Создать магазин'' должен быть видимым

Scenario: Проверка наличия кнопки создать товар при создании приемки если нет ни одного товара в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'кнопка 'Создать товар'' должен быть видимым

Scenario: Проверка отсутствия кнопки создать магазин через плюсик при создании приемки если нет ни одного магазина в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'плюсик, чтобы создать новый магазин' должен быть невидимым

Scenario: Проверка отсутствия кнопки создать товар через плюсик при создании приемки если нет ни одного товара в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'плюсик, чтобы создать новый товар' должен быть невидимым

Scenario: Проверка наличия кнопки создать магазин через плюсик при создании приемки если есть магазины в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_магазина.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'плюсик, чтобы создать новый магазин' должен быть видимым

Scenario: Проверка наличия кнопки создать товар через плюсик при создании приемки если есть товары в системе в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_магазина.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'плюсик, чтобы создать новый товар' должен быть видимым

Scenario: Проверка отсутствия кнопки создать магазин при создании приемки если есть магазины в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_магазина.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'кнопка 'Создать магазин'' должен быть невидимым

Scenario: Проверка отсутствия кнопки создать товар при создании приемки если есть товары в системе в системе

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/пользователь/создание_юзера.story,
              precondition/магазин/создание_магазина.story,
              precondition/магазин/создание_товара.story

Given пользователь открывает страницу товародвижения
And пользователь авторизуется в системе используя адрес электронной почты 'user@lighthouse.pro' и пароль 'lighthouse'

When пользователь нажимает на кнопку Принять от поставщика
And пользователь* находится в модальном окне 'создания приемки'

Then пользователь* проверяет, что элемент с именем 'кнопка 'Создать товар'' должен быть невидимым