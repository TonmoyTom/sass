/**
 * Removes HTML tags and collapses whitespace, for showing rich-text/legacy
 * fields (bio, description, etc.) as plain text — e.g. in a table cell or
 * card preview where rendering real HTML isn't appropriate.
 */
export const stripHtml = (value) =>
    value ? value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim() : '';

/**
 * Strips HTML then truncates to `length` characters, adding an ellipsis
 * if anything was cut.
 *
 * @param {string} value
 * @param {number} lengthhttp://tenant.myapp.test:8000/lms/
 */
export const truncate = (value, length = 55) => {
    const clean = stripHtml(value);
    return clean.length > length ? clean.slice(0, length).trimEnd() + '…' : clean;
};
