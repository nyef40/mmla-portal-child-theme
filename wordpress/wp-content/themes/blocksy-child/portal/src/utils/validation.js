/**
 * @param {string} value
 * @returns {boolean}
 */
export const isNonEmptyString = (value) => typeof value === 'string' && value.trim().length > 0;

/**
 * @param {string} email
 * @returns {boolean}
 */
export const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email).trim());

/**
 * @param {string} password
 * @returns {boolean}
 */
export const isStrongEnoughPassword = (password) => typeof password === 'string' && password.length >= 8;

/**
 * @param {Record<string, string>} registration
 * @returns {string|null}
 */
export const validateRegistrationPayload = (registration) => {
  if (!isNonEmptyString(registration.username)) return 'Username is required';
  if (!isValidEmail(registration.email)) return 'Please enter a valid email';
  if (!isStrongEnoughPassword(registration.password)) return 'Password must be at least 8 characters';
  if (registration.password !== registration.confirmPassword) return 'Passwords do not match';
  if (!isNonEmptyString(registration.first_name) || !isNonEmptyString(registration.last_name)) return 'First and last name are required';
  return null;
};
