Got the error 
        "Query-A": "code: 215, message: Column `unix_milli` is not under aggregate function and not in GROUP BY. Have columns: ['sum(if(equals(JSONExtractString(labels, \\'direction\\'), \\'transmit\\'), value, 0))','sum(if(equals(JSONExtractString(labels, \\'direction\\'), \\'receive\\'), value, 0))','toStartOfMinute(toDateTime(divide(unix_milli, 1000)))']: While processing toDateTime(unix_milli / 1000) AS timestamp, round((sum(if(JSONExtractString(labels, 'direction') = 'receive', value, 0)) / 1024) / 1024, 2) AS network_in_mb, round((sum(if(JSONExtractString(labels, 'direction') = 'transmit', value, 0)) / 1024) / 1024, 2) AS network_out_mb"

when using the clickhouse query
SELECT
      toDateTime(unix_milli/1000) as timestamp,
      round(sum(CASE WHEN JSONExtractString(ts.labels, 'direction') = 'receive' THEN
  s.value ELSE 0 END) / 1024 / 1024, 2) AS network_in_mb,
      round(sum(CASE WHEN JSONExtractString(ts.labels, 'direction') = 'transmit' THEN
  s.value ELSE 0 END) / 1024 / 1024, 2) AS network_out_mb
  FROM signoz_metrics.samples_v4 s
  JOIN signoz_metrics.time_series_v4 ts ON s.fingerprint = ts.fingerprint
  WHERE ts.metric_name = 'k8s_pod_network_io'
      AND JSONExtractString(ts.labels, 'k8s_pod_name') LIKE '%payment%'
      AND s.unix_milli >= (toUnixTimestamp(now() - INTERVAL 30 MINUTE) * 1000)
  GROUP BY toStartOfMinute(toDateTime(unix_milli/1000))
  ORDER BY timestamp DESC
  
