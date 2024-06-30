<?php 
    if( ! class_exists( 'MongoDBBackup' ) ) {
        class MongoDBBackup {
            public function __construct() {

            }

            public static function take_backup() {
                global $mongodb_backup;          
                // MongoDB server details
                $host = $mongodb_backup->mongodb_config['db']['host'];
                $port = $mongodb_backup->mongodb_config['db']['port'];
                $database = $mongodb_backup->mongodb_config['db']['db_name'];
                $backupFile = MONGODB_BACKUP_DIR . '/' . $database . '-' . date('Y-m-d') . '.gz';
                $backupFileName = $database . '-' . date('Y-m-d') . '.gz';
                $notication_emails = $mongodb_backup->mongodb_config['notification'];
                $mongodb_backup->mongodb_config['db']['backup_filename_path'] = $backupFile;
                $mongodb_backup->mongodb_config['db']['backup_filename'] = $backupFileName;
                $is_backup_done = false;
                // Create backup directory if it doesn't exist
                if ( ! is_dir( MONGODB_BACKUP_DIR ) ) {
                    mkdir( MONGODB_BACKUP_DIR, 0755, true );
                }
                // Command to run mongodump
                $command = "mongodump --host $host --port $port --db $database --archive=$backupFile --gzip";
                // Execute the command
                $output = shell_exec($command);
                if ($output === null) {
                    $output_message =  "Backup completed successfully. File saved to: $backupFile";
                    $is_backup_done = true;
                    $subject = 'DB Backup Successfully in Local Server';
                } else {
                    $output_message = "Error: $output";
                    $subject = 'DB Backup Failed in Local Server';
                }
                foreach( $notication_emails as $email ) {
                    Notification::send( $email, $subject, $output_message );
                }
                return $is_backup_done;
            }

        }

    }
?>