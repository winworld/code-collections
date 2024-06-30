<?php
class MongoDBBackup {

    private $to;
    private $db = [];
    private $ftp = [];

    public function __construct() {
        
    }

    public function addFTP($ftp_server, $ftp_user, $ftp_password, $ftp_remote_folder = '', $ftps = false, $ftp_passive_mode = true) {
        $this->ftp[] = [
            'ftp_server' => $ftp_server,
            'ftp_user' => $ftp_user,
            'ftp_password' => $ftp_password,
            'ftp_remote_folder' => $ftp_remote_folder,
            'ftps' => $ftps,
            'ftp_passive_mode' => $ftp_passive_mode,
        ];
    }

    public function performBackup() {
        foreach ($this->db as $db_item) {
            // Create SQL dump and gzip the dumped file
            exec("mysqldump -u {$db_item['db_user']} -p{$db_item['db_password']} --allow-keywords --add-drop-table --complete-insert --hex-blob --quote-names {$db_item['db_name']} > {$db_item['sql_file']}");
            exec("gzip {$db_item['sql_file']}");

            // FTP transfer: Transfer sql dump to the configured ftp servers
            foreach ($this->ftp as $ftp_item) {
                // Initiate connection
                if ($ftp_item['ftps']) {
                    $connection_id = ftp_ssl_connect($ftp_item['ftp_server']);
                } else {
                    $connection_id = ftp_connect($ftp_item['ftp_server']);
                }

                if (!$connection_id) {
                    echo "Error: Can't connect to {$ftp_item['ftp_server']}\n";
                    continue;
                }

                // Login with user and password
                $login_result = ftp_login($connection_id, $ftp_item['ftp_user'], $ftp_item['ftp_password']);

                if (!$login_result) {
                    echo "Error: Login wrong for {$ftp_item['ftp_server']}\n";
                    continue;
                }

                // Passive mode?
                ftp_pasv($connection_id, $ftp_item['ftp_passive_mode']);

                // Upload file to ftp
                if (!ftp_put($connection_id, $ftp_item['ftp_remote_folder'] . "/" . $db_item['sql_file'] . '.gz', $db_item['sql_file'] . '.gz', FTP_BINARY)) {
                    $this->sendNotificationEmail('Failed Backup Notification', "DB Backup Failed!. Error: While uploading {$db_item['sql_file']}.gz to {$ftp_item['ftp_server']}.");
                    echo "Error: While uploading {$db_item['sql_file']}.gz to {$ftp_item['ftp_server']}.\n";
                } else {
                    $this->sendNotificationEmail('Successful Backup Notification', 'DB Backup Successfully!.');
                }

                // Close ftp connection
                ftp_close($connection_id);
            }

            // Delete original *.sql file
            if (file_exists($db_item['sql_file'])) {
                unlink($db_item['sql_file']);
            }

            // Delete original *.gz file
            if (file_exists($db_item['sql_file'] . '.gz')) {
                unlink($db_item['sql_file'] . '.gz');
            }
        }
    }

    private function sendNotificationEmail($subject, $body) {
        sent_noti_email($this->to, $subject, $body);
    }
}

// Example usage:
$backup = new MongoDBBackup('example@example.com');

// Add databases to backup
$backup->addDatabase('dbuser1', 'dbpassword1', 'dbname1');
$backup->addDatabase('dbuser2', 'dbpassword2', 'dbname2');

// Add FTP servers to transfer backups
$backup->addFTP('ftp_server_ip_address1', 'ftpusername1', 'ftppassword1', '/mysite/backups1');
$backup->addFTP('ftp_server_ip_address2', 'ftpusername2', 'ftppassword2', '/mysite/backups2', true);

// Perform backup
$backup->performBackup();
