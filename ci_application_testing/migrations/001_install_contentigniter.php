<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Install_contentigniter extends CI_Migration
{

    public function up()
    {
        /*		echo '<p>Creating Test Table</p>';
          $this->dbforge->add_field('id');
          $this->dbforge->add_field(array(
              'test_blog_title' => array(
                  'type' => 'VARCHAR',
                  'constraint' => '100',
              ),
              'test_blog_description' => array(
                  'type' => 'TEXT',
                  'null' => TRUE,
              ),
          ));
          fb_log($this->dbforge);
          echo $this->dbforge->create_table('test_blog',TRUE);
          fb_log($this->dbforge);
          echo '<p>Done</p>';*/
    }

    public function down()
    {
        /*		echo '<p>Removing Test Table</p>';
          $this->dbforge->drop_table('test_blog');
          echo '<p>Done</p>';*/
    }
}