package taskflow;

import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;

import static org.junit.jupiter.api.Assertions.assertTrue;

class LoginTest extends BrowserTest {
    @Test
    void validCredentialsOpenDashboard() {
        login();
        assertTrue(driver.findElement(By.id("tasks-title")).isDisplayed());
    }

    @Test
    void incorrectPasswordShowsAnError() {
        String email = System.getenv("TASKFLOW_TEST_EMAIL");
        org.junit.jupiter.api.Assumptions.assumeTrue(email != null, "Set the test account email.");
        fillLogin(email, "incorrect-" + java.util.UUID.randomUUID());
        assertNotice("Sign-in failed. Check your email, password and email confirmation.");
    }

    @Test
    void emptyFieldsAreRejectedByPhp() {
        open("index.php");
        ((JavascriptExecutor) driver).executeScript("arguments[0].form.noValidate = true", input("email"));
        submit();
        assertNotice("Enter your email and password.");
    }

    @Test
    void dashboardRequiresAuthentication() {
        open("dashboard.php");
        assertTrue(driver.getCurrentUrl().endsWith("/index.php"));
    }
}
