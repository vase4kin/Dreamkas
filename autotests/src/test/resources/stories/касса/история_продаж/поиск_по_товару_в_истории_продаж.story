Meta:
@sprint_42
@us_112.2

Narrative:
Как владелец,
Я хочу увидеть список чеков, содержащих конкретный товар,
Чтобы найти продажу требуемую для возврата

Scenario: Проверка нахождения чека с товаром с помощью автокомплитного поиска товара по имени

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story,
              precondition/касса/создание_товара_и_чеков_для_проверки_автокомплитного_поиска_поля_истории_продаж.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь нажимает на кнопку чтобы показать боковое меню навигации кассы
And пользователь нажимает на ссылку с названием История продаж в боковом меню кассы

When пользователь* находится на странице 'истории продаж кассы'
And пользователь* вводит значение 'pos-product1' в поле с именем 'автокомплитное поле поиска товара'

Then пользователь ждет пока загрузится простой прелоадер

Then пользователь* проверяет, что список 'истории продаж' содержит точные данные
| price | date |
| 600,00 | saleTodayDate-1 |

Scenario: Проверка нахождения чека с товаром с помощью автокомплитного поиска товара по артикулу

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story,
              precondition/касса/создание_товара_и_чеков_для_проверки_автокомплитного_поиска_поля_истории_продаж.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь нажимает на кнопку чтобы показать боковое меню навигации кассы
And пользователь нажимает на ссылку с названием История продаж в боковом меню кассы

When пользователь* находится на странице 'истории продаж кассы'
And пользователь* вводит значение '10002' в поле с именем 'автокомплитное поле поиска товара'

Then пользователь ждет пока загрузится простой прелоадер

Then пользователь* проверяет, что список 'истории продаж' содержит точные данные
| price | date |
| 600,00 | saleTodayDate-1 |
| 350,00 | saleTodayDate-2 |

Scenario: Проверка нахождения разных чеков с товаром с помощью автокомплитного поиска товара

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story,
              precondition/касса/создание_товара_и_чеков_для_проверки_автокомплитного_поиска_поля_истории_продаж.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь нажимает на кнопку чтобы показать боковое меню навигации кассы
And пользователь нажимает на ссылку с названием История продаж в боковом меню кассы

When пользователь* находится на странице 'истории продаж кассы'
And пользователь* вводит значение 'pos-product2' в поле с именем 'автокомплитное поле поиска товара'

Then пользователь ждет пока загрузится простой прелоадер

Then пользователь* проверяет, что список 'истории продаж' содержит точные данные
| price | date |
| 600,00 | saleTodayDate-1 |
| 350,00 | saleTodayDate-2 |

Scenario: Проверка нахождения чека с товаром с помощью автокомплитного поиска товара в заданном диапазоне дат

Meta:
@smoke

GivenStories: precondition/customPrecondition/symfonyEnvInitPrecondition.story,
              precondition/касса/создание_юзера.story,
              precondition/касса/создание_магазина_с_товаром.story,
              precondition/касса/создание_товара_и_чеков_для_проверки_автокомплитного_поиска_поля_истории_продаж.story

Given пользователь открывает стартовую страницу авторизации
And пользователь авторизуется в системе используя адрес электронной почты 's41u1111@lighthouse.pro' и пароль 'lighthouse'

Given пользователь открывает страницу кассы магазина с названием 'store-s41u1111'

When пользователь нажимает на кнопку чтобы показать боковое меню навигации кассы
And пользователь нажимает на ссылку с названием История продаж в боковом меню кассы

When пользователь* находится на странице 'истории продаж кассы'
And пользователь* вводит значение 'pos-product2' в поле с именем 'автокомплитное поле поиска товара'

Then пользователь ждет пока загрузится простой прелоадер

When пользователь* вводит значение 'todayDate-1' в поле с именем 'дата с'

Then пользователь ждет пока загрузится простой прелоадер

When пользователь* вводит значение 'todayDate' в поле с именем 'дата по'

Then пользователь ждет пока загрузится простой прелоадер

Then пользователь* проверяет, что список 'истории продаж' содержит точные данные
| price | date |
| 600,00 | saleTodayDate-1 |