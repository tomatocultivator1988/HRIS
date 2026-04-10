-- Check all evaluations for Juanqwe Dela Cruz
SELECT 
    e.id,
    e.applicant_id,
    e.stage_name,
    e.score,
    e.interviewer_name,
    e.evaluation_date,
    e.pass_fail,
    e.notes,
    e.created_at,
    e.updated_at
FROM applicant_evaluations e
WHERE e.applicant_id = 'ab42d8b1-f895-458f-bca8-a23d091b81b9'
ORDER BY e.created_at DESC;

-- Count evaluations by stage
SELECT 
    stage_name,
    COUNT(*) as count
FROM applicant_evaluations
WHERE applicant_id = 'ab42d8b1-f895-458f-bca8-a23d091b81b9'
GROUP BY stage_name;
