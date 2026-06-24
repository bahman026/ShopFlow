const PERSIAN_DIGITS = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

const numberFormatter = new Intl.NumberFormat('fa-IR');

/**
 * Convert the Latin digits in any value to Persian digits.
 */
export function toPersianDigits(value) {
    return String(value ?? '').replace(/\d/g, (digit) => PERSIAN_DIGITS[Number(digit)]);
}

/**
 * Group a number with Persian digits and separators (e.g. 1234567 -> ۱٬۲۳۴٬۵۶۷).
 */
export function formatNumber(value) {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const number = Number(value);

    if (!Number.isFinite(number)) {
        return '';
    }

    return numberFormatter.format(number);
}

/**
 * Format a price with its currency label (defaults to Toman).
 */
export function formatPrice(value, currency = 'تومان') {
    const formatted = formatNumber(value);

    return formatted === '' ? '' : `${formatted} ${currency}`;
}

/**
 * Format a date as a Jalali (Shamsi) date with Persian digits.
 */
export function formatDate(value, options = {}) {
    if (!value) {
        return '';
    }

    const date = value instanceof Date ? value : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return new Intl.DateTimeFormat('fa-IR', {
        calendar: 'persian',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...options,
    }).format(date);
}

/**
 * Persian formatting helpers for prices, numbers, digits and Jalali dates.
 */
export function useFormat() {
    return {
        toPersianDigits,
        formatNumber,
        formatPrice,
        formatDate,
    };
}
