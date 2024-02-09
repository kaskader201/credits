-- FIND EXPIRED CREDITS WITH REMAINING AMOUNT
SELECT c.id AS id, SUM(t.amount) AS amount
FROM credit c
         INNER JOIN transaction t ON (c.id = t.credit_id AND c.user_id = t.user_id)
WHERE c.usable = true
  AND c.expired_at <= now()
  AND c.user_id = decode(replace('018d8ae4-bb31-739e-80e5-12685c52b7b6'::text, '-', ''), 'hex')
GROUP BY c.id
ORDER BY c.expired_at ASC;

-- GET BALANCE - ideal way (Execution Time: 0.226 ms, approx. 22k transaction rows per user)
SELECT SUM(t.amount) AS amount
FROM credit c
         INNER JOIN transaction t ON (c.id = t.credit_id AND c.user_id = t.user_id)
WHERE c.usable = true
  AND c.user_id = decode(replace('018d8ae4-bb31-739e-80e5-12685c52b7b6'::text, '-', ''), 'hex');

-- OR slow way (approx. 86x slower)

SELECT SUM(t.amount) AS amount
FROM transaction t
WHERE t.user_id = decode(replace('018d8ae4-bb31-739e-80e5-12685c52b7b6'::text, '-', ''), 'hex');

-- GET ALL USABLE CREDITS WIT USABLE AMOUNT SORTED - for use credit operation
SELECT c.id AS id, SUM(t.amount) AS amount
FROM credit c
         INNER JOIN transaction t ON (c.id = t.credit_id AND c.user_id = t.user_id)
WHERE c.usable = true
  AND c.user_id = decode(replace('018d8ae4-bb31-739e-80e5-12685c52b7b6'::text, '-', ''), 'hex')
GROUP BY c.id
ORDER BY c.priority ASC, c.expired_at ASC



