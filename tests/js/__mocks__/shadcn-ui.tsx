import * as React from 'react';
export const Card = ({ children, ...p }: any) => <div {...p}>{children}</div>;
export const CardHeader = ({ children, ...p }: any) => <div {...p}>{children}</div>;
export const CardContent = ({ children, ...p }: any) => <div {...p}>{children}</div>;
export const CardTitle = ({ children, ...p }: any) => <div {...p}>{children}</div>;
export const Button = ({ children, ...p }: any) => <button {...p}>{children}</button>;
export const Switch = ({ checked, onCheckedChange }: any) => (
  <input type="checkbox" checked={checked} onChange={(e) => onCheckedChange?.(e.target.checked)} />
);
export const Dialog = ({ children }: any) => <div>{children}</div>;
export const DialogContent = ({ children }: any) => <div>{children}</div>;
export const DialogHeader = ({ children }: any) => <div>{children}</div>;
export const DialogTitle = ({ children }: any) => <div>{children}</div>;
export const DialogTrigger = ({ children }: any) => <div>{children}</div>;
export const DropdownMenu = ({ children }: any) => <div>{children}</div>;
export const DropdownMenuTrigger = ({ children }: any) => <div>{children}</div>;
export const DropdownMenuContent = ({ children }: any) => <div>{children}</div>;
export const DropdownMenuItem = ({ children, ...p }: any) => <div {...p}>{children}</div>;
export const Input = (p: any) => <input {...p} />;
export default {};
