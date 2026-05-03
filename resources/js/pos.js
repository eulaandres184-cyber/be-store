// ============================================================
// BE Store — Punto de Venta (POS)
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // Foco automático en el buscador al cargar la página
    const buscador = document.querySelector('.pos-search input');
    if (buscador) buscador.focus();

    // Atajo de teclado: Escape limpia el buscador
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && buscador) {
            buscador.value = '';
            buscador.dispatchEvent(new Event('input'));
            buscador.focus();
        }

        // F2 = foco al buscador desde cualquier parte
        if (e.key === 'F2') {
            e.preventDefault();
            if (buscador) buscador.focus();
        }
    });
});

// Livewire: re-enfocar buscador después de cada actualización
document.addEventListener('livewire:update', () => {
    // Mantener foco si el usuario estaba escribiendo
});
