<?php

namespace ICT\Core\Api;

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Transmission;
use ICT\Core\Spool;
use ICT\Core\DB;

class KpiApi extends Api
{
    /**
 * @url GET /users/kpi/fax-stats-v2
 * Params: range=daily|weekly|monthly|yearly|custom
 *         start=YYYY-MM-DD (optional for custom or for anchoring)
 *         end=YYYY-MM-DD   (optional for custom)
 */
public function getFaxStatsV2($query = [])
{
    try {
        $range = $query['range'] ?? 'daily';
        $startParam = $query['start'] ?? null;
        $endParam = $query['end'] ?? null;

        // compute start and end timestamps (inclusive)
        list($startTs, $endTs) = $this->computeRangeTimestamps($range, $startParam, $endParam);

        // build periods array: each period has label, start_ts, end_ts, key
        $periods = $this->buildPeriodsForRange($range, $startTs, $endTs);

        // ✅ Dynamic groupBy depending on range
       if ($range === 'monthly' || $range === 'daily' || $range === 'weekly') {
    $groupBy = "DATE(FROM_UNIXTIME(date_created))";}
     elseif ($range === 'yearly') {
            $groupBy = "DATE_FORMAT(FROM_UNIXTIME(date_created), '%Y-%m')";
        } else {
            $groupBy = "DATE(FROM_UNIXTIME(date_created))";
        }

        // ✅ SQL with flexible groupBy
        $sql = "SELECT 
                    {$groupBy} as period_key,
                    SUM(CASE WHEN direction='outbound' THEN 1 ELSE 0 END) as sent,
                    SUM(CASE WHEN direction='inbound' THEN 1 ELSE 0 END) as received,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN status IN ('failed','failed(dnc)','no_provider') THEN 1 ELSE 0 END) as failed
                FROM transmission
                WHERE is_deleted = 0
                  AND date_created BETWEEN {$startTs} AND {$endTs}
                GROUP BY period_key
                ORDER BY period_key ASC";

        $result = DB::query('transmission', $sql);

        // Map DB rows into proper keys depending on range
        $map = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $key = $row['period_key']; // Already formatted correctly by SQL

            if (!isset($map[$key])) {
                $map[$key] = ['sent'=>0, 'received'=>0, 'success'=>0, 'failed'=>0];
            }

            $map[$key]['sent'] += (int)$row['sent'];
            $map[$key]['received'] += (int)$row['received'];
            $map[$key]['success'] += (int)$row['success'];
            $map[$key]['failed'] += (int)$row['failed'];
        }

        // Prepare final arrays
        $labels = [];
        $sentArr = []; 
        $receivedArr = []; 
        $successArr = []; 
        $failedArr = [];
        $totals = ['sent'=>0,'received'=>0,'success'=>0,'failed'=>0];

        foreach ($periods as $p) {
            $labels[] = $p['label'];
            $mapKey = $p['key']; // match against SQL period_key

            $data = $map[$mapKey] ?? ['sent'=>0,'received'=>0,'success'=>0,'failed'=>0];

            $sentArr[] = $data['sent'];
            $receivedArr[] = $data['received'];
            $successArr[] = $data['success'];
            $failedArr[] = $data['failed'];

            $totals['sent'] += $data['sent'];
            $totals['received'] += $data['received'];
            $totals['success'] += $data['success'];
            $totals['failed'] += $data['failed'];
        }

        $totalAll = $totals['sent'] + $totals['received'];
        $successRate = $totalAll > 0 ? round(($totals['success'] / $totalAll) * 100, 2) : 0;
        $failureRate = $totalAll > 0 ? round(($totals['failed'] / $totalAll) * 100, 2) : 0;

        return [
            'success' => true,
            'range' => $range,
            'labels' => $labels,
            'sent' => $sentArr,
            'received' => $receivedArr,
            'success' => $successArr,
            'failed' => $failedArr,
            'totals' => array_merge($totals, [
                'success_rate' => $successRate,
                'failure_rate' => $failureRate
            ]),
            'periods_meta' => array_map(function($p) {
                return ['label'=>$p['label'],'start'=>$p['start'],'end'=>$p['end'],'key'=>$p['key']];
            }, $periods)
        ];

    } catch (\Exception $e) {
        error_log("getFaxStatsV2 error: ".$e->getMessage());
        return ['success'=>false,'error'=>$e->getMessage()];
    }
}



