import { describe, test, expect } from 'vitest';
const { isValidEmail, validateDonationForm } = require('../public/js/donation-validation.js');

describe('isValidEmail', () => {
    test('accepte un email valide', () => {
        expect(isValidEmail('jean.dupont@test.com')).toBe(true);
    });

    test('refuse un email sans arobase', () => {
        expect(isValidEmail('jean.dupont-test.com')).toBe(false);
    });

    test('refuse un email sans domaine', () => {
        expect(isValidEmail('jean@dupont')).toBe(false);
    });

    test('refuse une chaine vide', () => {
        expect(isValidEmail('')).toBe(false);
    });
});

describe('validateDonationForm', () => {
    const validData = {
        type: 'puits',
        amount: '50',
        firstName: 'Jean',
        lastName: 'Dupont',
        email: 'jean@test.com',
    };

    test('accepte un formulaire complet et valide', () => {
        const result = validateDonationForm(validData);
        expect(result.valid).toBe(true);
        expect(result.error).toBeNull();
    });

    test('refuse si le type est manquant', () => {
        const result = validateDonationForm({ ...validData, type: null });
        expect(result.valid).toBe(false);
        expect(result.error).toContain('type de don');
    });

    test('refuse un montant a zero', () => {
        const result = validateDonationForm({ ...validData, amount: '0' });
        expect(result.valid).toBe(false);
        expect(result.error).toContain('montant');
    });

    test('refuse un montant negatif', () => {
        const result = validateDonationForm({ ...validData, amount: '-10' });
        expect(result.valid).toBe(false);
    });

    test('refuse si le prenom est manquant', () => {
        const result = validateDonationForm({ ...validData, firstName: '' });
        expect(result.valid).toBe(false);
        expect(result.error).toContain('obligatoires');
    });

    test('refuse un email invalide', () => {
        const result = validateDonationForm({ ...validData, email: 'pas-un-email' });
        expect(result.valid).toBe(false);
        expect(result.error).toContain('email valide');
    });
});