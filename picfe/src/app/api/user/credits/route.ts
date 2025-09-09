import { NextRequest, NextResponse } from 'next/server';
import { getUserToken } from '@/lib/auth-utils';

export async function GET(request: NextRequest) {
  try {
    const token = getUserToken();
    if (!token) {
      return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/users/credits`, {
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });

    if (!response.ok) {
      const errorData = await response.json();
      // Pass through the detailed error from the backend
      return NextResponse.json(
        { 
          success: false,
          message: errorData.message || 'Failed to fetch user credits',
          requiresVerification: errorData.requiresVerification || false,
          email: errorData.email
        }, 
        { status: response.status }
      );
    }

    const data = await response.json();
    return NextResponse.json(data);
  } catch (error) {
    console.error('Error in credits API:', error);
    return NextResponse.json(
      { error: 'Internal server error' }, 
      { status: 500 }
    );
  }
}
