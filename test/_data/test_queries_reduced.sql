SELECT 'quack' as my_column; -- -- Simplest query in memory
CREATE OR REPLACE TABLE measurements AS SELECT * FROM './test/_data/measurements.csv'; -- test.db -- Insert long data from csv
SELECT column0 as city, max(column1) as max, mean(column1)::DECIMAL(5,2) as mean, min(column1) as min FROM measurements GROUP BY column0 ORDER BY city; -- test.db -- Query aggregates from 1000000000 rows table
SELECT column0 as city, max(column1) as max, mean(column1) as mean, min(column1) as min FROM measurements GROUP BY column0 ORDER BY city; -- test.db -- Query aggregates from 1000000000 rows table (no decimal)
