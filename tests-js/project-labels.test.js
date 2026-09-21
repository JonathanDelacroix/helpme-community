import { describe, test, expect } from 'vitest';
const { labelForProject } = require('../public/js/project-labels.js');

describe('labelForProject', () => {
    test('detecte les puits', () => {
        expect(labelForProject('Construction de puits')).toBe('Puits construits');
    });

    test('detecte l\'aide alimentaire', () => {
        expect(labelForProject('Distribution alimentaire')).toBe('Repas distribués');
    });

    test('detecte la nourriture (variante)', () => {
        expect(labelForProject('Aide en nourriture')).toBe('Repas distribués');
    });

    test('detecte les vetements avec accent', () => {
        expect(labelForProject('Dons de vêtements')).toBe('Vêtements distribués');
    });

    test('detecte les vetements sans accent', () => {
        expect(labelForProject('Dons de vetements')).toBe('Vêtements distribués');
    });

    test('retombe sur le libelle generique si aucun mot-cle', () => {
        expect(labelForProject('Autre projet')).toBe('Actions menées');
    });

    test('gere une valeur nulle sans planter', () => {
        expect(labelForProject(null)).toBe('Actions menées');
    });

    test('est insensible a la casse', () => {
        expect(labelForProject('PUITS')).toBe('Puits construits');
    });
});