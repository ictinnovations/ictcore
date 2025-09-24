<?php

namespace ICT\Core;

/* * ***************************************************************
 * Copyright © 2016 ICT Innovations Pakistan All Rights Reserved   *
 * Developed By: Nasir Iqbal                                       *
 * Website : http://www.ictinnovations.com/                        *
 * Mail : nasir@ictinnovations.com                                 *
 * *************************************************************** */

class ServerHealth extends Core
{
    private static $table = 'server_health';
    private static $primary_key = 'id';
    private static $fields = array(
        'id',
        'node_id',
        'server',
        'cpu',
        'ram',
        'disk',
        'status',
        'checked_at'
    );
    private static $read_only = array(
        'id',
        'checked_at'
    );

    public $id = null;
    public $node_id = null;
    public $server = '';
    public $cpu = '';
    public $ram = '';
    public $disk = '';
    public $status = '';
    public $checked_at = null;

    public function __construct($id = null)
    {
        if (!empty($id) && $id > 0) {
            $this->id = $id;
            $this->load();
        }
    }

    public static function search($aFilter = array())
    {
        $aHealth = array();
        $from_str = self::$table;
        $aWhere = array();
        
        foreach ($aFilter as $search_field => $search_value) {
            switch ($search_field) {
                case 'id':
                case 'node_id':
                    $aWhere[] = "$search_field = $search_value";
                    break;
                case 'server':
                case 'status':
                    $aWhere[] = "$search_field LIKE '%$search_value%'";
                    break;
                case 'before':
                    $aWhere[] = "checked_at <= '$search_value'";
                    break;
                case 'after':
                    $aWhere[] = "checked_at >= '$search_value'";
                    break;
            }
        }
        
        if (!empty($aWhere)) {
            $from_str .= ' WHERE ' . implode(' AND ', $aWhere);
        }
        
        $query = "SELECT * FROM " . $from_str . " ORDER BY checked_at DESC";
        Corelog::log("Server health search with $query", Corelog::DEBUG, array('aFilter' => $aFilter));
        
        $result = DB::query(self::$table, $query);
        while ($data = mysqli_fetch_assoc($result)) {
            $aHealth[] = $data;
        }
        
        return $aHealth;
    }

    private function load()
    {
        $query = "SELECT * FROM " . self::$table . " WHERE id='%id%'";
        $result = DB::query(self::$table, $query, array('id' => $this->id));
        $data = mysqli_fetch_assoc($result);
        
        if ($data) {
            $this->id = $data['id'];
            $this->node_id = $data['node_id'];
            $this->server = $data['server'];
            $this->cpu = $data['cpu'];
            $this->ram = $data['ram'];
            $this->disk = $data['disk'];
            $this->status = $data['status'];
            $this->checked_at = $data['checked_at'];
            
            Corelog::log("Server health loaded: $this->id", Corelog::CRUD);
        } else {
            throw new CoreException('404', 'Server health record not found');
        }
    }

    public function delete()
    {
        Corelog::log("Server health delete: $this->id", Corelog::CRUD);
        return DB::delete(self::$table, 'id', $this->id);
    }

 public function save()
{
    $data = array(
        'id' => $this->id,
        'node_id' => $this->node_id,
        'server' => $this->server,
        'cpu' => $this->cpu,
        'ram' => $this->ram,
        'disk' => $this->disk,
        'status' => $this->status,
        'checked_at' => gmdate('Y-m-d H:i:s')
    );

    // Check if record already exists for this node_id
    $query = "SELECT id FROM " . self::$table . " WHERE node_id = '{$this->node_id}' LIMIT 1";
    $result = DB::query(self::$table, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        // Update existing row
        $this->id = $row['id'];
        $data['id'] = $this->id;
        $result = DB::update(self::$table, $data, 'id');
        Corelog::log("Server health updated: $this->id (node_id {$this->node_id})", Corelog::CRUD);
    } else {
        // Insert new row
        $result = DB::update(self::$table, $data, false);
        $this->id = $data['id'];
        Corelog::log("New server health record created: $this->id", Corelog::CRUD);
    }

    return $result;
}


    /**
     * Get all active nodes from the node table
     */
    public static function get_active_nodes()
    {
        $query = "SELECT node_id, name, api_host, api_port, api_user, api_pass, 
                         ssh_host, ssh_port, ssh_user, ssh_pass 
                  FROM node 
                  WHERE active = 1";
        
        $result = DB::query('node', $query);
        $nodes = array();
        
        while ($data = mysqli_fetch_assoc($result)) {
            $nodes[] = $data;
        }
        
        return $nodes;
    }

