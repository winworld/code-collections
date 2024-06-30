<?php 
    if( ! class_exists( 'MogoDBBackup_FTP' ) ) {
        class MogoDBBackup_FTP {
            public function __construct() {

            }

            public static function move_to_cloud() {
                global $mongodb_backup;
                $ftp_ftps = $mongodb_backup->mongodb_config['ftp']['ftps'];
                $ftp_server = $mongodb_backup->mongodb_config['ftp']['ftp_server'];
                $ftp_user = $mongodb_backup->mongodb_config['ftp']['ftp_user'];
                $ftp_password = $mongodb_backup->mongodb_config['ftp']['ftp_password'];
                $ftp_passive_mode = $mongodb_backup->mongodb_config['ftp']['ftp_passive_mode'];
                $ftp_remote_folder = $mongodb_backup->mongodb_config['ftp']['ftp_remote_folder'];
                $backup_filename_path = $mongodb_backup->mongodb_config['db']['backup_filename_path'];
                $backup_filename = $mongodb_backup->mongodb_config['db']['backup_filename'];
                $notication_emails = $mongodb_backup->mongodb_config['notification'];
                // Initiate connection
                if ($ftp_ftps) {
                    $connection_id = ftp_ssl_connect($ftp_server);
                } else {
                    $connection_id = ftp_connect($ftp_server);
                }

                if (!$connection_id) {
                    echo "Error: Can't connect to {$ftp_server}\n";
                    // continue;
                }

                // Login with user and password
                $login_result = ftp_login( $connection_id, $ftp_user, $ftp_password );

                if (!$login_result) {
                    echo "Error: Login wrong for {$ftp_server}\n";
                    // continue;
                }

                // Passive mode?
                ftp_pasv($connection_id, $ftp_passive_mode);

                // Upload file to ftp
                if (!ftp_put($connection_id, $ftp_remote_folder . "/" . $backup_filename, $backup_filename_path, FTP_BINARY)) {
                    $subject = 'DB Backup Failed to Move in Remote Server';
                    $output_message =  "DB Backup Failed!. Error: While uploading {$backup_filename} to {$ftp_server}.";
                } else {
                    $subject = 'DB Backup Successfully Move in Remote Server';
                    $output_message =  "DB Backup Successfully!.";
                }

                foreach( $notication_emails as $email ) {
                    Notification::send( $email, $subject, $output_message );
                }

                // Close ftp connection
                ftp_close($connection_id);

                // Delete original *.sql file
                if (file_exists($backup_filename)) {
                    unlink($backup_filename);
                }

                // Delete original *.gz file
                if (file_exists($backup_filename)) {
                    unlink($backup_filename);
                }
            }
        }
    }
?>