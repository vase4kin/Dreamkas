package ru.dreamkas.pageObjects;

import net.thucydides.core.annotations.findby.FindBy;

import org.openqa.selenium.WebElement;

public class LoginPage extends CommonPageObject {

    @FindBy(id = "ru.dreamkas.pos.debug:id/description")
    private WebElement description;

    @FindBy(id = "ru.dreamkas.pos.debug:id/txtUsername")
    private WebElement txtUsernameField;

    @FindBy(id = "ru.dreamkas.pos.debug:id/txtPassword")
    private WebElement txtPasswordField;

    @FindBy(id = "ru.dreamkas.pos.debug:id/btnLogin")
    private WebElement  loginButton;

    public void inputLoginCredentials(String email, String password) {
        txtUsernameField.sendKeys(email);
        txtPasswordField.sendKeys(password);
    }

    public void clickOnLoginButton() {
        loginButton.click();
    }

    public String getDescription() {
        return description.getText();
    }
}
