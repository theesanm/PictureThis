"use client";

import { useState } from 'react';
import { authAPI } from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/components/ui/use-toast';

const AlertCircleIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 48, height = 48, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <circle cx="12" cy="12" r="10" />
    <line x1="12" y1="8" x2="12" y2="12" />
    <line x1="12" y1="16" x2="12.01" y2="16" />
  </svg>
);

const MailIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 16, height = 16, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <path d="M4 4h16v16H4z" />
    <polyline points="22,6 12,13 2,6" />
  </svg>
);

const CheckCircleIcon: React.FC<React.SVGProps<SVGSVGElement>> = ({ width = 20, height = 20, className }) => (
  <svg xmlns="http://www.w3.org/2000/svg" width={width} height={height} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round" className={className}>
    <path d="M9 12l2 2 4-4" />
    <circle cx="12" cy="12" r="10" />
  </svg>
);

interface RequireVerificationProps {
  email: string;
}

export default function RequireVerification({ email }: RequireVerificationProps) {
  const [loading, setLoading] = useState(false);
  const [sent, setSent] = useState(false);
  const { toast } = useToast();

  const handleResendVerification = async () => {
    setLoading(true);
    try {
      const response = await authAPI.resendVerification(email);
      if (response.data.success) {
        setSent(true);
        toast({
          title: "Verification email sent",
          description: "Please check your inbox and follow the link to verify your email.",
          variant: "default",
        });
      } else {
        toast({
          title: "Error",
          description: response.data.message || "Failed to send verification email",
          variant: "destructive",
        });
      }
    } catch (error) {
      console.error('Error sending verification email:', error);
      toast({
        title: "Error",
        description: "Failed to send verification email. Please try again.",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card className="w-full max-w-md mx-auto">
      <CardHeader className="space-y-1">
        <div className="flex items-center justify-center mb-4 text-amber-500">
          <AlertCircleIcon width={48} height={48} />
        </div>
        <CardTitle className="text-2xl text-center">Email Verification Required</CardTitle>
        <CardDescription className="text-center">
          You need to verify your email address to continue using all features
        </CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
          <p className="text-sm text-amber-800">
            Your account with email <span className="font-semibold">{email}</span> requires verification. 
            Please check your inbox for the verification link or request a new one below.
          </p>
        </div>
      </CardContent>
      <CardFooter>
        {!sent ? (
          <Button 
            onClick={handleResendVerification} 
            disabled={loading} 
            className="w-full"
          >
            {loading ? (
              <span className="flex items-center">
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                  <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending...
              </span>
            ) : (
              <span className="flex items-center">
                <MailIcon width={16} height={16} className="mr-2 h-4 w-4" />
                Resend Verification Email
              </span>
            )}
          </Button>
        ) : (
          <div className="w-full text-center">
              <div className="flex justify-center items-center text-green-600 mb-2">
              <CheckCircleIcon width={20} height={20} className="mr-2" />
              Verification email sent!
            </div>
            <Button 
              variant="outline" 
              onClick={handleResendVerification} 
              disabled={loading}
              className="mt-2"
            >
              Send again
            </Button>
          </div>
        )}
      </CardFooter>
    </Card>
  );
}
