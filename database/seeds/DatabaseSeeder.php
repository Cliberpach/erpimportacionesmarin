<?php

use App\Almacenes\Almacen;
use App\Mantenimiento\Persona\Persona;
use App\Mantenimiento\Vendedor\Vendedor;
use App\PersonaTrabajador;
use App\Pos\Caja;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ProductoSeeder::class);

        $this->call(DepartamentoSeeder::class);
        $this->call(ProvinciaSeeder::class);
        $this->call(DistritoSeeder::class);
        $this->call(TablaSeeder::class);
        $this->call(TablaDetalleSeeder::class);
        $this->call(ParametroSeeder::class);
        $this->call(EmpresaSeeder::class);
        $this->call(EmpresaSedeSeeder::class);
        $this->call(UserSeeder::class);


        $this->call(PermissionsSeeder::class);
        $this->call(ConfiguracionSeeder::class);

        //--------Seeders Confirmados -----------
        $caja           =   new Caja();
        $caja->nombre   =   "Caja Principal";
        $caja->sede_id  =   1;
        $caja->save();

        $this->call(AlmacenSeeder::class);

        $this->call(GreenterSeeder::class);
        $this->call(EmpresaEnvioSeeder::class);

    }
}
