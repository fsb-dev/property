<?php

namespace Database\Seeders;

use App\Enums\ClientEmploymentType;
use App\Enums\ClientIndustry;
use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Seeds 20 full client profiles covering every fillable column on the
// `clients` table (personal, contact, ID, address, employment, financials,
// co-applicant) — not just the handful of fields the old seeder touched.
class ClientSeeder extends Seeder
{
    private array $fatherNamesMale   = ['Abdul Karim', 'Mokbul Hossain', 'Nurul Islam', 'Aminul Haque', 'Shafiqur Rahman', 'Habibur Rahman', 'Golam Mostofa', 'Ashraf Ali'];
    private array $motherNamesFemale = ['Rahima Begum', 'Amena Khatun', 'Rokeya Begum', 'Hosne Ara', 'Selina Akter', 'Nurjahan Begum', 'Shirin Begum', 'Rabeya Khatun'];

    private array $cities = ['Dhaka', 'Chattogram', 'Sylhet', 'Rajshahi', 'Khulna', 'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Narayanganj'];
    private array $states = ['Dhaka Division', 'Chattogram Division', 'Sylhet Division', 'Rajshahi Division', 'Khulna Division', 'Barishal Division', 'Rangpur Division', 'Mymensingh Division'];

    private array $banks = ['City Bank', 'BRAC Bank', 'Eastern Bank', 'Dutch-Bangla Bank', 'Islami Bank', 'Standard Chartered', 'Prime Bank'];

    // [occupation, employment_type, industry, company, designation, income tier 1-4]
    private array $occupationProfiles = [
        ['Software Engineer',   ClientEmploymentType::Salaried,     ClientIndustry::IT,             'Brain Station 23',      'Senior Software Engineer', 3],
        ['Doctor',              ClientEmploymentType::Salaried,     ClientIndustry::Healthcare,     'Square Hospitals',      'Consultant Physician',     4],
        ['Bank Officer',        ClientEmploymentType::Salaried,     ClientIndustry::BankingFinance, 'BRAC Bank',             'Assistant Vice President', 3],
        ['Business Owner',      ClientEmploymentType::Business,     ClientIndustry::RetailTrade,    null,                    null,                        4],
        ['Garments Exporter',   ClientEmploymentType::Business,     ClientIndustry::Garments,       null,                    null,                        4],
        ['Contractor',          ClientEmploymentType::Business,     ClientIndustry::Construction,   null,                    null,                        3],
        ['Govt. Employee',      ClientEmploymentType::Salaried,     ClientIndustry::Government,     'Ministry of Finance',   'Deputy Secretary',          2],
        ['Teacher',             ClientEmploymentType::Salaried,     ClientIndustry::Education,      'Sunbeams School',       'Senior Teacher',            2],
        ['Architect',           ClientEmploymentType::SelfEmployed, ClientIndustry::Construction,   null,                    'Principal Architect',       3],
        ['Accountant',          ClientEmploymentType::Salaried,     ClientIndustry::BankingFinance, 'A. Qasem & Co.',        'Chartered Accountant',      3],
        ['NGO Program Manager', ClientEmploymentType::Salaried,     ClientIndustry::NGO,             'BRAC',                  'Program Manager',           2],
        ['Freelance Designer',  ClientEmploymentType::Freelancer,   ClientIndustry::Media,           null,                    null,                        2],
        ['Retired Officer',     ClientEmploymentType::Retired,      ClientIndustry::Government,     null,                    null,                        2],
        ['Homemaker',           ClientEmploymentType::Unemployed,   ClientIndustry::Other,           null,                    null,                        1],
        ['Nurse',               ClientEmploymentType::Salaried,     ClientIndustry::Healthcare,      'Evercare Hospital',     'Senior Staff Nurse',        2],
        ['Logistics Manager',   ClientEmploymentType::Salaried,     ClientIndustry::Transport,       'Summit Group',          'Logistics Manager',         3],
        ['Hotel Manager',       ClientEmploymentType::Salaried,     ClientIndustry::Hospitality,     'Pan Pacific Sonargaon', 'Operations Manager',        3],
        ['Factory Owner',       ClientEmploymentType::Business,     ClientIndustry::Manufacturing,   null,                    null,                        4],
        ['Expat Professional',  ClientEmploymentType::Salaried,     ClientIndustry::IT,              'Regional Tech Ltd.',    'Regional Director',         4],
        ['University Lecturer', ClientEmploymentType::Salaried,     ClientIndustry::Education,       'North South University', 'Assistant Professor',     2],
    ];

