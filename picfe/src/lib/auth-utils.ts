/**
 * Utility functions for handling authentication in API routes
 */

/**
 * Gets the user token from local storage (for client-side API calls)
 */
export const getUserToken = (): string | null => {
  if (typeof window === 'undefined') {
    return null;
  }
  return localStorage.getItem('token');
};

/**
 * Checks if the current user is an admin
 */
export const isUserAdmin = (): boolean => {
  if (typeof window === 'undefined') {
    return false;
  }
  
  try {
    const userString = localStorage.getItem('user');
    if (!userString) return false;
    
    const user = JSON.parse(userString);
    return user && user.isAdmin === true;
  } catch (error) {
    console.error('Error checking admin status:', error);
    return false;
  }
};
