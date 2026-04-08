const test = require('node:test');
const assert = require('node:assert/strict');
const ProfileUtils = require('../../public/assets/js/profile-utils.js');

test('hasDisplayValue rejects empty and placeholder values', () => {
    assert.equal(ProfileUtils.hasDisplayValue(null), false);
    assert.equal(ProfileUtils.hasDisplayValue(undefined), false);
    assert.equal(ProfileUtils.hasDisplayValue(''), false);
    assert.equal(ProfileUtils.hasDisplayValue('   '), false);
    assert.equal(ProfileUtils.hasDisplayValue('N/A'), false);
    assert.equal(ProfileUtils.hasDisplayValue('undefined'), false);
    assert.equal(ProfileUtils.hasDisplayValue('Valid'), true);
});

test('normalizeEmployeeDate handles date-only values without timezone drift', () => {
    const normalized = ProfileUtils.normalizeEmployeeDate('2024-01-15');
    assert.ok(normalized);
    assert.equal(normalized.source, 'date-only');
    assert.equal(normalized.date.toISOString(), '2024-01-15T00:00:00.000Z');
});

test('formatEmployeeDate supports DateTime-like object payloads', () => {
    const formatted = ProfileUtils.formatEmployeeDate({ date: '2024-01-15 00:00:00+00:00' }, 'en-US');
    assert.equal(formatted, 'January 15, 2024');
});

test('formatEmployeeDate returns null for invalid values', () => {
    assert.equal(ProfileUtils.formatEmployeeDate('not-a-date', 'en-US'), null);
    assert.equal(ProfileUtils.formatEmployeeDate('', 'en-US'), null);
});

test('calculateServiceYears handles anniversary boundaries', () => {
    const beforeAnniversary = new Date('2026-01-14T10:00:00.000Z');
    const onAnniversary = new Date('2026-01-15T10:00:00.000Z');
    assert.equal(ProfileUtils.calculateServiceYears('2024-01-15', beforeAnniversary), 1);
    assert.equal(ProfileUtils.calculateServiceYears('2024-01-15', onAnniversary), 2);
});
