# Examples

### Get value by row and column

This is just an example to show how you can go into low level
handling directly datachunks and vectors from the result.

[DuckDB C API docs](https://duckdb.org/docs/stable/clients/c/overview) can be useful to understand those concepts.

Performance is not the best here. You should select only
the values that you need instead of using a `SELECT *` query
to actually retrieve only one from the result,
but the example works to show how is data stored internally.

```php
{!../../examples/04 - get-value-by-row-column.php!lines=1 17-62}
```

### Remote parquet file

Read a remote parquet file and query some aggregates.

```php
{!../../examples/06 - remote-parquet-file.php!}
```

### Summarize remote csv

Use of `SUMMARIZE TABLE` over a remote CSV file.

```php
{!../../examples/07 - summarize.php!}
```

### On the fly schema

Create table schema and insert data from a JSON.

```php
{!../../examples/10 - on-the-fly-schema.php!}
```

### UI plugin

Start a local web UI using the [DuckDB UI plugin](https://duckdb.org/docs/stable/extensions/ui.html).
When the PHP process finish the connection is closed so the UI is also stopped.
In this example we sleep for 120 seconds to illustrate the use.

```php
{!../../examples/11 - ui.php!}
```

### Monitorize local network usage

Only works for macOS due to its reliance on macOS-specific APIs for monitoring network usage. It stores network information every 2 seconds
and also opens the local UI to query data.

Can be stopped with `Cmd+C`.

```php
{!../../examples/12 - monitorize-network-usage.php!}
```

If you wait for a while to store enough data, you can run some
of this query using the UI interface.

```sql
SELECT 
    log_time,
    ARG_MAX(app, bytes_in) AS top_app_bytes_in,
    MAX(bytes_in) AS bytes_in,
    ARG_MAX(app, bytes_out) AS top_app_bytes_out,
    MAX(bytes_out) AS bytes_out,
FROM (
    SELECT date_trunc('minute', log_created_at) AS log_time,
      column01 as app,
    max(bytes_in) - min(bytes_in) as bytes_in,
    max(bytes_out) - min(bytes_out) as bytes_out
    FROM network_usage
    GROUP BY ALL
) 
GROUP BY log_time
ORDER BY log_time
```

```sql
SELECT date_trunc('minute', log_created_at) AS log_time,
  column01 as app,
max(bytes_in) - min(bytes_in) as bytes_in,
max(bytes_out) - min(bytes_out) as bytes_out
FROM network_usage
GROUP BY ALL
```

```sql
SELECT
  time_range,
  SUM(bytes_in) as total_bytes_in,
  SUM(bytes_out) as total_bytes_out
  FROM
(SELECT 
  time_bucket(INTERVAL '5 MINUTES', log_created_at) as time_range,
  column01 as app,
  max(bytes_in) - min(bytes_in) as bytes_in,
  max(bytes_out) - min(bytes_out) as bytes_out
  FROM network_usage
GROUP BY ALL)
  GROUP BY time_range
ORDER BY time_range
```

```sql
SELECT
  max(last_update) as last_update,
  time_range,
  SUM(bytes_in) / 30 as bytes_per_second_download,
  SUM(bytes_out) / 30 as bytes_per_second_upload
  FROM
(SELECT 
  time_bucket(INTERVAL '30 SECONDS', log_created_at, INTERVAL '-30 SECONDS') as time_range,
  max(log_created_at) as last_update,
  column01 as app,
  max(bytes_in) - min(bytes_in) as bytes_in,
  max(bytes_out) - min(bytes_out) as bytes_out
  FROM network_usage
GROUP BY ALL)
  GROUP BY time_range
ORDER BY time_range DESC
LIMIT 100
```

```sql
SELECT
  SUM(bytes_in) / 10 as current_bytes_per_second_download,
  SUM(bytes_out) / 10 as current_bytes_per_second_upload
FROM
(SELECT 
  column01 as app,
  max(bytes_in) - min(bytes_in) as bytes_in,
  max(bytes_out) - min(bytes_out) as bytes_out
  FROM network_usage
  WHERE log_created_at > (
    SELECT 
      max(log_created_at) - INTERVAL '10 SECONDS' 
    FROM 
      network_usage
  )
GROUP BY app)
```
