<?php

set_time_limit(300);




class FlxZipArchive extends ZipArchive
{

    public function addDir($location, $name)
    {
        $this->addEmptyDir($name);
        $this->addDirDo($location, $name);
    }

    private function addDirDo($location, $name)
    {
        $name .= '/';
        $location .= '/';
        $dir = opendir($location);
        //echo basename($location), '<br>';
        if (basename($location) == 'backup-export')
            return true;
        while ($file = readdir($dir)) {
            if ($file == '.' || $file == '..')
                continue;
            $do = (filetype($location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
    }

}

class BackupLocal
{

    public function startBackup($all_configs)
    {
        /**
         * 
         * Создать папку 
         * в папке создать htaccess deny from all
         * Сделать зип всех файлов (кроме этой папки)
         * Назвать зип дате 
         * Создать dump.sql с именем как зип.
         * Зазипить и удалить исходник
         * 
         */

        include $all_configs['path'] . 'classes/mysqldump.php';
        
        $tmpDir = $all_configs['path'] . 'backup-export/';
        $tmpName = $_SERVER['SERVER_NAME'] . '-' . date("YmdHis");

        @mkdir($tmpDir, 0775);
        

        $fp = fopen($tmpDir . '.htaccess', 'w');
        if ($fp) {
            fwrite($fp, 'deny from all');
            fclose($fp);
        } else {
            echo 'Could not create .htaccess';
            return false;
        }

        ## files to ZIP
        $zip_file_name = $tmpDir . $tmpName . '-fs' . '.zip';
        $za = new FlxZipArchive;
        $res = $za->open($zip_file_name, ZipArchive::CREATE);
        if ($res === TRUE) {
            $za->addDir($all_configs['sitepath'], basename($all_configs['sitepath']));
            $za->close();
        } else {
            echo 'Could not create a zip archive';
            return false;
        }



        ### SQL
        $sqlFilename = $tmpDir . $tmpName . '.sql';
        $dump = new Ifsnop\Mysqldump\Mysqldump('mysql:host=' . $all_configs['dbcfg']['host']
                . ';dbname=' . $all_configs['dbcfg']['dbname'], $all_configs['dbcfg']['username'], $all_configs['dbcfg']['password']);
        $dump->start($sqlFilename);

        #добавим SQL зип
        $za = new FlxZipArchive;
        $res = $za->open($sqlFilename . '.zip', ZipArchive::CREATE);
        if ($res === TRUE) {
            $za->addFile($sqlFilename, $tmpName . '.sql');
            $za->close();
            unlink($sqlFilename);
        } else {
            echo 'Could not create a sql zip archive';
            return false;
        }
        
        return true;
    }

}
