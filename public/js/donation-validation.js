function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validateDonationForm({ type, amount, firstName, lastName, email }) {
    if (!type) {
        return { valid: false, error: 'Veuillez sélectionner un type de don.' };
    }
    if (!amount || parseFloat(amount) <= 0) {
        return { valid: false, error: 'Veuillez saisir un montant valide.' };
    }
    if (!firstName || !lastName || !email) {
        return { valid: false, error: 'Veuillez remplir tous les champs obligatoires (prénom, nom, email).' };
    }
    if (!isValidEmail(email)) {
        return { valid: false, error: 'Veuillez saisir une adresse email valide.' };
    }
    return { valid: true, error: null };
}

// Compatible navigateur (script classique) ET Node (pour les tests)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { isValidEmail, validateDonationForm };
}