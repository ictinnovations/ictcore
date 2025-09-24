<?php

namespace ICT\Core\Api;

use ICT\Core\Api;
use ICT\Core\CoreException;
use ICT\Core\Corelog;

class SystemHealthApi extends Api
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
            $healthData = \ICT\Core\ServerHealth::get_all_servers_health();
            
            return [
                "status" => "success",
                "servers" => $healthData,
                "total_servers" => count($healthData),
                "message" => "Server health data retrieved successfully"
            ];
        } catch (\Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }

    /**
     * Check and update health for ALL servers/nodes
     *
     * @url POST /users/health/check
     */
    public function check_all_servers_health()
    {
        // $this->_authorize('system_admin');
        
        try {
            $results = \ICT\Core\ServerHealth::check_all_nodes_health();
            
            return [
                "status" => "success",
                "servers_checked" => count($results),
                "results" => $results,
                "message" => "Health check completed for all servers"
            ];
        } catch (\Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }

    /**
     * Get health history for a specific server
     *
     * @url GET /users/server/$node_id/health/history
     */
    public function get_server_health_history($node_id, $query = array())
    {
        // $this->_authorize('system_read');
        
        try {
            $filter = array_merge((array)$query, ['node_id' => $node_id]);
            $history = \ICT\Core\ServerHealth::search($filter);
            
            return [
                "status" => "success",
                "node_id" => $node_id,
                "history" => $history,
                "total_records" => count($history),
                "message" => "Health history retrieved successfully"
            ];
        } catch (\Exception $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }

   
}