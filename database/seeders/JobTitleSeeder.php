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
            ['title' => 'Technical Director', 'short_code' => 'TECH_DIR', 'group' => 'Engineering'],

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
            ['title' => 'Sales Director', 'short_code' => 'SALES_DIR', 'group' => 'Sales'],

            ['title' => 'Sales Executive', 'short_code' => 'SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Senior Sales Executive', 'short_code' => 'SR_SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Lead Sales Executive', 'short_code' => 'L_SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Principle Sales Executive', 'short_code' => 'P_SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Mid-level Sales Executive', 'short_code' => 'MID_SALES_EXEC', 'group' => 'Sales'],
            ['title' => 'Junior Sales Executive', 'short_code' => 'JR_SALES_EXEC', 'group' => 'Sales'],

            ['title' => 'Business Development Manager', 'short_code' => 'BDM', 'group' => 'Sales'],
            ['title' => 'Senior Business Development Manager', 'short_code' => 'SR_BDM', 'group' => 'Sales'],
            ['title' => 'Lead Business Development Manager', 'short_code' => 'L_BDM', 'group' => 'Sales'],
            ['title' => 'Principle Business Development Manager', 'short_code' => 'P_BDM', 'group' => 'Sales'],
            ['title' => 'Mid-level Business Development Manager', 'short_code' => 'MID_BDM', 'group' => 'Sales'],
            ['title' => 'Junior Business Development Manager', 'short_code' => 'JR_BDM', 'group' => 'Sales'],

            ['title' => 'Business Development Executive', 'short_code' => 'BDE', 'group' => 'Sales'],
            ['title' => 'Senior Business Development Executive', 'short_code' => 'SR_BDE', 'group' => 'Sales'],
            ['title' => 'Lead Business Development Executive', 'short_code' => 'L_BDE', 'group' => 'Sales'],
            ['title' => 'Principle Business Development Executive', 'short_code' => 'P_BDE', 'group' => 'Sales'],
            ['title' => 'Mid-level Business Development Executive', 'short_code' => 'MID_BDE', 'group' => 'Sales'],
            ['title' => 'Junior Business Development Executive', 'short_code' => 'JR_BDE', 'group' => 'Sales'],

            ['title' => 'Account Executive', 'short_code' => 'AE', 'group' => 'Sales'],
            ['title' => 'Senior Account Executive', 'short_code' => 'SR_AE', 'group' => 'Sales'],
            ['title' => 'Lead Account Executive', 'short_code' => 'L_AE', 'group' => 'Sales'],
            ['title' => 'Principle Account Executive', 'short_code' => 'P_AE', 'group' => 'Sales'],
            ['title' => 'Mid-level Account Executive', 'short_code' => 'MID_AE', 'group' => 'Sales'],
            ['title' => 'Junior Account Executive', 'short_code' => 'JR_AE', 'group' => 'Sales'],

            // Marketing
            ['title' => 'Marketing Director', 'short_code' => 'MKT_DIR', 'group' => 'Marketing'],
    
            ['title' => 'Marketing Executive', 'short_code' => 'MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Senior Marketing Executive', 'short_code' => 'SR_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Lead Marketing Executive', 'short_code' => 'L_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Principle Marketing Executive', 'short_code' => 'P_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Mid-level Marketing Executive', 'short_code' => 'MID_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Junior Marketing Executive', 'short_code' => 'JR_MKT_EXEC', 'group' => 'Marketing'],

            ['title' => 'Digital Marketing Manager', 'short_code' => 'DIG_MKT_MGR', 'group' => 'Marketing'],
            ['title' => 'Senior Digital Marketing Manager', 'short_code' => 'SR_DIG_MKT_MGR', 'group' => 'Marketing'],
            ['title' => 'Senior Digital Marketing Executive', 'short_code' => 'SR_DIG_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Lead Digital Marketing Executive', 'short_code' => 'L_DIG_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Principle Digital Marketing Executive', 'short_code' => 'P_DIG_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Mid-level Digital Marketing Executive', 'short_code' => 'MID_DIG_MKT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Junior Digital Marketing Executive', 'short_code' => 'JR_DIG_MKT_EXEC', 'group' => 'Marketing'],

            ['title' => 'SEO Specialist', 'short_code' => 'SEO', 'group' => 'Marketing'],
            ['title' => 'Senior SEO Specialist', 'short_code' => 'SR_SEO', 'group' => 'Marketing'],
            ['title' => 'Lead SEO Specialist', 'short_code' => 'L_SEO', 'group' => 'Marketing'],
            ['title' => 'Principle SEO Specialist', 'short_code' => 'P_SEO', 'group' => 'Marketing'],
            ['title' => 'Mid-level SEO Specialist', 'short_code' => 'MID_SEO', 'group' => 'Marketing'],
            ['title' => 'Junior SEO Specialist', 'short_code' => 'JR_SEO', 'group' => 'Marketing'],

            ['title' => 'Content Marketing Manager', 'short_code' => 'CONTENT_MGR', 'group' => 'Marketing'],
            ['title' => 'Senior Content Marketing Manager', 'short_code' => 'SR_CONTENT_MGR', 'group' => 'Marketing'],
            ['title' => 'Senior Content Marketing Executive', 'short_code' => 'SR_CONTENT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Lead Content Marketing Executive', 'short_code' => 'L_CONTENT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Principle Content Marketing Executive', 'short_code' => 'P_CONTENT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Mid-level Content Marketing Executive', 'short_code' => 'MID_CONTENT_EXEC', 'group' => 'Marketing'],
            ['title' => 'Junior Content Marketing Executive', 'short_code' => 'JR_CONTENT_EXEC', 'group' => 'Marketing'],

            ['title' => 'Social Media Manager', 'short_code' => 'SOCIAL_MGR', 'group' => 'Marketing'],
            ['title' => 'Senior Social Media Manager', 'short_code' => 'SR_SOCIAL_MGR', 'group' => 'Marketing'],
            ['title' => 'Senior Social Media Executive', 'short_code' => 'SR_SOCIAL_EXEC', 'group' => 'Marketing'],
            ['title' => 'Lead Social Media Executive', 'short_code' => 'L_SOCIAL_EXEC', 'group' => 'Marketing'],
            ['title' => 'Principle Social Media Executive', 'short_code' => 'P_SOCIAL_EXEC', 'group' => 'Marketing'],
            ['title' => 'Mid-level Social Media Executive', 'short_code' => 'MID_SOCIAL_EXEC', 'group' => 'Marketing'],
            ['title' => 'Junior Social Media Executive', 'short_code' => 'JR_SOCIAL_EXEC', 'group' => 'Marketing'],

            // Finance
            ['title' => 'Finance Director', 'short_code' => 'FIN_DIR', 'group' => 'Finance'],
    
            ['title' => 'Accountant', 'short_code' => 'ACC', 'group' => 'Finance'],
            ['title' => 'Senior Accountant', 'short_code' => 'SR_ACC', 'group' => 'Finance'],
            ['title' => 'Lead Accountant', 'short_code' => 'L_ACC', 'group' => 'Finance'],
            ['title' => 'Principle Accountant', 'short_code' => 'P_ACC', 'group' => 'Finance'],
            ['title' => 'Mid-level Accountant', 'short_code' => 'MID_ACC', 'group' => 'Finance'],
            ['title' => 'Junior Accountant', 'short_code' => 'JR_ACC', 'group' => 'Finance'],

            ['title' => 'Financial Analyst', 'short_code' => 'FIN_ANALYST', 'group' => 'Finance'],
            ['title' => 'Senior Financial Analyst', 'short_code' => 'SR_FIN_ANALYST', 'group' => 'Finance'],
            ['title' => 'Lead Financial Analyst', 'short_code' => 'L_FIN_ANALYST', 'group' => 'Finance'],
            ['title' => 'Principle Financial Analyst', 'short_code' => 'P_FIN_ANALYST', 'group' => 'Finance'],
            ['title' => 'Mid-level Financial Analyst', 'short_code' => 'MID_FIN_ANALYST', 'group' => 'Finance'],
            ['title' => 'Junior Financial Analyst', 'short_code' => 'JR_FIN_ANALYST', 'group' => 'Finance'],

            ['title' => 'Controller', 'short_code' => 'CTRL', 'group' => 'Finance'],
            ['title' => 'Senior Controller', 'short_code' => 'SR_CTRL', 'group' => 'Finance'],
            ['title' => 'Lead Controller', 'short_code' => 'L_CTRL', 'group' => 'Finance'],
            ['title' => 'Principle Controller', 'short_code' => 'P_CTRL', 'group' => 'Finance'],
            ['title' => 'Mid-level Controller', 'short_code' => 'MID_CTRL', 'group' => 'Finance'],
            ['title' => 'Junior Controller', 'short_code' => 'JR_CTRL', 'group' => 'Finance'],


            // HR
            ['title' => 'HR Director', 'short_code' => 'HR_DIR', 'group' => 'HR'],

            ['title' => 'HR Specialist', 'short_code' => 'HR_SPEC', 'group' => 'HR'],
            ['title' => 'Senior HR Specialist', 'short_code' => 'SR_HR_SPEC', 'group' => 'HR'],
            ['title' => 'Lead HR Specialist', 'short_code' => 'L_HR_SPEC', 'group' => 'HR'],
            ['title' => 'Principle HR Specialist', 'short_code' => 'P_HR_SPEC', 'group' => 'HR'],
            ['title' => 'Mid-level HR Specialist', 'short_code' => 'MID_HR_SPEC', 'group' => 'HR'],
            ['title' => 'Junior HR Specialist', 'short_code' => 'JR_HR_SPEC', 'group' => 'HR'],
    
            ['title' => 'HR Business Partner', 'short_code' => 'HRBP', 'group' => 'HR'],
            ['title' => 'Senior HR Business Partner', 'short_code' => 'SR_HRBP', 'group' => 'HR'],
            ['title' => 'Lead HR Business Partner', 'short_code' => 'L_HRBP', 'group' => 'HR'],
            ['title' => 'Principle HR Business Partner', 'short_code' => 'P_HRBP', 'group' => 'HR'],
            ['title' => 'Mid-level HR Business Partner', 'short_code' => 'MID_HRBP', 'group' => 'HR'],
            ['title' => 'Junior HR Business Partner', 'short_code' => 'JR_HRBP', 'group' => 'HR'],

            ['title' => 'Talent Acquisition Specialist', 'short_code' => 'TA_SPEC', 'group' => 'HR'],
            ['title' => 'Senior Talent Acquisition Specialist', 'short_code' => 'SR_TA_SPEC', 'group' => 'HR'],
            ['title' => 'Lead Talent Acquisition Specialist', 'short_code' => 'L_TA_SPEC', 'group' => 'HR'],
            ['title' => 'Principle Talent Acquisition Specialist', 'short_code' => 'P_TA_SPEC', 'group' => 'HR'],
            ['title' => 'Mid-level Talent Acquisition Specialist', 'short_code' => 'MID_TA_SPEC', 'group' => 'HR'],
            ['title' => 'Junior Talent Acquisition Specialist', 'short_code' => 'JR_TA_SPEC', 'group' => 'HR'],

            ['title' => 'Recruiter', 'short_code' => 'REC', 'group' => 'HR'],
            ['title' => 'Senior Recruiter', 'short_code' => 'SR_REC', 'group' => 'HR'],
            ['title' => 'Lead Recruiter', 'short_code' => 'L_REC', 'group' => 'HR'],
            ['title' => 'Principle Recruiter', 'short_code' => 'P_REC', 'group' => 'HR'],
            ['title' => 'Mid-level Recruiter', 'short_code' => 'MID_REC', 'group' => 'HR'],
            ['title' => 'Junior Recruiter', 'short_code' => 'JR_REC', 'group' => 'HR'],

            // Operations
            ['title' => 'Operations Director', 'short_code' => 'OPS_DIR', 'group' => 'Operations'],

            ['title' => 'Operations Coordinator', 'short_code' => 'OPS_COORD', 'group' => 'Operations'],
            ['title' => 'Senior Operations Coordinator', 'short_code' => 'SR_OPS_COORD', 'group' => 'Operations'],
            ['title' => 'Lead Operations Coordinator', 'short_code' => 'L_OPS_COORD', 'group' => 'Operations'],
            ['title' => 'Principle Operations Coordinator', 'short_code' => 'P_OPS_COORD', 'group' => 'Operations'],
            ['title' => 'Mid-level Operations Coordinator', 'short_code' => 'MID_OPS_COORD', 'group' => 'Operations'],
            ['title' => 'Junior Operations Coordinator', 'short_code' => 'JR_OPS_COORD', 'group' => 'Operations'],
    
            ['title' => 'Supply Chain Manager', 'short_code' => 'SCM', 'group' => 'Operations'],
            ['title' => 'Senior Supply Chain Manager', 'short_code' => 'SR_SCM', 'group' => 'Operations'],
            ['title' => 'Senior Supply Chain Executive', 'short_code' => 'SR_SC_EXEC', 'group' => 'Operations'],
            ['title' => 'Lead Supply Chain Executive', 'short_code' => 'L_SC_EXEC', 'group' => 'Operations'],
            ['title' => 'Principle Supply Chain Executive', 'short_code' => 'P_SC_EXEC', 'group' => 'Operations'],
            ['title' => 'Mid-level Supply Chain Executive', 'short_code' => 'MID_SC_EXEC', 'group' => 'Operations'],
            ['title' => 'Junior Supply Chain Executive', 'short_code' => 'JR_SC_EXEC', 'group' => 'Operations'],

            ['title' => 'Logistics Manager', 'short_code' => 'LOG_MGR', 'group' => 'Operations'],
            ['title' => 'Senior Logistics Manager', 'short_code' => 'SR_LOG_MGR', 'group' => 'Operations'],
            ['title' => 'Senior Logistics Executive', 'short_code' => 'SR_LOG_EXEC', 'group' => 'Operations'],
            ['title' => 'Lead Logistics Executive', 'short_code' => 'L_LOG_EXEC', 'group' => 'Operations'],
            ['title' => 'Principle Logistics Executive', 'short_code' => 'P_LOG_EXEC', 'group' => 'Operations'],
            ['title' => 'Mid-level Logistics Executive', 'short_code' => 'MID_LOG_EXEC', 'group' => 'Operations'],
            ['title' => 'Junior Logistics Executive', 'short_code' => 'JR_LOG_EXEC', 'group' => 'Operations'],

            // Support / Admin
            ['title' => 'Support Director', 'short_code' => 'SUPPORT_DIR', 'group' => 'Support'],
            ['title' => 'Office Manager', 'short_code' => 'OFFICE_MGR', 'group' => 'Support'],

            ['title' => 'Customer Success Manager', 'short_code' => 'CSM', 'group' => 'Support'],
            ['title' => 'Senior Customer Success Manager', 'short_code' => 'SR_CSM', 'group' => 'Support'],
            ['title' => 'Senior Customer Success Executive', 'short_code' => 'SR_CSE', 'group' => 'Support'],
            ['title' => 'Lead Customer Success Executive', 'short_code' => 'L_CME', 'group' => 'Support'],
            ['title' => 'Principle Customer Success Executive', 'short_code' => 'P_CME', 'group' => 'Support'],
            ['title' => 'Mid-level Customer Success Executive', 'short_code' => 'MID_CME', 'group' => 'Support'],
            ['title' => 'Junior Customer Success Executive', 'short_code' => 'JR_CME', 'group' => 'Support'],
    
            ['title' => 'Customer Support Specialist', 'short_code' => 'SUPPORT', 'group' => 'Support'],
            ['title' => 'Senior Customer Support Specialis', 'short_code' => 'SR_SUPPORT', 'group' => 'Support'],
            ['title' => 'Lead Customer Support Specialis', 'short_code' => 'L_SUPPORT', 'group' => 'Support'],
            ['title' => 'Principle Customer Support Specialis', 'short_code' => 'P_SUPPORT', 'group' => 'Support'],
            ['title' => 'Mid-level Customer Support Specialis', 'short_code' => 'MID_SUPPORT', 'group' => 'Support'],
            ['title' => 'Junior Customer Support Specialis', 'short_code' => 'JR_SUPPORT', 'group' => 'Support'],
    
            ['title' => 'Technical Support Engineer', 'short_code' => 'TECH_SUPPORT', 'group' => 'Support'],
            ['title' => 'Senior Technical Support Engineer', 'short_code' => 'SR_TECH_SUPPORT', 'group' => 'Support'],
            ['title' => 'Lead Technical Support Engineer', 'short_code' => 'L_TECH_SUPPORT', 'group' => 'Support'],
            ['title' => 'Principle Technical Support Engineer', 'short_code' => 'P_TECH_SUPPORT', 'group' => 'Support'],
            ['title' => 'Mid-level Technical Support Engineer', 'short_code' => 'MID_TECH_SUPPORT', 'group' => 'Support'],
            ['title' => 'Junior Technical Support Engineer', 'short_code' => 'JR_TECH_SUPPORT', 'group' => 'Support'],

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
