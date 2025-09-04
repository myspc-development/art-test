import { test, expect } from "@playwright/test";
import { widgetLocator } from "./utils/selectors";

const BASE_DASHBOARD_PATH = process.env.BASE_DASHBOARD_PATH || "/dashboard";

test.use({ storageState: "e2e/.state-artist.json" });

test("artist dashboard displays widgets", async ({ page }) => {
    await page.goto(BASE_DASHBOARD_PATH);

    const widgets = page.locator(widgetLocator);
    const widgetCount = await widgets.count();
    expect(widgetCount).toBeGreaterThan(0);

    const content = await page.content();
    expect(
        content.includes("widget_artist_artwork_manager") ||
            content.includes("widget_artist_revenue_summary") ||
            content.includes("widget_my_events"),
    ).toBe(true);
});
