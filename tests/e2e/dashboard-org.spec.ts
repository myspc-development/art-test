import { test, expect } from "@playwright/test";
import { widgetLocator } from "./utils/selectors";

const BASE_DASHBOARD_PATH = process.env.BASE_DASHBOARD_PATH || "/dashboard";

test.use({ storageState: "tests/e2e/.state-org.json" });

test("org dashboard displays widgets", async ({ page }) => {
    await page.goto(BASE_DASHBOARD_PATH);

    const widgets = page.locator(widgetLocator);
    const widgetCount = await widgets.count();
    expect(widgetCount).toBeGreaterThan(0);

    const content = await page.content();
    expect(
        content.includes("widget_org_event_overview") ||
            content.includes("widget_org_insights") ||
            content.includes("widget_org_team_roster") ||
            content.includes("widget_my_events"),
    ).toBe(true);
});
