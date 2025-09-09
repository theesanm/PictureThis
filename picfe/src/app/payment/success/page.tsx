// Server component wrapper. Keeps the page renderable during prerender and
// defers browser-only behavior to a client-only inner component.
export const dynamic = 'force-dynamic'
export const fetchCache = 'force-no-store'

import React from 'react';
import ClientSuccess from './ClientSuccess';

// Client-only UI lives in ./ClientSuccess.tsx

export default function PaymentSuccessPage() {
  return <ClientSuccess />;
}
