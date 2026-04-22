<?php
// database/seeders/AllowedSoftwareSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AllowedSoftware;

class AllowedSoftwareSeeder extends Seeder
{
    public function run()
    {
        // На основе вашего файла создаём разрешённое ПО
        $allowedSoftware = [
            // 7-Zip
            ['name' => '7-Zip', 'version' => '23.01 (x64)', 'vendor' => 'Igor Pavlov', 'notes' => 'Актуальная версия'],
            ['name' => '7-Zip', 'version' => '19.00 (x64)', 'vendor' => 'Igor Pavlov', 'notes' => 'Стабильная версия'],
            ['name' => '7-Zip', 'version' => '9.20 (x64 edition)', 'vendor' => 'Igor Pavlov', 'notes' => 'Старая версия'],

            // Adobe Acrobat Reader
            ['name' => 'Adobe Acrobat Reader', 'version' => '23.006.20380', 'vendor' => 'Adobe Systems Incorporated', 'notes' => 'Актуальная версия'],
            ['name' => 'Adobe Acrobat Reader DC', 'version' => '21.011.20039', 'vendor' => 'Adobe Systems Incorporated', 'notes' => ''],
            ['name' => 'Adobe Acrobat Reader DC', 'version' => '18.009.20044', 'vendor' => 'Adobe Systems Incorporated', 'notes' => ''],

            // Far Manager
            ['name' => 'Far Manager', 'version' => '3.0.3367', 'vendor' => 'Eugene Roshal & Far Group', 'notes' => 'Актуальная версия'],
            ['name' => 'Far Manager', 'version' => '3.0.6060', 'vendor' => 'Eugene Roshal & Far Group', 'notes' => ''],
            ['name' => 'Far Manager', 'version' => '2.0.1807', 'vendor' => 'Eugene Roshal & Far Group', 'notes' => 'Старая версия'],

            // CMake
            ['name' => 'CMake', 'version' => '3.31.5', 'vendor' => 'Kitware', 'notes' => 'Актуальная версия'],
            ['name' => 'CMake', 'version' => '3.31.0', 'vendor' => 'Kitware', 'notes' => ''],
            ['name' => 'CMake', 'version' => '3.28.0', 'vendor' => 'Kitware', 'notes' => ''],
            ['name' => 'CMake', 'version' => '3.27.7', 'vendor' => 'Kitware', 'notes' => ''],
            ['name' => 'CMake', 'version' => '3.20.1', 'vendor' => 'Kitware', 'notes' => ''],
            ['name' => 'CMake', 'version' => '3.5.2', 'vendor' => 'Kitware', 'notes' => 'Старая версия'],

            // DAEMON Tools Lite
            ['name' => 'DAEMON Tools Lite', 'version' => '4.47.1.0333', 'vendor' => 'Disc Soft Ltd', 'notes' => 'Актуальная версия'],
            ['name' => 'DAEMON Tools Lite', 'version' => '4.45.4.0316', 'vendor' => 'DT Soft Ltd', 'notes' => ''],
            ['name' => 'DAEMON Tools Lite', 'version' => '4.45.4.0315', 'vendor' => 'DT Soft Ltd', 'notes' => ''],

            // Crystal Reports
            ['name' => 'Crystal Reports Basic Runtime', 'version' => '10.5.0.0', 'vendor' => 'Business Objects', 'notes' => ''],

            // Adobe Flash Player (разные версии)
            ['name' => 'Adobe Flash Player', 'version' => '32.0.0.465', 'vendor' => 'Adobe Systems Incorporated', 'notes' => 'Последняя версия'],

            // AMD Software
            ['name' => 'AMD Software', 'version' => '24.8.1', 'vendor' => 'Advanced Micro Devices, Inc.', 'notes' => 'Актуальная'],
            ['name' => 'AMD Chipset Software', 'version' => '5.11.02.217', 'vendor' => 'Advanced Micro Devices, Inc.', 'notes' => ''],
            ['name' => 'AMD Chipset Software', 'version' => '2.07.21.306', 'vendor' => 'Advanced Micro Devices, Inc.', 'notes' => ''],

            // ASUS
            ['name' => 'ASUS Product Register Program', 'version' => '1.0.030', 'vendor' => 'ASUSTek Computer Inc.', 'notes' => ''],

            // Canon Drivers
            ['name' => 'Canon LBP2900', 'version' => '', 'vendor' => 'Canon Inc.', 'notes' => 'Принтер'],
            ['name' => 'Canon LBP3000', 'version' => '', 'vendor' => 'Canon Inc.', 'notes' => 'Принтер'],
            ['name' => 'Canon TM-300 Printer Driver', 'version' => '', 'vendor' => 'Canon Inc.', 'notes' => 'Принтер'],

            // Altium Designer
            ['name' => 'Altium Designer', 'version' => '16.0.8.354', 'vendor' => 'Altium Limited', 'notes' => 'Лицензионное ПО'],

            // 3D Printer
            ['name' => '3DPrinter', 'version' => '5.3.21.1', 'vendor' => 'РФЯЦ ВНИИЭФ', 'notes' => 'Специализированное ПО'],
            ['name' => '3DPrinter', 'version' => '5.3.21.3', 'vendor' => 'РФЯЦ ВНИИЭФ', 'notes' => 'Специализированное ПО'],
        ];

        foreach ($allowedSoftware as $software) {
            AllowedSoftware::create([
                'name' => $software['name'],
                'version' => $software['version'],
                'vendor' => $software['vendor'],
                'normalized_name' => AllowedSoftware::normalizeName($software['name']),
                'version_normalized' => AllowedSoftware::normalizeVersion($software['version']),
                'version_parts' => AllowedSoftware::parseVersion($software['version']),
                'notes' => $software['notes'] ?? null
            ]);
        }
    }
}
