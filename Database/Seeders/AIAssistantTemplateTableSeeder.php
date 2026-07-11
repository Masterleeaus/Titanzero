<?php

namespace Modules\TitanZero\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Nwidart\Modules\Facades\Module;

class TitanZeroTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this_module = Module::find('TitanZero');
        $this_module->enable();
        $modules = Module::all();
        if(module_is_active('TitanZero'))
        {
            foreach ($modules as $key => $value) {
                $name = '\\Modules\\' . $value->getName();
                $path =   $value->getPath();
                if(file_exists($path.'/Database/Seeders/TitanZeroTemplateListTableSeeder.php'))
                {
                $this->call($name . '\\Database\\Seeders\\TitanZeroTemplateListTableSeeder');
                }
            }
        }

        // $this->call("OthersTableSeeder");
    }
}
