<?php

namespace Klsandbox\MoveModelCommand;

use Illuminate\Console\Command;
use File;
use Symfony\Component\Finder\SplFileInfo;

class ListModel extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'list:model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all models.';

    /**
     * @return SplFileInfo */
    public static function getAllModels()
    {
        $files = File::allFiles(app_path());
        foreach ($files as $file) {
            /* @var $file SplFileInfo */
            $noExt = preg_replace("/\.[^.]+$/", '', $file->getBasename());
            $f = $file->openFile();
            $content = $f->fread($f->getSize());

            if (str_contains($content, "class $noExt extends Model")) {
                yield $file;
            }
        }
    }

    public function fire()
    {
        foreach (self::getAllModels() as $model) {
            $this->comment($model->getFilename());
        }
    }
}
