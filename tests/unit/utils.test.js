import { describe, it, expect } from 'vitest';
import { fmtDate, fmtTime, esc, statusBadge } from '../../src/js/utils.js';

describe('fmtDate', () => {
  it('formats a date string', () => {
    const result = fmtDate('2025-01-15');
    expect(result).toContain('Jan');
    expect(result).toContain('15');
    expect(result).toContain('2025');
  });

  it('returns dash for null', () => {
    expect(fmtDate(null)).toBe('—');
  });

  it('returns dash for undefined', () => {
    expect(fmtDate(undefined)).toBe('—');
  });

  it('returns dash for empty string', () => {
    expect(fmtDate('')).toBe('—');
  });
});

describe('fmtTime', () => {
  it('formats morning time with AM', () => {
    expect(fmtTime('09:30')).toBe('9:30 AM');
  });

  it('formats afternoon time with PM', () => {
    expect(fmtTime('14:00')).toBe('2:00 PM');
  });

  it('formats noon as 12 PM', () => {
    expect(fmtTime('12:00')).toBe('12:00 PM');
  });

  it('formats midnight as 12 AM', () => {
    expect(fmtTime('00:00')).toBe('12:00 AM');
  });

  it('formats 1 AM correctly', () => {
    expect(fmtTime('01:15')).toBe('1:15 AM');
  });

  it('returns dash for null', () => {
    expect(fmtTime(null)).toBe('—');
  });

  it('returns dash for undefined', () => {
    expect(fmtTime(undefined)).toBe('—');
  });

  it('returns dash for empty string', () => {
    expect(fmtTime('')).toBe('—');
  });
});

describe('esc', () => {
  it('escapes HTML special characters', () => {
    expect(esc('<script>alert("xss")</script>')).toBe('&lt;script&gt;alert("xss")&lt;/script&gt;');
  });

  it('escapes ampersands', () => {
    expect(esc('Tom & Jerry')).toBe('Tom &amp; Jerry');
  });

  it('escapes angle brackets', () => {
    expect(esc('<b>bold</b>')).toBe('&lt;b&gt;bold&lt;/b&gt;');
  });

  it('returns empty string for null', () => {
    expect(esc(null)).toBe('');
  });

  it('returns empty string for undefined', () => {
    expect(esc(undefined)).toBe('');
  });

  it('returns empty string for empty string', () => {
    expect(esc('')).toBe('');
  });

  it('passes through normal text unchanged', () => {
    expect(esc('Hello World')).toBe('Hello World');
  });
});

describe('statusBadge', () => {
  it('generates badge HTML with status class', () => {
    const badge = statusBadge('confirmed');
    expect(badge).toContain('status-confirmed');
    expect(badge).toContain('Confirmed');
  });

  it('capitalizes first letter', () => {
    expect(statusBadge('new')).toContain('New');
    expect(statusBadge('cancelled')).toContain('Cancelled');
  });

  it('defaults to "new" for null/undefined', () => {
    expect(statusBadge(null)).toContain('status-new');
    expect(statusBadge(null)).toContain('New');
    expect(statusBadge(undefined)).toContain('status-new');
  });
});
