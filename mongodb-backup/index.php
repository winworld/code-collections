<?php
    if( !class_exists( 'MongoDBBackup_Init' ) ) {
        class MongoDBBackup_Init {
            public $mongodb_config;

            public function __construct() {

            }

            public function init() {

                $this->define( 'MONGODB_FILE', __FILE__ );
                $this->define( 'MONGODB_DIR', __DIR__ );
                $this->define( 'MONGODB_BACKUP_DIR', '/home/clurmuser/webapps/clurm/mongodb-backup' );

                $this->mongodb_config = [
                    'db' => [
                        'host' => 'localhost',
                        'port' => '27017',
                        'db_name' => 'clarum_production', // change backup db name in here
                    ],
                    'ftp' => [
                        'ftp_server' => '108.167.181.59',
                        'ftp_user' => '',
                        'ftp_password' => '',
                        'ftp_remote_folder' => '',
                        'ftps' => false,
                        'ftp_passive_mode' => true
                    ],
                    'notification' => [
                        'moehtetnaing.ucsh@gmail.com',
                        // 'ztun.25@gmail.com'
                    ]
                ];

                require 'vendor/autoload.php';
                include_once( MONGODB_DIR . '/includes/class-mongodb-backup.php' );
                include_once( MONGODB_DIR . '/includes/class-upload-cloud.php' );
                include_once( MONGODB_DIR . '/includes/class-notification.php' );
                
                $is_backup_done = MongoDBBackup::take_backup();
                if( $is_backup_done ) {
                    MogoDBBackup_FTP::move_to_cloud();
                }
                
            }

            public function define( $name, $value = true ) {
                if( ! defined( $name ) ) {
                  define( $name, $value );
                }
            }
        }

        function mongodb_backup_init() {
            global $mongodb_backup;
            if( ! isset( $mongodb_backup ) ) {
                $mongodb_backup = new MongoDBBackup_Init();
                $mongodb_backup->init();
            }
            return $mongodb_backup;
        }
        
        mongodb_backup_init();

    }
?>