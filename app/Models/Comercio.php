<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Comercio
 * Entidad raíz del sistema. Contiene los datos del local,
 * datos fiscales y contadores de numeración de documentos.
 */
class Comercio extends Model
{
    protected $table = 'comercios';

    protected $fillable = [
        'nombre','cuit','direccion','telefono','email',
        'condicion_iva','plan','activo',
        'punto_venta','ultimo_nro_ticket','ultimo_nro_factura',
        'ultimo_nro_recibo','ultimo_nro_presupuesto'
    ];

    protected $casts = ['activo' => 'boolean'];

    public function usuarios()     { return $this->hasMany(User::class); }
    public function configuracion(){ return $this->hasOne(Configuracion::class); }
    public function categorias()   { return $this->hasMany(Categoria::class); }
    public function productos()    { return $this->hasMany(Producto::class); }
    public function proveedores()  { return $this->hasMany(Proveedor::class); }
    public function documentos()   { return $this->hasMany(Documento::class); }

    /**
     * Genera el próximo número de documento y lo incrementa atómicamente.
     * Formato: 0001-00000001
     */
    public function siguienteNumero(string $tipo): string
    {
        $campo = match($tipo) {
            'ticket'      => 'ultimo_nro_ticket',
            'factura'     => 'ultimo_nro_factura',
            'recibo'      => 'ultimo_nro_recibo',
            'presupuesto' => 'ultimo_nro_presupuesto',
        };

        $this->increment($campo);
        $this->refresh();

        return str_pad($this->punto_venta, 4, '0', STR_PAD_LEFT)
             . '-'
             . str_pad($this->$campo, 8, '0', STR_PAD_LEFT);
    }
}
