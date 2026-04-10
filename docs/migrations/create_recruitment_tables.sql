-- Recruitment Module Database Schema
-- Creates tables for job postings, applicants, and evaluations

-- Enable UUID extension if not already enabled
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================================================
-- Table: job_postings
-- Stores information about open positions
-- ============================================================================
CREATE TABLE IF NOT EXISTS job_postings (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    job_title VARCHAR(255) NOT NULL,
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    num_openings INTEGER NOT NULL CHECK (num_openings >= 0),
    description TEXT,
    status VARCHAR(20) DEFAULT 'Open' CHECK (status IN ('Open', 'Closed', 'On Hold')),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Indexes for job_postings
CREATE INDEX IF NOT EXISTS idx_job_postings_status ON job_postings(status);
CREATE INDEX IF NOT EXISTS idx_job_postings_department ON job_postings(department);
CREATE INDEX IF NOT EXISTS idx_job_postings_position ON job_postings(position);

-- ============================================================================
-- Table: applicants
-- Stores candidate information matching employee fields
-- ============================================================================
CREATE TABLE IF NOT EXISTS applicants (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    job_posting_id UUID REFERENCES job_postings(id) ON DELETE RESTRICT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    work_email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20),
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    employment_status VARCHAR(50) NOT NULL CHECK (employment_status IN ('Regular', 'Probationary', 'Contractual', 'Part-time')),
    status VARCHAR(20) DEFAULT 'Applied' CHECK (status IN ('Applied', 'In Progress', 'Passed', 'Failed', 'Hired')),
    employee_id UUID REFERENCES employees(id) ON DELETE SET NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Indexes for applicants
CREATE INDEX IF NOT EXISTS idx_applicants_job_posting ON applicants(job_posting_id);
CREATE INDEX IF NOT EXISTS idx_applicants_status ON applicants(status);
CREATE INDEX IF NOT EXISTS idx_applicants_email ON applicants(work_email);
CREATE INDEX IF NOT EXISTS idx_applicants_employee ON applicants(employee_id);

-- ============================================================================
-- Table: applicant_evaluations
-- Stores evaluation scores for each hiring stage
-- ============================================================================
CREATE TABLE IF NOT EXISTS applicant_evaluations (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    applicant_id UUID NOT NULL REFERENCES applicants(id) ON DELETE CASCADE,
    stage_name VARCHAR(50) NOT NULL CHECK (stage_name IN ('Screening', 'Interview 1', 'Interview 2', 'Final Interview')),
    score DECIMAL(5,2) NOT NULL CHECK (score >= 0 AND score <= 100),
    notes TEXT,
    interviewer_name VARCHAR(255) NOT NULL,
    evaluation_date DATE NOT NULL CHECK (evaluation_date <= CURRENT_DATE),
    pass_fail BOOLEAN NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(applicant_id, stage_name)
);

-- Indexes for applicant_evaluations
CREATE INDEX IF NOT EXISTS idx_evaluations_applicant ON applicant_evaluations(applicant_id);
CREATE INDEX IF NOT EXISTS idx_evaluations_stage ON applicant_evaluations(stage_name);
CREATE INDEX IF NOT EXISTS idx_evaluations_pass_fail ON applicant_evaluations(pass_fail);

-- ============================================================================
-- Trigger Function: Auto-update updated_at timestamp
-- ============================================================================
CREATE OR REPLACE FUNCTION update_recruitment_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- Triggers: Apply updated_at function to all recruitment tables
-- ============================================================================
CREATE TRIGGER trigger_job_postings_updated_at
    BEFORE UPDATE ON job_postings
    FOR EACH ROW
    EXECUTE FUNCTION update_recruitment_updated_at();

CREATE TRIGGER trigger_applicants_updated_at
    BEFORE UPDATE ON applicants
    FOR EACH ROW
    EXECUTE FUNCTION update_recruitment_updated_at();

CREATE TRIGGER trigger_evaluations_updated_at
    BEFORE UPDATE ON applicant_evaluations
    FOR EACH ROW
    EXECUTE FUNCTION update_recruitment_updated_at();

-- ============================================================================
-- Migration Complete
-- ============================================================================
-- To apply this migration:
-- 1. Open Supabase Dashboard
-- 2. Go to SQL Editor
-- 3. Copy and paste this entire script
-- 4. Click "Run" to execute
-- 
-- This will create:
-- - 3 tables: job_postings, applicants, applicant_evaluations
-- - 9 indexes for query performance
-- - 1 trigger function for auto-updating timestamps
-- - 3 triggers applied to all tables
