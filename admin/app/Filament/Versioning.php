<?php

declare(strict_types=1);

namespace App\Filament;

use Illuminate\Support\Facades\Process;

class Versioning
{
    public function getName(): string
    {
        return trans('general.release');
    }

    public function getVersion(): string
    {
        // return trim(Process::path(base_path())->run([
        //    'git', 'describe', '--tags', '--abbrev=0',
        // ])->output());

        return 'v1.5.0';
    }
}
