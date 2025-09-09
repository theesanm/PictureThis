'use client';

import * as React from 'react';
import { cn } from '@/lib/utils';

interface ToastProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'destructive' | 'success';
  title?: string;
  description?: string;
  action?: React.ReactNode;
}

export const Toast = React.forwardRef<HTMLDivElement, ToastProps>(
  ({ className, variant = 'default', title, description, action, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
          {
            'bg-background border': variant === 'default',
            'bg-destructive border-destructive text-destructive-foreground': variant === 'destructive',
            'bg-green-50 border-green-200 text-green-800': variant === 'success',
          },
          className
        )}
        {...props}
      >
        <div className="grid gap-1">
          {title && <h3 className="font-medium">{title}</h3>}
          {description && <p className="text-sm opacity-90">{description}</p>}
        </div>
        {action}
      </div>
    );
  }
);
Toast.displayName = 'Toast';

interface ToastProviderProps {
  children: React.ReactNode;
}

type ToastActionType = {
  title?: string;
  description?: string;
  variant?: 'default' | 'destructive' | 'success';
};

type ToastContextType = {
  toast: (props: ToastActionType) => void;
  dismiss: (id?: string) => void;
};

const ToastContext = React.createContext<ToastContextType | undefined>(undefined);

export const ToastProvider = ({ children }: ToastProviderProps) => {
  const [toasts, setToasts] = React.useState<(ToastActionType & { id: string })[]>([]);

  const dismiss = React.useCallback((id?: string) => {
    setToasts((prevToasts) => 
      id 
        ? prevToasts.filter((toast) => toast.id !== id) 
        : prevToasts.slice(1)
    );
  }, []);

  const toast = React.useCallback(
    ({ title, description, variant = 'default' }: ToastActionType) => {
      const id = Math.random().toString(36).substring(2, 9);
      const newToast = { id, title, description, variant };
      
      setToasts((prevToasts) => [...prevToasts, newToast]);
      
      // Auto dismiss after 5 seconds
      setTimeout(() => {
        dismiss(id);
      }, 5000);
      
      return id;
    },
    [dismiss]
  );

  return (
    <ToastContext.Provider value={{ toast, dismiss }}>
      {children}
      <div className="fixed top-0 right-0 z-50 flex flex-col items-end p-4 gap-2 max-w-md">
        {toasts.map((t) => (
          <Toast
            key={t.id}
            title={t.title}
            description={t.description}
            variant={t.variant}
            className="animate-slide-in"
            onClick={() => dismiss(t.id)}
          />
        ))}
      </div>
    </ToastContext.Provider>
  );
};

export const useToast = () => {
  const context = React.useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within a ToastProvider');
  }
  return context;
};
