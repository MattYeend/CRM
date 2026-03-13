<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $titles = [

            // C-Suite
            ['title' => 'Chief Executive Officer', 'short_code' => 'CEO', 'group' => 'C-Suite'],
            ['title' => 'Chief Technology Officer', 'short_code' => 'CTO', 'group' => 'C-Suite'],
            ['title' => 'Chief Financial Officer', 'short_code' => 'CFO', 'group' => 'C-Suite'],
            ['title' => 'Chief Operating Officer', 'short_code' => 'COO', 'group' => 'C-Suite'],
            ['title' => 'Chief Marketing Officer', 'short_code' => 'CMO', 'group' => 'C-Suite'],
            ['title' => 'Chief Information Officer', 'short_code' => 'CIO', 'group' => 'C-Suite'],
            ['title' => 'Chief Revenue Officer', 'short_code' => 'CRO', 'group' => 'C-Suite'],
            ['title' => 'Chief Product Officer', 'short_code' => 'CPO', 'group' => 'C-Suite'],
            ['title' => 'Chief Strategy Officer', 'short_code' => 'CSO', 'group' => 'C-Suite'],
            ['title' => 'Chief Human Resources Officer', 'short_code' => 'CHRO', 'group' => 'C-Suite'],

            // Executive
            ['title' => 'President', 'short_code' => 'PRES', 'group' => 'Executive'],
            ['title' => 'Vice President', 'short_code' => 'VP', 'group' => 'Executive'],
            ['title' => 'Executive Vice President', 'short_code' => 'EVP', 'group' => 'Executive'],
            ['title' => 'Senior Vice President', 'short_code' => 'SVP', 'group' => 'Executive'],
            ['title' => 'Managing Director', 'short_code' => 'MD', 'group' => 'Executive'],
            ['title' => 'Director', 'short_code' => 'DIR', 'group' => 'Executive'],
            ['title' => 'Senior Director', 'short_code' => 'SR_DIR', 'group' => 'Executive'],

            // Management
            ['title' => 'General Manager', 'short_code' => 'GM', 'group' => 'Management'],
            ['title' => 'Operations Manager', 'short_code' => 'OPS_MGR', 'group' => 'Management'],
            ['title' => 'Project Manager', 'short_code' => 'PM', 'group' => 'Management'],
            ['title' => 'Product Manager', 'short_code' => 'PROD_MGR', 'group' => 'Management'],
            ['title' => 'Account Manager', 'short_code' => 'ACC_MGR', 'group' => 'Management'],
            ['title' => 'Sales Manager', 'short_code' => 'SALES_MGR', 'group' => 'Management'],
            ['title' => 'Marketing Manager', 'short_code' => 'MKT_MGR', 'group' => 'Management'],
            ['title' => 'Engineering Manager', 'short_code' => 'ENG_MGR', 'group' => 'Management'],
            ['title' => 'HR Manager', 'short_code' => 'HR_MGR', 'group' => 'Management'],
            ['title' => 'Finance Manager', 'short_code' => 'FIN_MGR', 'group' => 'Management'],
            ['title' => 'Manager', 'short_code' => 'MGR', 'group' => 'Management'],
            ['title' => 'Senior Manager', 'short_code' => 'SR_MGR', 'group' => 'Management'],
            ['title' => 'Mid-level Manager', 'short_code' => 'MID_MGR', 'group' => 'Management'],
            ['title' => 'Junior Manager', 'short_code' => 'JR_MGR', 'group' => 'Management'],


            // Engineering / Tech
            ['title' => 'Software Engineer', 'short_code' => 'SE', 'group' => 'Engineering'],
            ['title' => 'Senior Software Engineer', 'short_code' => 'SSE', 'group' => 'Engineering'],
            ['title' => 'Lead Software Engineer', 'short_code' => 'L_SE', 'group' => 'Engineering'],
            ['title' => 'Principal Engineer', 'short_code' => 'P_E', 'group' => 'Engineering'],
            ['title' => 'Mid-level Software Engineer', 'short_code' => 'MID_SE', 'group' => 'Engineering'],
            ['title' => 'Junior Software Engineer', 'short_code' => 'JR_SE', 'group' => 'Engineering'],

            ['title' => 'DevOps Engineer', 'short_code' => 'DEVOPS', 'group' => 'Engineering'],
            ['title' => 'Senior DevOps Engineer', 'short_code' => 'SR_DEVOPS', 'group' => 'Engineering'],
            ['title' => 'Lead DevOps Engineer', 'short_code' => 'L_DEVOPS', 'group' => 'Engineering'],
            ['title' => 'Principle DevOps Engineer', 'short_code' => 'P_DEVOPS', 'group' => 'Engineering'],
            ['title' => 'Mid-level DevOps Engineer', 'short_code' => 'MID_DEVOPS', 'group' => 'Engineering'],
            ['title' => 'Junior DevOps Engineer', 'short_code' => 'JR_DEVOPS', 'group' => 'Engineering'],

            ['title' => 'Site Reliability Engineer', 'short_code' => 'SRE', 'group' => 'Engineering'],
            ['title' => 'Senior Site Reliablilty Engineer', 'short_code' => 'SR_SRE', 'group' => 'Engineering'],
            ['title' => 'Lead Site Reliablilty Engineer', 'short_code' => 'L_SRE', 'group' => 'Engineering'],
            ['title' => 'Principle Site Reliability Engineer', 'short_code' => 'P_SRE', 'group' => 'Engineering'],
            ['title' => 'Mid-level Site Reliability Engineer', 'short_code' => 'MID_SRE', 'group' => 'Engineering'],
            ['title' => 'Junior Site Reliability Engineer', 'short_code' => 'JR_SRE', 'group' => 'Engineering'],

            ['title' => 'Data Engineer', 'short_code' => 'DATA_ENG', 'group' => 'Engineering'],
            ['title' => 'Senior Data Engineer', 'short_code' => 'SR_DATA_ENG', 'group' => 'Engineering'],
            ['title' => 'Lead Data Engineer', 'short_code' => 'L_DATA_ENG', 'group' => 'Engineering'],
            ['title' => 'Principle Data Engineer', 'short_code' => 'P_DATA_ENG', 'group' => 'Engineering'],
            ['title' => 'Mid-level Data Engineer', 'short_code' => 'MID_DATA_ENG', 'group' => 'Engineering'],
            ['title' => 'Junior Data Engineer', 'short_code' => 'JR_DATA_ENG', 'group' => 'Engineering'],
    
            ['title' => 'Machine Learning Engineer', 'short_code' => 'ML_ENG', 'group' => 'Engineering'],
            ['title' => 'Senior Machine Learning Engineer', 'short_code' => 'SR_ML_ENG', 'group' => 'Engineering'],
            ['title' => 'Lead Machine Learning Engineer', 'short_code' => 'L_ML_ENG', 'group' => 'Engineering'],
            ['title' => 'Principle Machine Learning Engineer', 'short_code' => 'P_ML_ENG', 'group' => 'Engineering'],
            ['title' => 'Mid-level Machine Learning Engineer', 'short_code' => 'MID_ML_ENG', 'group' => 'Engineering'],
            ['title' => 'Junior Machine Learning Engineer', 'short_code' => 'JR_ML_ENG', 'group' => 'Engineering'],

            ['title' => 'Solutions Architect', 'short_code' => 'SA', 'group' => 'Engineering'],
            ['title' => 'Senior Solutions Architect', 'short_code' => 'SR_SA', 'group' => 'Engineering'],
            ['title' => 'Lead Solutions Architect', 'short_code' => 'L_SA', 'group' => 'Engineering'],
            ['title' => 'Principle Solutions Architect', 'short_code' => 'P_SA', 'group' => 'Engineering'],
            ['title' => 'Mid-level Solutions Architect', 'short_code' => 'MID_SA', 'group' => 'Engineering'],
            ['title' => 'Junior Solutions Architect', 'short_code' => 'JR_SA', 'group' => 'Engineering'],

            ['title' => 'Technical Architect', 'short_code' => 'TA', 'group' => 'Engineering'],
            ['title' => 'Senior Technical Architect', 'short_code' => 'SR_TA', 'group' => 'Engineering'],
            ['title' => 'Lead Technical Architect', 'short_code' => 'L_TA', 'group' => 'Engineering'],
            ['title' => 'Principle Technical Architect', 'short_code' => 'P_TA', 'group' => 'Engineering'],
            ['title' => 'Mid-level Technical Architect', 'short_code' => 'MID_TA', 'group' => 'Engineering'],
            ['title' => 'Junior Technical Architect', 'short_code' => 'JR_TA', 'group' => 'Engineering'],

            // Sales
            ['title' => 'Sales Executive', 'short_code' => 'SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Senior Sales Executive', 'short_code' => 'SR_SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Business Development Manager', 'short_code' => 'BDM', 'group' => 'Sales'],
            ['title' => 'Business Development Executive', 'short_code' => 'BDE', 'group' => 'Sales'],
            ['title' => 'Account Executive', 'short_code' => 'AE', 'group' => 'Sales'],
            ['title' => 'Sales Director', 'short_code' => 'SALES_DIR', 'group' => 'Sales'],

            // Marketing
            ['title' => 'Marketing Executive', 'short_code' => 'MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Digital Marketing Manager', 'short_code' => 'DIG_MKT_MGR', 'group' => 'Marketing'],
            ['title' => 'SEO Specialist', 'short_code' => 'SEO', 'group' => 'Marketing'],
            ['title' => 'Content Marketing Manager', 'short_code' => 'CONTENT_MGR', 'group' => 'Marketing'],
            ['title' => 'Social Media Manager', 'short_code' => 'SOCIAL_MGR', 'group' => 'Marketing'],

            // Finance
            ['title' => 'Accountant', 'short_code' => 'ACC', 'group' => 'Finance'],
            ['title' => 'Senior Accountant', 'short_code' => 'SR_ACC', 'group' => 'Finance'],
            ['title' => 'Financial Analyst', 'short_code' => 'FIN_ANALYST', 'group' => 'Finance'],
            ['title' => 'Finance Director', 'short_code' => 'FIN_DIR', 'group' => 'Finance'],
            ['title' => 'Controller', 'short_code' => 'CTRL', 'group' => 'Finance'],

            // HR
            ['title' => 'HR Specialist', 'short_code' => 'HR_SPEC', 'group' => 'HR'],
            ['title' => 'HR Business Partner', 'short_code' => 'HRBP', 'group' => 'HR'],
            ['title' => 'Talent Acquisition Specialist', 'short_code' => 'TA_SPEC', 'group' => 'HR'],
            ['title' => 'Recruiter', 'short_code' => 'RECRUITER', 'group' => 'HR'],

            // Operations
            ['title' => 'Operations Coordinator', 'short_code' => 'OPS_COORD', 'group' => 'Operations'],
            ['title' => 'Operations Director', 'short_code' => 'OPS_DIR', 'group' => 'Operations'],
            ['title' => 'Supply Chain Manager', 'short_code' => 'SCM', 'group' => 'Operations'],
            ['title' => 'Logistics Manager', 'short_code' => 'LOG_MGR', 'group' => 'Operations'],

            // Support / Admin
            ['title' => 'Customer Success Manager', 'short_code' => 'CSM', 'group' => 'Support'],
            ['title' => 'Customer Support Specialist', 'short_code' => 'SUPPORT', 'group' => 'Support'],
            ['title' => 'Technical Support Engineer', 'short_code' => 'TECH_SUPPORT', 'group' => 'Support'],
            ['title' => 'Office Manager', 'short_code' => 'OFFICE_MGR', 'group' => 'Support'],
            ['title' => 'Administrative Assistant', 'short_code' => 'ADMIN', 'group' => 'Support'],
        ];

        foreach ($titles as $title) {
            DB::table('job_titles')->insert([
                'title' => $title['title'],
                'short_code' => $title['short_code'],
                'group' => $title['group'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
