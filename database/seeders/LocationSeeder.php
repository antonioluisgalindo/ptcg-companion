<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Province;
use App\Models\Locality;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            'Álava', 'Albacete', 'Alicante', 'Almería', 'Asturias', 'Ávila', 'Badajoz', 'Baleares', 'Barcelona', 'Burgos',
            'Cáceres', 'Cádiz', 'Cantabria', 'Castellón', 'Ciudad Real', 'Córdoba', 'Cuenca', 'Gerona', 'Granada', 'Guadalajara',
            'Guipúzcoa', 'Huelva', 'Huesca', 'Jaén', 'La Coruña', 'La Rioja', 'Las Palmas', 'León', 'Lérida', 'Lugo',
            'Madrid', 'Málaga', 'Murcia', 'Navarra', 'Orense', 'Palencia', 'Pontevedra', 'Salamanca', 'Segovia', 'Sevilla',
            'Soria', 'Tarragona', 'Santa Cruz de Tenerife', 'Teruel', 'Toledo', 'Valencia', 'Valladolid', 'Vizcaya', 'Zamora', 'Zaragoza'
        ];

        foreach ($provinces as $pName) {
            $province = Province::create(['name' => $pName]);

            // Add some representative localities for each province (example)
            if ($pName === 'Cáceres') {
                foreach (['Cáceres', 'Plasencia', 'Navalmoral de la Mata', 'Trujillo', 'Coria'] as $loc) {
                    Locality::create(['province_id' => $province->id, 'name' => $loc]);
                }
            } elseif ($pName === 'Badajoz') {
                foreach (['Badajoz', 'Mérida', 'Don Benito', 'Almendralejo', 'Villanueva de la Serena'] as $loc) {
                    Locality::create(['province_id' => $province->id, 'name' => $loc]);
                }
            } elseif ($pName === 'Madrid') {
                foreach (['Madrid', 'Móstoles', 'Alcalá de Henares', 'Fuenlabrada', 'Leganés', 'Getafe'] as $loc) {
                    Locality::create(['province_id' => $province->id, 'name' => $loc]);
                }
            } elseif ($pName === 'Barcelona') {
                foreach (['Barcelona', 'Hospitalet de Llobregat', 'Badalona', 'Terrassa', 'Sabadell'] as $loc) {
                    Locality::create(['province_id' => $province->id, 'name' => $loc]);
                }
            } else {
                // Default locality for others
                Locality::create(['province_id' => $province->id, 'name' => $pName]);
            }
        }
    }
}
