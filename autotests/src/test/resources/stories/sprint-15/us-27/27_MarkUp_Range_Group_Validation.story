Meta:
@sprint 15
@us 27

Scenario: group mark up properties validation - min mark up validation good

Given the user validates min mark up with '1' value of the group with name 'GroupMarkUp-valid'
Then the user sees success message 'Свойства успешно сохранены' and logs out

Scenario: group mark up properties validation - min mark up validation eng small register

Given the user validates min mark up with 'abc' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - min mark up validation eng big register

Given the user validates min mark up with 'ABC' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - min mark up validation rus small register

Given the user validates min mark up with 'абв' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - min mark up validation rus big register

Given the user validates min mark up with 'АБВ' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - min mark up validation symbols

Given the user validates min mark up with '!"№;%:?*()' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - min mark up validation - Boundary-value analysis -99

Given the user validates min mark up with '-99' value of the group with name 'GroupMarkUp-valid'
Then the user sees success message 'Свойства успешно сохранены' and logs out

Scenario: group mark up properties validation - min mark up validation - Boundary-value analysis -99.99

Given the user validates min mark up with '-99.99' value of the group with name 'GroupMarkUp-valid'
Then the user sees success message 'Свойства успешно сохранены' and logs out

Scenario: group mark up properties validation - min mark up validation - Boundary-value analysis -100

Given the user validates min mark up with '-100' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть больше -100 |

Scenario: group mark up properties validation - min mark up validation - Boundary-value analysis -101

Given the user validates min mark up with '-101' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть больше -100 |

Scenario: group mark up properties validation - max mark up validation good

Given the user validates max mark up with '1' value of the group with name 'GroupMarkUp-valid'
Then the user sees success message 'Свойства успешно сохранены' and logs out

Scenario: group mark up properties validation - max mark up validation eng small register

Given the user validates max mark up with 'abc' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - max mark up validation eng big register

Given the user validates max mark up with 'ABC' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - max mark up validation rus small register

Given the user validates max mark up with 'абв' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - max mark up validation rus big register

Given the user validates max mark up with 'АБВ' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - max mark up validation symbols

Given the user validates max mark up with '!"№;%:?*()' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть числом |

Scenario: group mark up properties validation - max mark up validation - Boundary-value analysis -99

Given the user validates max mark up with '-99' value of the group with name 'GroupMarkUp-valid'
Then the user sees success message 'Свойства успешно сохранены' and logs out

Scenario: group mark up properties validation - max mark up validation - Boundary-value analysis -99.99

Given the user validates max mark up with '-99.99' value of the group with name 'GroupMarkUp-valid'
Then the user sees success message 'Свойства успешно сохранены' and logs out

Scenario: group mark up properties validation - max mark up validation - Boundary-value analysis -100

Given the user validates max mark up with '-100' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть больше -100 |

Scenario: group mark up properties validation - max mark up validation - Boundary-value analysis -101

Given the user validates max mark up with '-101' value of the group with name 'GroupMarkUp-valid'
Then the user sees error message and logs out
| error message|
| Значение должно быть больше -100 |

Scenario: Group mark up properties validation - min mark up cant be more than max mark up

Given there is the group with name 'GroupMarkUp-valid'
And the user navigates to the group with name 'GroupMarkUp-valid'
And the user logs in as 'commercialManager'
When the user clicks on start edition link and starts the edition
And the user switches to 'group' properties tab
And the user sets min mark up value to '2'
And the user sets max mark up value to '1'
And the user clicks save mark up button
Then the user sees error messages
| error message|
| Минимальная наценка не может быть больше максимальной |
When the user clicks on end edition link and ends the edition
And the user logs out