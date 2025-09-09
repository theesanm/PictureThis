import { NextRequest, NextResponse } from 'next/server';

// A simple endpoint to get system settings that doesn't require admin access
// This allows the frontend to access basic settings like credit costs
export async function GET() {
  try {
    const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3011';
    console.log('Settings API: Fetching from', `${apiUrl}/api/settings`);
    
    const response = await fetch(`${apiUrl}/api/settings`, {
      headers: {
        'Content-Type': 'application/json'
      },
      // Ensure we don't cache the response
      cache: 'no-store'
    });

    console.log('Settings API: Response status', response.status);

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      console.error('Settings API: Error response', errorData);
      return NextResponse.json(
        { error: errorData.message || 'Failed to fetch system settings' }, 
        { status: response.status }
      );
    }

    const data = await response.json();
    console.log('Settings API: Success response', data);
    return NextResponse.json(data);
  } catch (error) {
    console.error('Error in settings API:', error);
    return NextResponse.json(
      { error: 'Internal server error', details: error instanceof Error ? error.message : String(error) }, 
      { status: 500 }
    );
  }
}
