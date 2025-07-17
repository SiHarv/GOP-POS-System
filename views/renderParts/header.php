<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function getNetworkInfo() {
    $info = ['ip' => 'Unable to detect', 'gateway' => 'Unable to detect'];
    
    // For Windows systems
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Execute ipconfig command with /all to get detailed info
        $output = shell_exec('ipconfig /all');
        if ($output) {
            // Split by adapters
            $adapters = preg_split('/\r?\n\r?\n/', $output);
            
            foreach ($adapters as $adapter) {
                // Skip if it's not a real network adapter or is disconnected
                if (stripos($adapter, 'Ethernet adapter') === false && 
                    stripos($adapter, 'Wireless LAN adapter') === false) {
                    continue;
                }
                
                // Skip if adapter is disconnected or media disconnected
                if (stripos($adapter, 'Media disconnected') !== false ||
                    stripos($adapter, 'Media State') !== false && stripos($adapter, 'Media disconnected') !== false) {
                    continue;
                }
                
                // Get IP address from this adapter
                preg_match('/IPv4 Address[.\s]*:\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $adapter, $ipMatch);
                
                // Get Default Gateway from this adapter
                preg_match('/Default Gateway[.\s]*:\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $adapter, $gatewayMatch);
                
                if (!empty($ipMatch[1]) && !empty($gatewayMatch[1])) {
                    $ip = $ipMatch[1];
                    $gateway = $gatewayMatch[1];
                    
                    // Make sure it's a private network IP and has a valid gateway
                    if ($ip !== '127.0.0.1' && $gateway !== '0.0.0.0' && 
                        ($gateway !== $ip) && // Gateway shouldn't be the same as IP
                        ($ip !== '192.168.56.1') && // Skip VirtualBox/VMware adapter
                        (strpos($ip, '192.168.') === 0 || 
                         strpos($ip, '10.') === 0 || 
                         strpos($ip, '172.') === 0)) {
                        $info['ip'] = $ip;
                        $info['gateway'] = $gateway;
                        break; // Found active adapter, stop looking
                    }
                }
            }
        }
        
        // If still not found, try alternative method to get active connection
        if ($info['ip'] === 'Unable to detect') {
            $routeOutput = shell_exec('route print 0.0.0.0');
            if ($routeOutput) {
                // Find the active route (0.0.0.0)
                preg_match('/0\.0\.0\.0\s+0\.0\.0\.0\s+([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\s+([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $routeOutput, $routeMatch);
                if (!empty($routeMatch[1]) && !empty($routeMatch[2])) {
                    $info['gateway'] = $routeMatch[1];
                    $info['ip'] = $routeMatch[2];
                }
            }
        }
    } else {
        // For Linux/Unix systems
        $output = shell_exec("hostname -I");
        if ($output) {
            $ips = explode(' ', trim($output));
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && 
                    $ip !== '127.0.0.1' && (
                    strpos($ip, '192.168.') === 0 || 
                    strpos($ip, '10.') === 0 || 
                    strpos($ip, '172.') === 0
                )) {
                    $info['ip'] = $ip;
                    break;
                }
            }
        }
        
        // Get default gateway for Linux/Unix
        $gatewayOutput = shell_exec("ip route | grep default");
        if ($gatewayOutput) {
            preg_match('/default via ([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $gatewayOutput, $gatewayMatch);
            if (!empty($gatewayMatch[1])) {
                $info['gateway'] = $gatewayMatch[1];
            }
        }
    }
    
    return $info;
}

$networkInfo = getNetworkInfo();
?>

<header class="main-header">
    <div class="header-content">
        <div class="header-left">
            <!-- <img src="../../icon/icon.png" alt="" style="height: 40px; width: 30px; margin-right: 10px;"> -->
            <h1 class="fw-bold">GOP-<span class="fw-bold">MARKETING</span></h1>
        </div>
        <div class="header-right">
            <span class="ip-display">
                <span class="ip-text">
                    <span class="ip-label">IP:</span> <?php echo htmlspecialchars($networkInfo['ip']); ?>
                    <span class="gateway-separator">|</span>
                    <span class="gateway-label">GW:</span> <?php echo htmlspecialchars($networkInfo['gateway']); ?>
                </span>
            </span>
        </div>
    </div>
</header>