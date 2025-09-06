declare const wp: any;
declare global {
  interface Window {
    wp?: any;
    wpApiSettings?: { root?: string; nonce?: string };
    APWidgetMatrix?: { endpoint?: string; nonce?: string; apNonce?: string };
  }
}
export {};
