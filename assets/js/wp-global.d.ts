export {};

declare global {
  interface WPNoticeDispatcher {
    createNotice: (
      status: string,
      message: string,
      options?: { isDismissible?: boolean }
    ) => void;
  }

  interface WPData {
    dispatch: (store: string) => WPNoticeDispatcher;
  }

  interface WP {
    i18n: {
      __: (text: string, domain?: string) => string;
    };
    data?: WPData;
  }

  var wp: WP;

  interface Window {
    wp?: WP;
  }
}
