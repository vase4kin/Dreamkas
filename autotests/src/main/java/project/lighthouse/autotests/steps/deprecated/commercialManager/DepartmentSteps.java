package project.lighthouse.autotests.steps.deprecated.commercialManager;

import net.thucydides.core.annotations.Step;
import net.thucydides.core.steps.ScenarioSteps;
import org.jbehave.core.model.ExamplesTable;
import project.lighthouse.autotests.pages.deprecated.commercialManager.department.DepartmentCardPage;
import project.lighthouse.autotests.pages.deprecated.commercialManager.department.DepartmentCreatePage;
import project.lighthouse.autotests.pages.deprecated.commercialManager.store.StoreCardPage;

public class DepartmentSteps extends ScenarioSteps {
    StoreCardPage storeCardPage;
    DepartmentCreatePage departmentCreatePage;
    DepartmentCardPage departmentCardPage;

    @Step
    public void clickCreateNewDepartmentButton() {
        storeCardPage.createNewDepartmentButton().click();
    }

    @Step
    public void clickCreateDepartmentSubmitButton() {
        departmentCreatePage.submitButton().click();
    }

    @Step
    public void fillStoreFormData(ExamplesTable formData) {
        departmentCreatePage.input(formData);
    }

    @Step
    public void navigateToDepartmentPage(String departmentId, String storeId) {
        departmentCardPage.navigateToDepartmentCardPage(departmentId, storeId);
    }

    @Step
    public void clicksEditDepartmentLink() {
        departmentCardPage.editButton().click();
    }
}
