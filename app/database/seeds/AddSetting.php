<?php

use Phinx\Seed\AbstractSeed;

class AddSetting extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
      $size = array(
        'upload_size' => 1073741824
      );
      $this->insert('settings', $size);
    }
}