    /**
     * Check health of all nodes/servers
     */
    public static function check_all_nodes_health()
    {
        $nodes = self::get_active_nodes();
        $results = array();

        foreach ($nodes as $node) {
            if ($node['ssh_host'] === 'localhost' || $node['ssh_host'] === '127.0.0.1') {
                // Local server - use direct system calls
                $healthData = self::check_local_health($node);
            } else {
                // Remote server - use SSH
                $healthData = self::check_remote_health($node);
            }

            // Save to database
            $oHealth = new ServerHealth();
            $oHealth->node_id = $node['node_id'];
            $oHealth->server = $node['name'];
            $oHealth->cpu = $healthData['cpu'];
            $oHealth->ram = $healthData['ram'];
            $oHealth->disk = $healthData['disk'];
            $oHealth->status = $healthData['status'];
            
if (isset($data['id']) && !empty($data['id'])) {
    $result = DB::update(self::$table, $data, 'id');
} else {
    $result = DB::update(self::$table, $data, false); // inserts new row
}

            $oHealth->save();

            $results[] = $healthData;
        }

        return $results;
    }

    /**
     * Check health of local server
     */
    private static function check_local_health($node)
    {
        $healthData = array(
            'node_id' => $node['node_id'],
            'server' => $node['name'],
            'status' => 'online',
            'cpu' => 'N/A',
            'ram' => 'N/A',
            'disk' => 'N/A'
        );

        try {
            // CPU usage
            $cpuLoad = sys_getloadavg();
            $cpu = round($cpuLoad[0], 2);
            $healthData['cpu'] = $cpu . " %";

            // RAM usage
            $free = shell_exec('free -m');
            if ($free) {
                $free = explode("\n", trim($free));
                $mem = explode(" ", preg_replace("!\s+!", " ", $free[1]));
                $ramTotal = $mem[1] ?? 0;
                $ramUsed = $mem[2] ?? 0;
                $ramUsage = $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 2) : 0;
                $healthData['ram'] = "{$ramUsed} MB / {$ramTotal} MB ({$ramUsage}%)";
            }

            // Disk usage
            $diskTotal = round(disk_total_space("/") / 1024 / 1024 / 1024, 2);
            $diskFree = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);
            $diskUsed = $diskTotal - $diskFree;
            $diskUsage = $diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 2) : 0;
            $healthData['disk'] = "{$diskUsed} GB / {$diskTotal} GB ({$diskUsage}%)";

        } catch (\Exception $e) {
            $healthData['status'] = 'error';
            Corelog::log("Local health check failed: " . $e->getMessage(), Corelog::ERROR);
        }

        return $healthData;
    }

    /**
     * Check health of remote server via SSH
     */
    private static function check_remote_health($node)
    {
        $healthData = array(
            'node_id' => $node['node_id'],
            'server' => $node['name'],
            'status' => 'offline',
            'cpu' => 'N/A',
            'ram' => 'N/A',
            'disk' => 'N/A'
        );

        try {
            $ssh_host = $node['ssh_host'];
            $ssh_port = $node['ssh_port'] ?? 22;
            $ssh_user = $node['ssh_user'];
            $ssh_pass = $node['ssh_pass'];

            // Create SSH connection
            $connection = @ssh2_connect($ssh_host, $ssh_port);
            if ($connection && @ssh2_auth_password($connection, $ssh_user, $ssh_pass)) {
                $healthData['status'] = 'online';

                // Get CPU load
                $stream = ssh2_exec($connection, "cat /proc/loadavg | awk '{print \$1}'");
                stream_set_blocking($stream, true);
                $cpuLoad = stream_get_contents($stream);
                $healthData['cpu'] = trim($cpuLoad) . " %";

                // Get RAM usage
                $stream = ssh2_exec($connection, "free -m | awk 'NR==2{printf \"%s MB / %s MB (%.2f%%)\", \$3, \$2, \$3*100/\$2}'");
                stream_set_blocking($stream, true);
                $ramUsage = stream_get_contents($stream);
                $healthData['ram'] = trim($ramUsage);

                // Get Disk usage
                $stream = ssh2_exec($connection, "df -h / | awk 'NR==2{printf \"%s / %s (%s)\", \$3, \$2, \$5}'");
                stream_set_blocking($stream, true);
                $diskUsage = stream_get_contents($stream);
                $healthData['disk'] = trim($diskUsage);

                ssh2_disconnect($connection);
            }
        } catch (\Exception $e) {
            Corelog::log("SSH connection failed for node {$node['node_id']}: " . $e->getMessage(), Corelog::ERROR);
        }

        return $healthData;
    }

    /**
     * Get latest health for all servers
     */
    public static function get_all_servers_health()
    {
        $query = "SELECT sh.*, n.name as node_name, n.ssh_host 
                  FROM server_health sh 
                  INNER JOIN node n ON sh.node_id = n.node_id 
                  WHERE sh.checked_at = (
                      SELECT MAX(checked_at) 
                      FROM server_health 
                      WHERE node_id = sh.node_id
                  )
                  ORDER BY n.name";
        
        $result = DB::query(self::$table, $query);
        $healthData = array();
        
        while ($data = mysqli_fetch_assoc($result)) {
            $healthData[] = $data;
        }
        
        return $healthData;
    }
}