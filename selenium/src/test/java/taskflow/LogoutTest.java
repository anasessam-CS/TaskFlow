package taskflow;

import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;

import static org.junit.jupiter.api.Assertions.assertTrue;

class LogoutTest extends BrowserTest {
    @Test
    void logoutEndsTheSession() {
        login();
        driver.findElement(By.cssSelector("form[action='logout.php'] button")).click();
        assertNotice("You have signed out.");
        open("dashboard.php");
        assertTrue(driver.getCurrentUrl().endsWith("/index.php"));
    }
}
