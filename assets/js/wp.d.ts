export {};

declare global {
  const wp: any;
  interface Window {
    wp: any;
    wpApiSettings: any;
    APWidgetMatrix: any;
  }
}
