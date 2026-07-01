<?php

namespace Database\Seeders;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Rahim Uddin Ahmed',    'email' => 'rahim.ahmed@gmail.com',     'phone' => '01711-234567', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '1234567890123', 'occupation' => 'Business Owner',    'status' => ClientStatus::Active],
            ['name' => 'Fatema Begum',          'email' => 'fatema.begum@yahoo.com',    'phone' => '01812-345678', 'gender' => 'female', 'nationality' => 'Bangladeshi', 'nid' => '2345678901234', 'occupation' => 'Homemaker',           'status' => ClientStatus::Active],
            ['name' => 'Karim Hossain',         'email' => 'karim.h@outlook.com',       'phone' => '01913-456789', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '3456789012345', 'occupation' => 'Software Engineer',   'status' => ClientStatus::Active],
            ['name' => 'Salma Khatun',          'email' => 'salma.khatun@gmail.com',    'phone' => '01611-567890', 'gender' => 'female', 'nationality' => 'Bangladeshi', 'nid' => '4567890123456', 'occupation' => 'Doctor',              'status' => ClientStatus::Active],
            ['name' => 'Jamal Uddin',           'email' => 'jamal.uddin@hotmail.com',   'phone' => '01511-678901', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '5678901234567', 'occupation' => 'Govt. Employee',      'status' => ClientStatus::Active],
            ['name' => 'Nusrat Jahan',          'email' => 'nusrat.j@gmail.com',        'phone' => '01711-789012', 'gender' => 'female', 'nationality' => 'Bangladeshi', 'nid' => '6789012345678', 'occupation' => 'Banker',              'status' => ClientStatus::Active],
            ['name' => 'Arif Billah',           'email' => 'arif.billah@gmail.com',     'phone' => '01812-890123', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '7890123456789', 'occupation' => 'Businessman',         'status' => ClientStatus::Active],
            ['name' => 'Rubina Akter',          'email' => 'rubina.akter@yahoo.com',    'phone' => '01913-901234', 'gender' => 'female', 'nationality' => 'Bangladeshi', 'nid' => '8901234567890', 'occupation' => 'Teacher',             'status' => ClientStatus::Active],
            ['name' => 'Mosharraf Hossain',     'email' => 'mosharraf.h@gmail.com',     'phone' => '01611-012345', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '9012345678901', 'occupation' => 'Retired Officer',     'status' => ClientStatus::Inactive],
            ['name' => 'Dilnoza Yusupova',      'email' => 'dilnoza.y@gmail.com',       'phone' => '01511-123456', 'gender' => 'female', 'nationality' => 'Uzbek',       'nid' => null,             'occupation' => 'Expat Professional',  'status' => ClientStatus::Active, 'passport_no' => 'AA9876543'],
            ['name' => 'Tanvir Rahman',         'email' => 'tanvir.r@gmail.com',        'phone' => '01711-234568', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '0123456789012', 'occupation' => 'Garments Owner',      'status' => ClientStatus::Active],
            ['name' => 'Sharmin Sultana',       'email' => 'sharmin.s@hotmail.com',     'phone' => '01812-345679', 'gender' => 'female', 'nationality' => 'Bangladeshi', 'nid' => '1234509876543', 'occupation' => 'Architect',           'status' => ClientStatus::Inactive],
            ['name' => 'Badrul Islam',          'email' => 'badrul.islam@gmail.com',    'phone' => '01913-456780', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '2345609876543', 'occupation' => 'Contractor',          'status' => ClientStatus::Blacklisted, 'notes' => 'Bounced cheque on previous booking. Do not accept new bookings without full payment.'],
            ['name' => 'Shirin Akter',          'email' => 'shirin.a@gmail.com',        'phone' => '01611-567891', 'gender' => 'female', 'nationality' => 'Bangladeshi', 'nid' => '3456709876543', 'occupation' => 'Nurse',               'status' => ClientStatus::Active],
            ['name' => 'Kawsar Ahmed',          'email' => 'kawsar.a@outlook.com',      'phone' => '01511-678902', 'gender' => 'male',   'nationality' => 'Bangladeshi', 'nid' => '4567809876543', 'occupation' => 'CPA / Accountant',    'status' => ClientStatus::Active],
        ];

        foreach ($clients as $data) {
            Client::create(array_merge([
                'nationality' => 'Bangladeshi',
                'notes'       => null,
                'passport_no' => null,
                'password'    => bcrypt('password'),
            ], $data));
        }
    }
}
