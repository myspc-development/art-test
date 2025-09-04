export const widgetLocator = '[data-widget-id], [data-ap-widget], [id^="widget_"]';

export const roleBadgeSelector = (role: string): string =>
  `[data-role-badge="${role}"], [data-role="${role}"] [data-role-badge], .role-${role}-badge`;

