<?php

namespace Klsandbox\MoveModelCommand;

use Illuminate\Console\Command;
use File;

class EditMoveModel extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'edit:movemodel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move models from App to App\Models\.';

    public function fire()
    {
        if (!File::exists(app_path('Models'))) {
            File::makeDirectory(app_path('Models'));
        }

        foreach (ListModel::getAllModels() as $model) {
            $this->processModel($model);
        }
    }

    private function processModel($model)
    {
        $this->comment($model->getFilename());

        $file = $model->openFile('r');
        $content = $file->fread($file->getSize());
        $content = preg_replace("/\bnamespace\s+.*?;/", "namespace App\Models;", $content);
        $file = $model->openFile('w');
        $file->fwrite($content);
        $file->fflush();

        $targetFolder = app_path('Models/' . $model->getBasename());
        if ($model->getPathname() != $targetFolder) {
            File::move($model->getPathname(), $targetFolder);
        }

        $this->comment('Move: ' . $model->getPathname() . ' to ' . $targetFolder);

        $className = preg_replace("/\.[^.]+$/", '', $file->getBasename());

        $appPath = base_path('app');
        $testsPath = base_path('tests');
        $databaseSeedsPath = base_path('database/seeds');
        $configPath = base_path('config');

        $command = "grep -l -R '^\s*use\b.*\\\\$className;' '$appPath' '$testsPath' '$databaseSeedsPath'";
        $this->comment("USE GREP COMMAND:$command");
        $out = null;
        $res = exec($command, $out);
        foreach ($out as $line) {
            if (!$line) {
                continue;
            }

            $regex = "/^(\\s*use\\s+).*?\\\\$className;/m";

            $this->comment("LINE:$line");
            $this->comment("REGEX:$regex");
            //dd($regex);
            $referrerContent = file_get_contents($line);

            if (preg_match($regex, $referrerContent)) {
                $this->comment('Regex found');
            } else {
                $this->comment('Regex not found');
                continue;
            }

            $newReferrerContent = preg_replace($regex, "\$1App\\Models\\$className;", $referrerContent);

            if ($referrerContent != $newReferrerContent) {
                file_put_contents($line, $newReferrerContent);
            }
        }

        //
        $command = "grep -l -R 'belongsTo\|hasMany\|hasOne' $appPath";
        $this->comment("RELATIONSHIP COMMAND:$command");
        $out;
        $res = exec($command, $out);
        foreach ($out as $line) {
            if (!$line) {
                continue;
            }

            $this->comment("LINE RELATIONSHIP:$line");
            $regex = "/(belongsTo|hasMany|hasOne)\('.*?\\\\$className'\)/m";
            $referrerContent = file_get_contents($line);
            $newReferrerContent = preg_replace($regex, "\$1('App\\Models\\$className')", $referrerContent);
            if ($newReferrerContent != $referrerContent) {
                file_put_contents($line, $newReferrerContent);
            }
        }

        //
        $command = "grep -l -R \"'model'[[:space:]]*=>[[:space:]]'App\\\\\\\\$className'\" '$configPath'";
        $this->comment("MODEL CONFIG COMMAND:$command");

        $out;
        $res = exec($command, $out);
        foreach ($out as $line) {
            if (!$line) {
                continue;
            }

            $this->comment("LINE RELATIONSHIP:$line");
            $regex = "/('model'\s*=>\s*)'App\\\\$className'/m";
            $referrerContent = file_get_contents($line);

            if (preg_match($regex, $referrerContent)) {
                $this->comment('Regex found');
            } else {
                $this->comment('Regex not found');
                continue;
            }

            $newReferrerContent = preg_replace($regex, "\$1'App\\Models\\$className'", $referrerContent);
            if ($newReferrerContent != $referrerContent) {
                file_put_contents($line, $newReferrerContent);
            }
        }

        //

        $command = "grep -l -R 'App\\\\$className::class' .";
        $this->comment("MODEL CONFIG CLASS COMMAND:$command");

        $out;
        $res = exec($command, $out);
        foreach ($out as $line) {
            if (!$line) {
                continue;
            }

            $this->comment("LINE RELATIONSHIP:$line");
            $regex = "/App\\\\$className::class/m";
            $referrerContent = file_get_contents($line);

            if (preg_match($regex, $referrerContent)) {
                $this->comment('Regex found');
            } else {
                $this->comment('Regex not found');
                continue;
            }

            $newReferrerContent = preg_replace($regex, "App\\Models\\$className::class", $referrerContent);
            if ($newReferrerContent != $referrerContent) {
                file_put_contents($line, $newReferrerContent);
            }
        }
    }
}
