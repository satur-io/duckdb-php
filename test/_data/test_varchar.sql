SELECT * FROM repeat('h', 1000000); -- -- 1 000 000 rows one character
SELECT * FROM repeat('123456789012', 1000000); -- -- 1 000 000 rows 12 character
SELECT * FROM repeat('1234567890123', 1000000); -- -- 1 000 000 rows 13 character
SELECT * FROM repeat('1234567890123456789012345678901234567890', 1000000); -- -- 1 000 000 rows 40 character