    private array $incomeTiers = [
        1 => ['monthly' => [25_000, 40_000],   'loan' => 0],
        2 => ['monthly' => [45_000, 90_000],   'loan' => 1],
        3 => ['monthly' => [100_000, 220_000], 'loan' => 1],
        4 => ['monthly' => [250_000, 600_000], 'loan' => 1],
    ];

    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'homeverse'],
            ['name' => 'HomeVerse Real Estate', 'status' => 'active']
        );

        // Client -> Booking cascades on delete, so this also clears any
        // bookings/payment data tied to the old client set. Re-run
        // BookingSeeder afterward to regenerate a fresh, consistent set.
        Client::query()->delete();

        $profiles = [
            ['name' => 'Client',  'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Fatema Begum',       'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Karim Hossain',      'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Salma Khatun',       'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Jamal Uddin',        'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Nusrat Jahan',       'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Arif Billah',        'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Rubina Akter',       'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Mosharraf Hossain',  'gender' => 'male',   'nationality' => 'Bangladeshi', 'status' => ClientStatus::Inactive],
            ['name' => 'Tanvir Rahman',      'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Sharmin Sultana',    'gender' => 'female', 'nationality' => 'Bangladeshi', 'status' => ClientStatus::Inactive],
            ['name' => 'Badrul Islam',       'gender' => 'male',   'nationality' => 'Bangladeshi', 'status' => ClientStatus::Blacklisted, 'notes' => 'Bounced cheque on previous booking. Do not accept new bookings without full payment.'],
            ['name' => 'Shirin Akter',       'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Kawsar Ahmed',       'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Farhana Yasmin',     'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Shahriar Kabir',     'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Ayesha Siddiqua',    'gender' => 'female', 'nationality' => 'Bangladeshi'],
            ['name' => 'Delwar Hossain',     'gender' => 'male',   'nationality' => 'Bangladeshi'],
            ['name' => 'Dilnoza Yusupova',   'gender' => 'female', 'nationality' => 'Uzbek',       'foreign' => true, 'passport' => 'AA9876543'],
            ['name' => 'Michael Chen',       'gender' => 'male',   'nationality' => 'Singaporean', 'foreign' => true, 'passport' => 'K1122334'],
        ];

        foreach ($profiles as $i => $profile) {
            $this->makeClient($tenant, $profile, $this->occupationProfiles[$i % count($this->occupationProfiles)]);
        }
    }

    private function makeClient(Tenant $tenant, array $profile, array $occupation): void
    {
        [$occTitle, $employmentType, $industry, $company, $designation, $tier] = $occupation;
        $income = $this->incomeTiers[$tier];

        $isMale     = $profile['gender'] === 'male';
        $isForeign  = $profile['foreign'] ?? false;
        $marital    = fake()->boolean(60) ? 'married' : 'single';
        $city       = collect($this->cities)->random();
        $monthly    = fake()->numberBetween(...$income['monthly']);
        $annual     = $monthly * 12;
        $slug       = Str::slug($profile['name']);

        $data = [
            'tenant_id'      => $tenant->id,
            'name'           => $profile['name'],
            'father_name'    => $isForeign ? fake()->lastName() . ' Sr.' : collect($this->fatherNamesMale)->random(),
            'mother_name'    => $isForeign ? fake()->firstNameFemale() . ' ' . fake()->lastName() : collect($this->motherNamesFemale)->random(),
            'gender'         => $profile['gender'],
            'marital_status' => $marital,
            'date_of_birth'  => fake()->dateTimeBetween('-58 years', '-26 years')->format('Y-m-d'),
            'nationality'    => $profile['nationality'],

            'email'                   => $slug . '@' . collect(['gmail.com'])->random(),
            'password'                => bcrypt('password'),
            'phone'                   => '01' . fake()->numberBetween(3, 9) . fake()->numerify('##-######'),
            'alternate_phone'         => fake()->boolean(40) ? '01' . fake()->numberBetween(3, 9) . fake()->numerify('##-######') : null,
            'whatsapp'                => fake()->boolean(70) ? '+8801' . fake()->numberBetween(3, 9) . fake()->numerify('#######') : null,
            'emergency_contact_name'  => $isMale ? collect($this->motherNamesFemale)->random() : collect($this->fatherNamesMale)->random(),
            'emergency_contact_phone' => '01' . fake()->numberBetween(3, 9) . fake()->numerify('##-######'),

            'nid'                      => $isForeign ? null : fake()->numerify('#############'),
            'birth_certificate_number' => $isForeign ? null : fake()->numerify('##################'),
            'passport_no'              => $isForeign ? $profile['passport'] : (fake()->boolean(30) ? strtoupper(fake()->bothify('??#######')) : null),
            'passport_expiry'          => $isForeign || fake()->boolean(30) ? fake()->dateTimeBetween('+1 year', '+9 years')->format('Y-m-d') : null,

            'address'            => fake()->numberBetween(1, 200) . ', Road ' . fake()->numberBetween(1, 27) . ', ' . collect(['Gulshan', 'Banani', 'Dhanmondi', 'Uttara', 'Mirpur', 'Bashundhara R/A'])->random() . ', ' . $city,
            'country'            => $isForeign ? 'other' : 'BD',
            'city'               => $city,
            'state'              => collect($this->states)->random(),
            'postal_code'        => (string) fake()->numberBetween(1000, 9280),
            'permanent_address'  => fake()->boolean(70) ? null : fake()->numberBetween(1, 200) . ', Village Road, ' . $city,

            'occupation'      => $occTitle,
            'employment_type' => $employmentType->value,
            'company'         => $company,
            'designation'     => $designation,
            'industry'        => $industry->value,
            'office_address'  => $company ? $city . ' Corporate Office' : null,
            'tenure'          => in_array($employmentType, [ClientEmploymentType::Retired, ClientEmploymentType::Unemployed]) ? null : fake()->numberBetween(1, 20) . ' years',

            'monthly_income'  => (string) $monthly,
            'annual_income'   => (string) $annual,
            'other_income'    => fake()->boolean(25) ? (string) fake()->numberBetween(5_000, 30_000) : null,
            'existing_loans'  => $income['loan'] && fake()->boolean(35) ? (string) fake()->numberBetween(200_000, 3_000_000) : null,
            'bank_name'       => collect($this->banks)->random(),
            'account_number'  => fake()->numerify('##########'),

            'notes'  => $profile['notes'] ?? null,
            'status' => $profile['status'] ?? ClientStatus::Active,
        ];

        // Roughly half of married clients get a co-applicant (spouse) on file.
        if ($marital === 'married' && fake()->boolean(50)) {
            $coName  = $isMale ? fake()->firstNameFemale() . ' ' . fake()->lastName() : fake()->firstNameMale() . ' ' . fake()->lastName();
            $coMonthly = fake()->numberBetween(20_000, (int) ($monthly * 0.8));

            $data = array_merge($data, [
                'coapplicant_name'                 => $coName,
                'coapplicant_relationship'          => 'Spouse',
                'coapplicant_dob'                   => fake()->dateTimeBetween('-55 years', '-24 years')->format('Y-m-d'),
                'coapplicant_phone'                 => '01' . fake()->numberBetween(3, 9) . fake()->numerify('##-######'),
                'coapplicant_email'                 => Str::slug($coName) . '@gmail.com',
                'coapplicant_nid'                   => fake()->numerify('#############'),
                'coapplicant_occupation'            => collect(['Homemaker', 'Teacher', 'Bank Officer', 'Business Owner'])->random(),
                'coapplicant_monthly_income'        => (string) $coMonthly,
                'coapplicant_annual_income'         => (string) ($coMonthly * 12),
                'coapplicant_tin'                   => fake()->numerify('#########'),
                'coapplicant_ownership_percentage'  => (string) collect([0, 25, 50])->random(),
                'coapplicant_address'               => $data['address'],
            ]);
        }

        Client::create($data);
    }
}
