// ============================================================
// BE Store — Módulo de Productos
// Lector de códigos de barras: pistola USB + cámara
// ============================================================

// ── Pistola lectora USB ──────────────────────────────────────
// Las pistolas envían el código como si fuera teclado + Enter
let bufferPistola = '';
let timerPistola  = null;

document.addEventListener('keydown', (e) => {
    // Solo capturar si el foco está en el campo de código de barras
    // o si la pistola escaneó desde cualquier parte (caracteres rápidos)
    const campoActivo = document.activeElement;
    const esCampoCodigo = campoActivo?.dataset?.scanner === 'true';

    if (esCampoCodigo) return; // El campo lo maneja solo

    // Detectar entrada rápida de pistola (< 50ms entre teclas)
    clearTimeout(timerPistola);

    if (e.key === 'Enter' && bufferPistola.length > 4) {
        // Es un escaneo de pistola — enviar a Livewire
        const codigo = bufferPistola.trim();
        bufferPistola = '';

        // Buscar el componente Livewire activo y notificar
        if (window.Livewire) {
            window.Livewire.dispatch('codigoEscaneadoPistola', { codigo });
        }
        return;
    }

    if (e.key.length === 1) {
        bufferPistola += e.key;
        timerPistola = setTimeout(() => { bufferPistola = ''; }, 100);
    }
});

// ── Cámara (QuaggaJS) ────────────────────────────────────────
window.BeScanner = {
    activo: false,

    iniciar(onDetectado) {
        if (this.activo) return;

        // Cargar QuaggaJS dinámicamente si no está cargado
        if (typeof Quagga === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js';
            script.onload = () => this._arrancarQuagga(onDetectado);
            document.head.appendChild(script);
        } else {
            this._arrancarQuagga(onDetectado);
        }
    },

    _arrancarQuagga(onDetectado) {
        const video = document.getElementById('scanner-video');
        if (!video) return;

        Quagga.init({
            inputStream: {
                name: 'Live',
                type: 'LiveStream',
                target: video,
                constraints: {
                    facingMode: 'environment', // Cámara trasera
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                },
            },
            decoder: {
                readers: ['ean_reader', 'ean_8_reader', 'code_128_reader', 'upc_reader'],
            },
            locate: true,
        }, (err) => {
            if (err) {
                console.error('Scanner error:', err);
                alert('No se pudo acceder a la cámara. Verificá los permisos.');
                return;
            }
            Quagga.start();
            this.activo = true;
        });

        // Evitar detecciones duplicadas
        let ultimoDetectado = '';
        let timerDeteccion  = null;

        Quagga.onDetected((result) => {
            const codigo = result.codeResult.code;
            if (codigo === ultimoDetectado) return;

            ultimoDetectado = codigo;
            clearTimeout(timerDeteccion);
            timerDeteccion = setTimeout(() => { ultimoDetectado = ''; }, 2000);

            // Vibrar en móvil si está disponible
            if (navigator.vibrate) navigator.vibrate(100);

            onDetectado(codigo);
        });
    },

    detener() {
        if (!this.activo) return;
        if (typeof Quagga !== 'undefined') {
            Quagga.stop();
        }
        this.activo = false;
    }
};

// ── Livewire events ──────────────────────────────────────────
document.addEventListener('livewire:init', () => {

    // Escuchar cuando Livewire pide abrir el scanner
    window.Livewire.on('abrirScanner', () => {
        const modal = document.getElementById('scanner-modal');
        if (modal) {
            modal.style.display = 'flex';
            BeScanner.iniciar((codigo) => {
                window.Livewire.dispatch('codigoEscaneado', { codigo });
                BeScanner.detener();
                modal.style.display = 'none';
            });
        }
    });

    // Cerrar scanner
    window.Livewire.on('cerrarScanner', () => {
        BeScanner.detener();
        const modal = document.getElementById('scanner-modal');
        if (modal) modal.style.display = 'none';
    });

    // Pistola USB — escuchar el evento global
    window.Livewire.on('codigoEscaneadoPistola', ({ codigo }) => {
        // Envia al componente activo de productos
        const componente = document.querySelector('[wire\\:id]');
        if (componente) {
            window.Livewire.find(componente.getAttribute('wire:id'))
                ?.call('codigoEscaneado', codigo);
        }
    });
});
