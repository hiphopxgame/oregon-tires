import { describe, it, expect } from 'vitest';
import { reviews, getRandomReviews, generateReviewHTML } from '../../src/js/reviews.js';

describe('reviews data', () => {
  it('has 12 reviews', () => {
    expect(reviews).toHaveLength(12);
  });

  it('every review has required fields', () => {
    for (const review of reviews) {
      expect(review.name).toBeTruthy();
      expect(typeof review.name).toBe('string');
      expect(review.rating).toBeGreaterThanOrEqual(1);
      expect(review.rating).toBeLessThanOrEqual(5);
      expect(review.date).toBeTruthy();
      expect(review.text).toBeTruthy();
    }
  });

  it('ratings are integers between 1 and 5', () => {
    for (const review of reviews) {
      expect(Number.isInteger(review.rating)).toBe(true);
      expect(review.rating).toBeGreaterThanOrEqual(1);
      expect(review.rating).toBeLessThanOrEqual(5);
    }
  });

  it('all names are unique', () => {
    const names = reviews.map(r => r.name);
    expect(new Set(names).size).toBe(names.length);
  });
});

describe('getRandomReviews', () => {
  it('returns 3 reviews by default', () => {
    const result = getRandomReviews();
    expect(result).toHaveLength(3);
  });

  it('returns the requested count', () => {
    expect(getRandomReviews(1)).toHaveLength(1);
    expect(getRandomReviews(5)).toHaveLength(5);
  });

  it('does not mutate the original array', () => {
    const originalLength = reviews.length;
    getRandomReviews(3);
    expect(reviews).toHaveLength(originalLength);
  });

  it('returns valid review objects', () => {
    const result = getRandomReviews(3);
    for (const review of result) {
      expect(review).toHaveProperty('name');
      expect(review).toHaveProperty('rating');
      expect(review).toHaveProperty('date');
      expect(review).toHaveProperty('text');
    }
  });
});

describe('generateReviewHTML', () => {
  it('includes the reviewer name', () => {
    const html = generateReviewHTML(reviews[0]);
    expect(html).toContain(reviews[0].name);
  });

  it('includes the review text', () => {
    const html = generateReviewHTML(reviews[0]);
    expect(html).toContain(reviews[0].text);
  });

  it('includes the review date', () => {
    const html = generateReviewHTML(reviews[0]);
    expect(html).toContain(reviews[0].date);
  });

  it('includes star rating characters', () => {
    const html = generateReviewHTML(reviews[0]);
    expect(html).toContain('★');
  });

  it('shows correct stars for rating 4', () => {
    const review4 = reviews.find(r => r.rating === 4);
    const html = generateReviewHTML(review4);
    expect(html).toContain('★★★★☆');
  });

  it('shows correct stars for rating 5', () => {
    const review5 = reviews.find(r => r.rating === 5);
    const html = generateReviewHTML(review5);
    expect(html).toContain('★★★★★');
  });
});
