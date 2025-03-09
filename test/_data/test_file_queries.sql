SELECT 'quack' as my_column; -- -- Simplest query in memory
SELECT * FROM './test/_data/measurements_medium.csv'; -- -- Query medium data from csv in memory
CREATE OR REPLACE TABLE measurements AS SELECT * FROM './test/_data/measurements_medium.csv';  -- test_medium.db -- Insert medium data from csv
SELECT column0 as city, max(column1) as max, mean(column1)::DECIMAL(5,2) as mean, min(column1) as min FROM measurements GROUP BY column0 ORDER BY city;  -- test_medium.db -- Query aggregates from medium-sized table
SELECT column0 as city, max(column1) as max, mean(column1) as mean, min(column1) as min FROM measurements GROUP BY column0 ORDER BY city; -- test_medium.db -- Query aggregates from medium-sized table (no decimal)
SELECT column0 as city, max(column1) as max, mean(column1)::DECIMAL(5,2) as mean, min(column1) as min FROM './test/_data/measurements.csv' GROUP BY column0 ORDER BY city; -- -- Query aggregates from long csv
SELECT column0 as city, max(column1) as max, mean(column1) as mean, min(column1) as min FROM './test/_data/measurements.csv' GROUP BY column0 ORDER BY city; -- -- Query aggregates from long csv (no decimals)
SELECT 'quack' as my_column; -- -- Simplest query in memory (again)
