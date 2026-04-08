(function(global) {
    function hasDisplayValue(value) {
        if (value === null || value === undefined) {
            return false;
        }

        if (typeof value === 'string') {
            const trimmed = value.trim().toLowerCase();
            return trimmed !== '' && trimmed !== 'null' && trimmed !== 'undefined' && trimmed !== 'n/a' && trimmed !== 'na';
        }

        if (typeof value === 'number') {
            return Number.isFinite(value);
        }

        if (value instanceof Date) {
            return !Number.isNaN(value.getTime());
        }

        if (typeof value === 'object') {
            return Object.keys(value).length > 0;
        }

        return true;
    }

    function getFirstDisplayValue() {
        for (let index = 0; index < arguments.length; index += 1) {
            if (hasDisplayValue(arguments[index])) {
                if (typeof arguments[index] === 'string') {
                    return arguments[index].trim();
                }

                return arguments[index];
            }
        }

        return null;
    }

    function normalizeEmployeeDate(input) {
        if (!hasDisplayValue(input)) {
            return null;
        }

        if (input instanceof Date) {
            if (Number.isNaN(input.getTime())) {
                return null;
            }

            return { date: input, source: 'datetime' };
        }

        if (typeof input === 'object' && input.date) {
            return normalizeEmployeeDate(input.date);
        }

        const rawValue = String(input).trim();

        if (!hasDisplayValue(rawValue)) {
            return null;
        }

        const dateOnlyMatch = rawValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (dateOnlyMatch) {
            const year = Number(dateOnlyMatch[1]);
            const month = Number(dateOnlyMatch[2]) - 1;
            const day = Number(dateOnlyMatch[3]);
            const utcDate = new Date(Date.UTC(year, month, day));

            if (Number.isNaN(utcDate.getTime())) {
                return null;
            }

            return { date: utcDate, source: 'date-only' };
        }

        const parsed = new Date(rawValue);
        if (Number.isNaN(parsed.getTime())) {
            return null;
        }

        return { date: parsed, source: 'datetime' };
    }

    function formatEmployeeDate(input, locale) {
        const normalized = normalizeEmployeeDate(input);
        if (!normalized) {
            return null;
        }

        const options = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };

        if (normalized.source === 'date-only') {
            return new Intl.DateTimeFormat(locale || undefined, {
                ...options,
                timeZone: 'UTC'
            }).format(normalized.date);
        }

        return new Intl.DateTimeFormat(locale || undefined, options).format(normalized.date);
    }

    function calculateServiceYears(input, nowDate = new Date()) {
        const normalized = normalizeEmployeeDate(input);
        if (!normalized || Number.isNaN(nowDate.getTime())) {
            return null;
        }

        let years = nowDate.getFullYear() - normalized.date.getFullYear();
        const currentMonth = nowDate.getMonth();
        const currentDay = nowDate.getDate();
        const hireMonth = normalized.date.getMonth();
        const hireDay = normalized.date.getDate();

        if (currentMonth < hireMonth || (currentMonth === hireMonth && currentDay < hireDay)) {
            years -= 1;
        }

        return years < 0 ? 0 : years;
    }

    const profileUtils = {
        hasDisplayValue,
        getFirstDisplayValue,
        normalizeEmployeeDate,
        formatEmployeeDate,
        calculateServiceYears
    };

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = profileUtils;
    }

    global.ProfileUtils = profileUtils;
})(typeof window !== 'undefined' ? window : globalThis);
