package project.lighthouse.autotests.jbehave.deprecated.commercialManager.supplier;

import net.thucydides.core.annotations.Steps;
import org.jbehave.core.annotations.Then;
import project.lighthouse.autotests.steps.deprecated.commercialManager.supplier.SupplierMenuNavigationSteps;

public class ThenSupplierMenuNavigationSteps {

    @Steps
    SupplierMenuNavigationSteps supplierMenuNavigationSteps;

    @Then("the user asserts the create supplier link on supplier page menu navigation is not visible")
    public void thenTheUserAssertsTheCreateSupplierLinkOnSupplierPageMenuNavigationIsNotVisible() {
        supplierMenuNavigationSteps.assertCreateSupplierLinkIsNotVisible();
    }
}