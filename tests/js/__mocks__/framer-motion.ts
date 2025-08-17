// Minimal passthrough mock so components render in tests
export const motion: any = new Proxy({}, { get: () => (props: any) => props.children });
export default {};
