<?php

use Phinx\Migration\AbstractMigration;

class UpdateTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
      $this->table('update')
      ->addColumn('version', 'string')
      ->create();

      $this->table('settings')
      ->addColumn('ssh_login', 'string', [
          'null' => true
      ])
      ->addColumn('ssh_pass', 'string', [
          'null' => true
      ])
      ->addColumn('server_user', 'string', [
          'null' => true
      ])
      ->addColumn('server_group', 'string', [
          'null' => true
      ])
      ->update();
    }
}
