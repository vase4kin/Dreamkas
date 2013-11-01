package project.lighthouse.autotests.objects.notApi.search;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import project.lighthouse.autotests.objects.notApi.abstractObjects.AbstractSearchObjectCollection;
import project.lighthouse.autotests.objects.notApi.abstractObjects.AbstractSearchObjectNode;

public class WriteOffSearchObjectCollection extends AbstractSearchObjectCollection {

    public WriteOffSearchObjectCollection(WebDriver webDriver, By findBy) {
        super(webDriver, findBy);
    }

    @Override
    public AbstractSearchObjectNode createNode(WebElement element) {
        return new WriteOffSearchObject(element);
    }
}
