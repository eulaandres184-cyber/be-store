// ============================================================
// BE Store — JS Global
// ============================================================

// Confirmar acciones destructivas
window.confirmar = (mensaje = '¿Estás seguro?') => confirm(mensaje);

// Flash messages — auto-ocultar después de 4 segundos
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bs-flash').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });
});

// Livewire — scroll al tope en navegación
document.addEventListener('livewire:navigated', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
