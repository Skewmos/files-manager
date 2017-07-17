<?php

use Phinx\Seed\AbstractSeed;

class AddAdmin extends AbstractSeed
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
      $rank = array(
        'name' => 'admin',
      );
      $this->insert('ranks', $rank);

      $rank = array(
        'name' => 'utilisateur'
      );
      $this->insert('ranks', $rank);

      $user = array(
        'email' => 'admin@admin.fr',
        'password' => password_hash("admin", PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s'),
        'id_rank' => 1
      );
      $this->insert('users', $user);
    }
}
