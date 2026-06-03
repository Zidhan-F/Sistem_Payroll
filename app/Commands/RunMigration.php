<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunMigration extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'run:migrasi';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Runs custom raw SQL migration controller';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'run:migrasi';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $migrasi = new \App\Controllers\Migrasi();
        $result = $migrasi->index();
        CLI::write($result, 'green');
    }
}