/**
 * @url GET /users/kpi/fax-details
 * Params:
 *    key = period_key (required)  -- this is the 'key' returned in periods_meta (format: YYYY-MM-DD or YYYY-MM)
 *    status = completed|failed|all (optional, default all)
 *    direction = outbound|inbound|all (optional)
 *    limit, offset (optional)
 *
 * Returns list of transmissions for that period and filter.
 */
public function getFaxDetails($query = [])
{
    try {
        if (empty($query['key'])) {
            return ['success'=>false,'error'=>'period key required'];
        }
        $periodKey = $query['key'];
        $status = $query['status'] ?? 'all';
        $direction = $query['direction'] ?? 'all';
        $limit = isset($query['limit']) ? (int)$query['limit'] : 200;
        $offset = isset($query['offset']) ? (int)$query['offset'] : 0;

        // periodKey format: YYYY-MM-DD (day) or YYYY-MM (month) or Week key like YYYY-MM-DD__W
        // We'll detect YYYY-MM-DD -> day range; YYYY-MM -> full month; YYYY -> year
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodKey)) {
            $start = strtotime($periodKey . ' 00:00:00');
            $end   = strtotime($periodKey . ' 23:59:59');
        } elseif (preg_match('/^\d{4}-\d{2}$/', $periodKey)) {
            $start = strtotime($periodKey . '-01 00:00:00');
            $end   = strtotime(date('Y-m-t', $start) . ' 23:59:59'); // t = last day of month
        } elseif (preg_match('/^\d{4}$/', $periodKey)) {
            $start = strtotime($periodKey . '-01-01 00:00:00');
            $end   = strtotime($periodKey . '-12-31 23:59:59');
        } else {
            // fallback: if we get a custom key that contains start__end
            if (strpos($periodKey, '__') !== false) {
                list($s, $e) = explode('__', $periodKey, 2);
                $start = (int)$s;
                $end = (int)$e;
            } else {
                return ['success'=>false,'error'=>'invalid period key format'];
            }
        }

        $wheres = [];
        $wheres[] = "is_deleted = 0";
        $wheres[] = "date_created BETWEEN {$start} AND {$end}";

        if ($status === 'completed') {
            $wheres[] = "status = 'completed'";
        } elseif ($status === 'failed') {
            $wheres[] = "status IN ('failed','failed(dnc)','no_provider')";
        } // else 'all' -> no filter

        if ($direction === 'outbound') {
            $wheres[] = "direction = 'outbound'";
        } elseif ($direction === 'inbound') {
            $wheres[] = "direction = 'inbound'";
        }

        $whereSql = implode(' AND ', $wheres);

        $sql = "SELECT transmission_id, contact_id, direction, status, response, date_created, pages, file_path
                FROM transmission
                WHERE {$whereSql}
                ORDER BY date_created DESC
                LIMIT {$offset}, {$limit}";

        $res = DB::query('transmission', $sql);

        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = [
                'id' => $r['transmission_id'],
                'contact_id' => $r['contact_id'],
                'direction' => $r['direction'],
                'status' => $r['status'],
                'response' => $r['response'],
                'date_created' => (int)$r['date_created'],
                'pages' => (int)$r['pages'],
                'file' => $r['file_path']
            ];
        }

        // also return totals for this period
        $countSql = "SELECT 
                        SUM(CASE WHEN direction='outbound' THEN 1 ELSE 0 END) as sent,
                        SUM(CASE WHEN direction='inbound' THEN 1 ELSE 0 END) as received,
                        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as success,
                        SUM(CASE WHEN status IN ('failed','failed(dnc)','no_provider') THEN 1 ELSE 0 END) as failed
                     FROM transmission
                     WHERE {$whereSql}";
        $countRes = DB::query('transmission', $countSql);
        $tot = mysqli_fetch_assoc($countRes);

        return [
            'success' => true,
            'period_key' => $periodKey,
            'range_start' => $start,
            'range_end' => $end,
            'totals' => [
                'sent' => (int)$tot['sent'],
                'received' => (int)$tot['received'],
                'success' => (int)$tot['success'],
                'failed' => (int)$tot['failed']
            ],
            'rows' => $rows
        ];

    } catch (\Exception $e) {
        error_log("getFaxDetails error: ".$e->getMessage());
        return ['success'=>false,'error'=>$e->getMessage()];
    }
}

