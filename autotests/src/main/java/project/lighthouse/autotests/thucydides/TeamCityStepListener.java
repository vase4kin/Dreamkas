package project.lighthouse.autotests.thucydides;

import net.thucydides.core.model.DataTable;
import net.thucydides.core.model.Story;
import net.thucydides.core.model.TestOutcome;
import net.thucydides.core.model.TestStep;
import net.thucydides.core.steps.ExecutedStepDescription;
import net.thucydides.core.steps.StepFailure;
import net.thucydides.core.steps.StepListener;
import org.apache.commons.lang.exception.ExceptionUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.util.*;

import static ch.lambdaj.Lambda.on;
import static ch.lambdaj.Lambda.sum;

public class TeamCityStepListener implements StepListener {

    private static final String messageTemplate = "##teamcity[%s %s]";
    private static final String propertyTemplate = " %s='%s'";

    private static final HashMap<String, String> escapeChars = new LinkedHashMap<String, String>() {
        {
            put("\\|", "||");
            put("\'", "|\'");
            put("\n", "|n");
            put("\r", "|r");
            put("\\[", "|[");
            put("\\]", "|]");
            put("[", "|[");
            put("]", "|]");
        }
    };

    private Logger logger;

    private Stack<String> suiteStack = new Stack<>();

    private Integer examplesTestCount = 0;
    private HashMap<Integer, String> exampleTestNames = new HashMap<>();

    public TeamCityStepListener(Logger logger) {
        this.logger = logger;
    }

    public TeamCityStepListener() {
        this(LoggerFactory.getLogger(TeamCityStepListener.class));
    }

    private String escapeProperty(String value) {
        for (Map.Entry<String, String> escapeChar : escapeChars.entrySet()) {
            value = value.replace(escapeChar.getKey(), escapeChar.getValue());
        }
        return value;
    }

    private void printMessage(String messageName, Map<String, String> properties) {
        StringBuilder propertiesBuilder = new StringBuilder();
        for (Map.Entry<String, String> property : properties.entrySet()) {
            propertiesBuilder.append(
                    String.format(
                            propertyTemplate,
                            property.getKey(),
                            escapeProperty(property.getValue())
                    )
            );
        }
        String message = String.format(messageTemplate, messageName, propertiesBuilder.toString());
        logger.info(message);
    }

    private void printMessage(String messageName, String description, Long duration) {
        Map<String, String> properties = new HashMap<>();
        properties.put("name", description);
        properties.put("duration", duration.toString());
        printMessage(messageName, properties);
    }

    private void printMessage(String messageName, String description) {
        Map<String, String> properties = new HashMap<>();
        properties.put("name", description);
        printMessage(messageName, properties);
    }

    @Override
    public void testSuiteStarted(Class<?> storyClass) {
        printMessage("testSuiteStarted", storyClass.getSimpleName());
    }

    @Override
    public void testSuiteStarted(Story story) {
        String storyName = story.getName();
        suiteStack.push(storyName);
        printTestSuiteStarted(storyName);
    }

    @Override
    public void testSuiteFinished() {
        String suiteName = suiteStack.pop();
        printTestSuiteFinished(suiteName);
    }

    @Override
    public void testStarted(String description) {
    }

    @Override
    public void testFinished(TestOutcome result) {
        if (result.isDataDriven()) {
            printExampleResults(result);
        } else if (!result.isDataDriven()) {
            printTestStarted(result);
            if (result.isFailure() || result.isError()) {
                printFailure(result);
            } else if (result.isSkipped() || result.isPending()) {
                printTestIgnored(result);
            }
            printTestFinished(result);
        }
    }

    @Override
    public void testRetried() {
    }

    private void printFailure(TestOutcome result) {
        HashMap<String, String> properties = new HashMap<>();
        properties.put("name", getResultTitle(result));
        properties.put("message", result.getTestFailureCause().getMessage());
        properties.put("details", getStepsInfo(result.getTestSteps()));
        printMessage("testFailed", properties);
    }

    private void printExampleResults(TestOutcome result) {
        List<TestStep> testSteps = result.getTestSteps();
        int number = 0;
        for (int i = 0; i < testSteps.size(); i++) {
            if (isExample(testSteps.get(i))) {
                List<TestStep> childrenTestSteps = result.getTestSteps().get(i).getChildren();
                String testName = getResultTitle(result, exampleTestNames.get(number));
                Long duration = sum(childrenTestSteps, on(TestStep.class).getDuration());
                printTestStarted(testName);
                if (hasFailureStep(childrenTestSteps)) {
                    String getStepsInfo = getStepsInfo(childrenTestSteps);
                    HashMap<String, String> properties = new HashMap<>();
                    properties.put("name", testName);
                    properties.put("details", getStepsInfo);
                    printMessage("testFailed", properties);
                } else if (hasPendingStep(childrenTestSteps)) {
                    printTestIgnored(testName);
                }
                printTestFinished(testName, duration);
                number++;
            }
        }
        examplesTestCount = 0;
        exampleTestNames.clear();
    }

