package project.lighthouse.autotests.jbehave.authorization;

import net.thucydides.core.annotations.Steps;
import org.jbehave.core.annotations.BeforeScenario;
import org.jbehave.core.annotations.Given;
import org.jbehave.core.annotations.Then;
import org.jbehave.core.annotations.When;
import project.lighthouse.autotests.steps.AuthorizationSteps;

public class AuthorizationUserSteps {

    @Steps
    AuthorizationSteps authorizationSteps;

    @BeforeScenario()
    public void beforeScenario() {
        authorizationSteps.beforeScenario();
    }

    @Given("the user logs in as '$userName'")
    public void givenTheUSerLogsInAsUserName(String userName) {
        authorizationSteps.authorization(userName);
    }

    @Given("the user opens the authorization page")
    public void givenTheUserOpensAuthorizationPage() {
        authorizationSteps.openPage();
    }

    @When("the user logs in using '$userName' userName and '$password' password")
    public void givenTheUserLogsInUsingUserNameAndPassword(String userName, String password) {
        authorizationSteps.authorization(userName, password);
    }

    @When("the user logs in using '$userName' userName and '$password' password to check validation")
    public void givenTheUserLogsInUsingUserNameAndPasswordToCheckValidation(String userName, String password) {
        authorizationSteps.authorizationFalse(userName, password);
    }

    @When("the user logs out")
    public void whenTheUserLogsOut() {
        authorizationSteps.logOut();
    }

    @Then("the user checks that authorized is '$userName' user")
    public void thenTheUserChecksThatAuthorizedIsUser(String userName) {
        authorizationSteps.checkUser(userName);
    }

    @Then("the user checks the login form is present")
    public void thenTheUserChecksTheLoginFormIsPresent() {
        authorizationSteps.loginFormIsPresent();
    }
}
