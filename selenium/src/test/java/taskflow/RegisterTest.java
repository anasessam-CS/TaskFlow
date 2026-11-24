package taskflow;

import org.junit.jupiter.api.Test;
import org.openqa.selenium.JavascriptExecutor;

import static org.junit.jupiter.api.Assertions.assertEquals;

class RegisterTest extends BrowserTest {
    @Test
    void invalidEmailIsRejectedByPhp() {
        open("register.php");
        input("full_name").sendKeys("TaskFlow Tester");
        input("email").sendKeys("invalid-address");
        input("password").sendKeys("Strong-test-123");
        input("confirm_password").sendKeys("Strong-test-123");
        ((JavascriptExecutor) driver).executeScript("arguments[0].form.noValidate = true", input("email"));
        submit();
        assertNotice("Enter a valid email address.");
    }

    @Test
    void passwordMismatchIsReportedInBrowser() {
        open("register.php");
        input("password").sendKeys("Strong-test-123");
        input("confirm_password").sendKeys("different-password");
        assertEquals("The passwords do not match.", input("confirm_password").getDomProperty("validationMessage"));
    }
}
