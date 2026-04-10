-- Insert a test evaluation for Juanqwe Dela Cruz (Screening stage)
INSERT INTO applicant_evaluations (
    applicant_id,
    stage_name,
    score,
    interviewer_name,
    evaluation_date,
    pass_fail,
    notes
) VALUES (
    'ab42d8b1-f895-458f-bca8-a23d091b81b9',  -- Juanqwe's applicant_id from the console log
    'Screening',
    85.5,
    'Test Interviewer',
    '2025-01-15',
    true,
    'Test evaluation - checking if display works'
);

-- Verify the insertion
SELECT 
    a.first_name,
    a.last_name,
    e.stage_name,
    e.score,
    e.interviewer_name,
    e.evaluation_date,
    e.pass_fail,
    e.notes,
    e.created_at
FROM applicants a
JOIN applicant_evaluations e ON a.id = e.applicant_id
WHERE a.id = 'ab42d8b1-f895-458f-bca8-a23d091b81b9';
