<?php

use Phinx\Migration\AbstractMigration;

class AddDirAndAccess extends AbstractMigration
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
      $this->table('dirs')
      ->addColumn('name', 'string')
      ->addColumn('location', 'string')
      ->create();

      $this->table('access')
      ->addColumn('id_dir', 'integer', [
        'null' => true
      ])
      ->addColumn('type', 'string', [
        'null' => true
      ])
      ->addColumn('id_user', 'integer', [
          'null' => true
      ])
      ->addForeignKey('id_user', 'users', 'id', [
        'delete' => 'SET_NULL',
        'update' => 'NO_ACTION'
      ])
      ->addForeignKey('id_dir', 'dirs', 'id', [
        'delete' => 'SET_NULL',
        'update' => 'NO_ACTION'
      ])
      ->create();
    }
}
