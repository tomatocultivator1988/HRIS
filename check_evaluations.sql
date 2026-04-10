-- Check if evaluations exist for applicants
SELECT 
    a.id as applicant_id,
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
LEFT JOIN applicant_evaluations e ON a.id = e.applicant_id
WHERE a.first_name = 'Juanqwe' AND a.last_name = 'Dela Cruz'
ORDER BY e.created_at DESC;

-- Also check all evaluations
SELECT 
    applicant_id,
    stage_name,
    score,
    interviewer_name,
    evaluation_date,
    pass_fail,
    created_at
FROM applicant_evaluations
ORDER BY created_at DESC
LIMIT 10;
