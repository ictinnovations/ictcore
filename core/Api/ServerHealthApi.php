<?php

namespace ICT\Core\Api;

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Corelog;
use ICT\Core\ServerHealth;
use ICT\Core\Transmission;
use ICT\Core\Spool;
use ICT\Core\DB;

class ServerHealthApi extends Api
{

    /**
     * Get health status for ALL servers/nodes
     *
     * @noAuth
     * @url GET /users/cpuhealth
     */
    public function get_all_servers_health()
    {
       try {
            $healthData = ServerHealth::get_all_servers_health();

            if (!is_array($healthData)) {
                throw new CoreException(500, 'Invalid health data returned');
            }

            return [
                "status" => "success",
                "servers" => $healthData,
                "total_servers" => count($healthData),
                "message" => "Server health data retrieved successfully"
            ];
        } catch (\Throwable $e) {
            throw new CoreException(
                500,
                'Failed to retrieve server health: ' . $e->getMessage()
            );
        }
    }


    /**
     * Check and update health for ALL servers/nodes
     *
     * @url POST /users/health/check
     */
    public function check_all_servers_health()
    {
    
        
        try {
            $results = ServerHealth::check_all_nodes_health();
            
            return [
                "status" => "success",
                "servers_checked" => count($results),
                "results" => $results,
                "message" => "Health check completed for all servers"
            ];
        } catch (\Throwable $e) {
        throw new CoreException(
            500,
            'Failed to Fetch Servers ',
            $e
        );
    }
    }

    /**
     * Get health history for a specific server
     *
     * @url GET /users/server/$node_id/health/history
     */
    public function get_server_health_history($node_id, $query = array())
    {
       
        
        try {
            $filter = array_merge((array)$query, ['node_id' => $node_id]);
            $history = ServerHealth::search($filter);
            
            return [
                "status" => "success",
                "node_id" => $node_id,
                "history" => $history,
                "total_records" => count($history),
                "message" => "Health history retrieved successfully"
            ];
        } catch (\Throwable $e) {
        throw new CoreException(
            500,
            'Failed to load Serverhealth history',
            $e
        );
    }
    }



    /**
 * @url GET /users/kpi/fax-stats-v2
 * Params: range=daily|weekly|monthly|yearly|custom
 *         start=YYYY-MM-DD (optional for custom or for anchoring)
 *         end=YYYY-MM-DD   (optional for custom)
 */
public function getFaxStatsV2($query = array())
{
    try {
        return ServerHealth::faxStatsV2($query);
    } catch (\Throwable $e) {
        throw new CoreException(
            500,
            'Failed to load fax KPI stats',
            $e
        );
    }
}



}
   
