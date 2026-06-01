import parse from 'html-react-parser';

/**
 * Safely parse HTML content to React components
 * Handles null, undefined, and non-string values gracefully
 * 
 * @param {string|null|undefined} content - HTML content to parse
 * @returns {string|React.ReactNode} - Parsed React components or original content
 */
export const safeParse = (content) => {
  if (!content || typeof content !== 'string') {
    return content || '';
  }
  try {
    return parse(content);
  } catch (error) {
    console.error('Error parsing HTML content:', error, 'Content:', content);
    return content; // Return original content if parsing fails
  }
};

export default safeParse;