    private boolean isExample(TestStep testStep) {
        return !testStep.getChildren().isEmpty() && testStep.getDescription().startsWith("[");
    }

    private String getStepsInfo(List<TestStep> testSteps) {
        StringBuilder builder = new StringBuilder("Steps:\r\n");
        for (TestStep testStep : testSteps) {
            String stepMessage = String.format("%s (%s) -> %s\r\n", testStep.getDescription(), testStep.getDurationInSeconds(), getResultMessage(testStep));
            builder.append(stepMessage);
        }
        return builder.append("\r\n").toString();
    }

    private Boolean hasFailureStep(List<TestStep> testSteps) {
        for (TestStep testStep : testSteps) {
            if (testStep.isError() || testStep.isFailure()) {
                return true;
            }
        }
        return false;
    }

    private Boolean hasPendingStep(List<TestStep> testSteps) {
        for (TestStep testStep : testSteps) {
            if (testStep.isSkipped() || testStep.isPending()) {
                return true;
            }
        }
        return false;
    }

    private String getResultMessage(TestStep testStep) {
        StringBuilder builder = new StringBuilder();
        builder.append(testStep.getResult().toString());
        if (testStep.isFailure() || testStep.isError()) {
            //TODO iterate through children to get exception
            String exceptionCause = testStep.getException() != null ? ExceptionUtils.getStackTrace(testStep.getException().getCause()) : "";
            builder.append(
                    String.format("\r\n\n%s", exceptionCause)
            );
        }
        return builder.toString();
    }

    private String getResultTitle(TestOutcome result) {
        String path = result.getPath();
        if (path.startsWith("stories/")) {
            path = path.substring(8);
        }
        if (path.endsWith(".story")) {
            path = path.substring(0, path.length() - 6);
        }
        String title = path.replace(".", "_").replace("/", ".");
        return title + "." + result.getMethodName().replace(".", "_");
    }

    private String getResultTitle(TestOutcome result, String name) {
        String title = getResultTitle(result);
        title = title + "." + name.replace(".", "_");
        return title;
    }

    private void printTestStarted(String name) {
        printMessage("testStarted", name);
    }

    private void printTestStarted(TestOutcome result) {
        printMessage("testStarted", getResultTitle(result));
    }

    private void printTestIgnored(TestOutcome result) {
        printMessage("testIgnored", getResultTitle(result));
    }

    private void printTestIgnored(String name) {
        printMessage("testIgnored", name);
    }

    private void printTestFinished(TestOutcome result) {
        printMessage("testFinished", getResultTitle(result), result.getDuration());
    }

    private void printTestFinished(String name, Long duration) {
        printMessage("testFinished", name, duration);
    }

    private void printTestSuiteFinished(String name) {
        printMessage("testSuiteFinished", name);
    }

    private void printTestSuiteStarted(String name) {
        printMessage("testSuiteStarted", name);
    }

    @Override
    public void testFailed(TestOutcome testOutcome, Throwable cause) {
        printMessage("testFailed", getResultTitle(testOutcome));
    }

    @Override
    public void testIgnored() {
        Map<String, String> properties = new HashMap<>();
        printMessage("testIgnored", properties);
    }

    @Override
    public void stepStarted(ExecutedStepDescription description) {
    }

    @Override
    public void skippedStepStarted(ExecutedStepDescription description) {
    }

    @Override
    public void stepFailed(StepFailure failure) {
    }

    @Override
    public void lastStepFailed(StepFailure failure) {
    }

    @Override
    public void stepIgnored() {
    }

    @Override
    public void stepPending() {
    }

    @Override
    public void stepPending(String message) {
    }

    @Override
    public void stepFinished() {
    }

    @Override
    public void notifyScreenChange() {
    }

    @Override
    public void useExamplesFrom(DataTable table) {
        //TODO run examples test in test suite
    }

    @Override
    public void exampleStarted(Map<String, String> data) {
        exampleTestNames.put(examplesTestCount, data.toString());
        examplesTestCount++;
    }

    @Override
    public void exampleFinished() {
    }

    @Override
    public void assumptionViolated(String message) {
    }
}