/**
 * Helper: compute start & end timestamps based on range
 */
private function computeRangeTimestamps($range, $startParam = null, $endParam = null)
{
    $now = time();
    if ($range === 'custom' && $startParam && $endParam) {
        $start = strtotime($startParam . ' 00:00:00');
        $end = strtotime($endParam . ' 23:59:59');
        return [$start, $end];
    }

    switch ($range) {
        case 'today':
            $start = strtotime('today 00:00:00');
            $end = strtotime('today 23:59:59');
            break;
        case 'weekly':
            // if user passed a start anchor (YYYY-MM-DD), use that week (7 days from that date)
            if ($startParam) {
                $anchor = strtotime($startParam . ' 00:00:00');
                $start = strtotime(date('Y-m-d', $anchor) . ' 00:00:00');
                $end = strtotime('+6 days', $start) + 86399;
            } else {
                // last 7 days including today
                $start = strtotime('today -6 days 00:00:00');
                $end = strtotime('today 23:59:59');
            }
            break;
        case 'monthly':
            if ($startParam) {
                $start = strtotime(date('Y-m-01', strtotime($startParam)) . ' 00:00:00');
            } else {
                $start = strtotime(date('Y-m-01', $now) . ' 00:00:00');
            }
            $end = strtotime(date('Y-m-t', $start) . ' 23:59:59');
            break;
        case 'yearly':
            if ($startParam && preg_match('/^\d{4}$/', $startParam)) {
                $year = $startParam;
            } else {
                $year = date('Y', $now);
            }
            $start = strtotime($year . '-01-01 00:00:00');
            $end = strtotime($year . '-12-31 23:59:59');
            break;
        default: // daily (last N days)
            // default show last 7 days
            $start = strtotime('today -6 days 00:00:00');
            $end = strtotime('today 23:59:59');
            break;
    }

    return [$start, $end];
}

/**
 * Helper: build periods array for range.
 * Each period: ['label'=>..., 'start'=>ts, 'end'=>ts, 'map_key'=>YYYY-MM-DD, 'key'=>period_key]
 */
private function buildPeriodsForRange($range, $startTs, $endTs)
{
    $periods = [];

    if ($range === 'yearly') {
        // months Jan..Dec
        $year = date('Y', $startTs);
        for ($m = 1; $m <= 12; $m++) {
            $s = strtotime("$year-$m-01 00:00:00");
            $e = strtotime(date('Y-m-t', $s) . ' 23:59:59');
            $periods[] = [
                'label' => date('M', $s),
                'start' => $s,
                'end' => $e,
                'map_key' => date('Y-m', $s),
                'key' => date('Y-m', $s)
            ];
        }
    } elseif ($range === 'monthly') {
        // build *daily* buckets for the given month
        $cur = strtotime(date('Y-m-01 00:00:00', $startTs));
        $endDay = strtotime(date('Y-m-t 00:00:00', $startTs));
        while ($cur <= $endDay) {
            $s = $cur;
            $e = $cur + 86399;
            $label = date('d M', $cur); // e.g., 01 Sep, 02 Sep
            $periods[] = [
                'label' => $label,
                'start' => $s,
                'end' => $e,
                'map_key' => date('Y-m-d', $cur), // daily key
                'key' => date('Y-m-d', $cur)
            ];
            $cur = strtotime('+1 day', $cur);
        }
    } else {
        // daily/weekly/custom
        $cur = strtotime(date('Y-m-d 00:00:00', $startTs));
        $endDay = strtotime(date('Y-m-d 00:00:00', $endTs));
        while ($cur <= $endDay) {
            $s = $cur;
            $e = $cur + 86399;
            $label = date('Y-m-d', $cur);
            $periods[] = [
                'label' => $label,
                'start' => $s,
                'end' => $e,
                'map_key' => date('Y-m-d', $cur),
                'key' => date('Y-m-d', $cur)
            ];
            $cur = strtotime('+1 day', $cur);
        }
    }

    return $periods;
}



}