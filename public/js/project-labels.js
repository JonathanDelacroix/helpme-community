function labelForProject(title) {
    title = (title || '').toLowerCase();
    if (title.indexOf('puits') !== -1) return 'Puits construits';
    if (title.indexOf('alimentaire') !== -1 || title.indexOf('nourriture') !== -1) return 'Repas distribués';
    if (title.indexOf('vêtement') !== -1 || title.indexOf('vetement') !== -1) return 'Vêtements distribués';
    return 'Actions menées';
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { labelForProject };
}