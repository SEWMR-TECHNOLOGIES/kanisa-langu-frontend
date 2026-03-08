// helpers.js
function formatDateDMY(dateStr) {
    if (!dateStr) return '';

    const date = new Date(dateStr);

    // handle invalid date (if backend sends weird format)
    if (isNaN(date.getTime())) return dateStr;

    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}
