package taskflow;

import java.util.UUID;
import org.junit.jupiter.api.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.support.ui.Select;

import static org.junit.jupiter.api.Assertions.assertTrue;

class TaskManagementTest extends BrowserTest {
    @Test
    void emptyTitleIsRejectedByPhp() {
        login();
        open("task.php");
        ((JavascriptExecutor) driver).executeScript("arguments[0].form.noValidate = true", input("title"));
        submit();
        assertNotice("Enter a task title of up to 120 characters.");
    }

    @Test
    void createEditCompleteAndDeleteTask() {
        login();
        String title = "Selenium task " + UUID.randomUUID();
        String editedTitle = title + " edited";
        open("task.php");
        input("title").sendKeys(title);
        input("description").sendKeys("Created by the browser test.");
        submit();
        assertNotice("Task added.");
        driver.findElement(By.cssSelector("a[aria-label='Edit " + title + "']")).click();
        input("title").clear();
        input("title").sendKeys(editedTitle);
        submit();
        assertNotice("Task updated.");
        var status = By.cssSelector("select[aria-label='Status for " + editedTitle + "']");
        new Select(driver.findElement(status)).selectByVisibleText("Completed");
        wait.until(browser -> !browser.findElements(By.cssSelector("select.status-completed[aria-label='Status for " + editedTitle + "']")).isEmpty());
        assertNotice("Status updated.");
        driver.findElement(By.cssSelector("button[aria-label='Delete " + editedTitle + "']")).click();
        driver.switchTo().alert().accept();
        wait.until(browser -> browser.findElements(status).isEmpty());
        assertNotice("Task deleted.");
        assertTrue(driver.findElements(By.cssSelector("a[aria-label='Edit " + editedTitle + "']")).isEmpty());
    }
}
