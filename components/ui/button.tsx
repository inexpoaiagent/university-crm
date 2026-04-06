import { clsx } from 'clsx';
import React from 'react';

type ButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: 'primary' | 'secondary' | 'ghost';
};

export function Button({ className, variant = 'primary', ...props }: ButtonProps) {
  return (
    <button
      className={clsx(
        'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2',
        variant === 'primary' && 'bg-slate-900 text-white hover:bg-slate-700 focus:ring-slate-900',
        variant === 'secondary' && 'bg-white text-slate-900 border border-slate-200 hover:bg-slate-50 focus:ring-slate-300',
        variant === 'ghost' && 'text-slate-600 hover:bg-slate-100 focus:ring-slate-300',
        className
      )}
      {...props}
    />
  );
}
