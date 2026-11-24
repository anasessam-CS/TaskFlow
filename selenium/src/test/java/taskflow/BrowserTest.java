package taskflow;

import java.time.Duration;
import org.junit.jupiter.api.AfterEach;
import org.junit.jupiter.api.BeforeEach;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.WebDriverWait;

import static org.junit.jupiter.api.Assertions.assertEquals;
import static org.junit.jupiter.api.Assumptions.assumeTrue;

abstract class BrowserTest {
    WebDriver driver;
    WebDriverWait wait;
    final String base = System.getProperty("taskflow.url", "http://127.0.0.1:8000").replaceAll("/+$", "");

    @BeforeEach
    void openBrowser() {
        var options = new ChromeOptions();
        if (Boolean.getBoolean("taskflow.headless")) {
            options.addArguments("--headless=new");
        }
        if (Boolean.getBoolean("taskflow.noSandbox")) {
            options.addArguments("--no-sandbox");
        }
        options.addArguments("--window-size=1280,900", "--disable-dev-shm-usage");
        String binary = System.getProperty("taskflow.chrome");
        if (binary != null) {
            options.setBinary(binary);
        }
        driver = new ChromeDriver(options);
        wait = new WebDriverWait(driver, Duration.ofSeconds(12));
    }

    @AfterEach
    void closeBrowser() {
        if (driver != null) {
            driver.quit();
        }
    }

    void open(String page) {
        driver.get(base + "/" + page);
    }

    WebElement input(String name) {
        return driver.findElement(By.name(name));
    }

    void submit() {
        driver.findElement(By.cssSelector("main form button.primary")).click();
    }

    void fillLogin(String email, String password) {
        open("index.php");
        input("email").sendKeys(email);
        input("password").sendKeys(password);
        submit();
    }

    void login() {
        String email = System.getenv("TASKFLOW_TEST_EMAIL");
        String password = System.getenv("TASKFLOW_TEST_PASSWORD");
        assumeTrue(email != null && password != null, "Set the confirmed test account credentials.");
        fillLogin(email, password);
        wait.until(browser -> browser.getCurrentUrl().endsWith("/dashboard.php"));
    }

    void assertNotice(String expected) {
        assertEquals(expected, driver.findElement(By.className("notice")).getText());
    }
}
