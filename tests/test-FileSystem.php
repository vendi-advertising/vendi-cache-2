<?php declare(strict_types=1);
namespace Vendi\Cache\Tests;

use org\bovigo\vfs\vfsStream;
use Vendi\Cache\FileSystem;

/**
 * @group FileSystem
 */
class test_FileSystem extends vendi_cache_test_base_no_wordpress
{
    /**
     * @covers \Vendi\Cache\FileSystem::__construct
     * @covers \Vendi\Cache\FileSystem::get_maestro
     */
    public function test_concrete_methods()
    {
        $obj = new FileSystem($this->__get_new_maestro());
        $this->assertInstanceOf('\Vendi\Cache\Maestro', $obj->get_maestro());
    }

    /**
     * @covers \Vendi\Cache\FileSystem::get_root
     * @covers \Vendi\Cache\FileSystem::set_root
     */
    public function test_get_root_set_root()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);
        $this->assertSame($maestro->get_secretary()->get_cache_folder_abs(), $obj->get_root());
        $obj->set_root('/cheese');
        $this->assertSame('/cheese', $obj->get_root());
    }

    /**
     * @covers \Vendi\Cache\FileSystem::perform_trapped_function
     * @covers \Vendi\Cache\FileSystem::get_last_error
     * @covers \Vendi\Cache\FileSystem::handle_error
     */
    public function test_perform_trapped_function()
    {
        $obj = new FileSystem($this->__get_new_maestro());
        $this->assertNull($obj->get_last_error());

        //mkdir should throw an E_WARNING because this folder should always exist
        $this->assertFileExists('/');
        $obj->perform_trapped_function(function () {
            \mkdir('/');
        });

        $last_error = $obj->get_last_error();
        $this->assertInstanceOf('\ErrorException', $last_error);
        $this->assertSame('mkdir(): File exists', $last_error->getMessage());
    }

    /**
     * @covers \Vendi\Cache\FileSystem::file_exists
     * @covers \Vendi\Cache\FileSystem::file_exists_abs
     * @covers \Vendi\Cache\FileSystem::delete_file
     * @covers \Vendi\Cache\FileSystem::delete_file_abs
     * @group  FileSystemFileExists
     */
    public function test_file_exists()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);

        //The default root for FS is the cache folder, reset it to the VFS root
        //to make test paths easier
        $obj->set_root(vfsStream::url($this->get_root_dir_name_no_trailing_slash()));

        //The absolute path to our new file
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . '/cheese.txt');

        //Should not exist
        $this->assertFileNotExists($abs);
        $this->assertFalse($obj->file_exists('cheese.txt'));

        //Deleting a non-existant file is technically valid
        $this->assertTrue($obj->delete_file('cheese.txt'));

        //Create the file in it
        vfsStream::newFile('cheese.txt')
            ->at($this->get_vfs_root())
        ;

        //Should exist now
        $this->assertFileExists($abs);
        $this->assertTrue($obj->file_exists('cheese.txt'));

        //Delete the file
        $this->assertTrue($obj->delete_file('cheese.txt'));

        //Should not exist
        $this->assertFileNotExists($abs);
        $this->assertFalse($obj->file_exists('cheese.txt'));
    }

    /**
     * @covers \Vendi\Cache\FileSystem::delete_file
     * @covers \Vendi\Cache\FileSystem::delete_file_abs
     * @covers \Vendi\Cache\FileSystem::_do_delete_file_abs
     * @group  FileSystemDeleteException
     */
    public function test_delete_file__with_exception()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);

        //The default root for FS is the cache folder, reset it to the VFS root
        //to make test paths easier
        $obj->set_root(vfsStream::url($this->get_root_dir_name_no_trailing_slash()));

        $the_folder = 'folder';
        $the_file = 'cheese.txt';

        //Create a directory with no permissions
        vfsStream::newDirectory($the_folder, 0000)
            ->at(
                    $this
                        ->get_vfs_root()
                )
        ;

        //Create a file in the above folder.
        //NOTE: The permissions for deletion are based on the folder, not the file.
        //See: https://github.com/mikey179/vfsStream/issues/78#issuecomment-42605128
        vfsStream::newFile($the_file)
            ->at(
                    $this
                        ->get_vfs_root()
                        ->getChild($the_folder)
                )
        ;

        //The absolute path to our new file
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . "/$the_folder/$the_file");

        $this->assertNull($obj->get_last_error());

        $this->assertFileExists($abs);
        $obj->delete_file("$the_folder/$the_file");
        $this->assertFileExists($abs);

        $last_error = $obj->get_last_error();
        $this->assertInstanceOf('\Exception', $last_error);
        $this->assertSame('unlink() on file failed', $last_error->getMessage());
    }

    /**
     * @covers \Vendi\Cache\FileSystem::delete_dir
     * @covers \Vendi\Cache\FileSystem::delete_dir_abs
     * @covers \Vendi\Cache\FileSystem::_do_delete_dir_abs
     * @covers \Vendi\Cache\FileSystem::_do_delete_dir_abs__root_dir
     * @group  FileSystemDeleteDir
     */
    public function test_delete_dir()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);

        //The default root for FS is the cache folder, reset it to the VFS root
        //to make test paths easier
        $obj->set_root(vfsStream::url($this->get_root_dir_name_no_trailing_slash()));

        $the_folder = 'folder';
        $the_file = 'cheese.txt';

        //Create a directory with no permissions
        vfsStream::newDirectory($the_folder)
            ->at(
                    $this
                        ->get_vfs_root()
                )
        ;

        //Create a file in the above folder.
        //NOTE: The permissions for deletion are based on the folder, not the file.
        //See: https://github.com/mikey179/vfsStream/issues/78#issuecomment-42605128
        vfsStream::newFile($the_file)
            ->at(
                    $this
                        ->get_vfs_root()
                        ->getChild($the_folder)
                )
        ;

        //The absolute path to our new file
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . "/$the_folder/");

        $this->assertFileExists($abs);
        $this->assertTrue($obj->delete_dir("$the_folder"));
        $this->assertFileNotExists($abs);
    }

    /**
     * @covers \Vendi\Cache\FileSystem::delete_dir
     * @covers \Vendi\Cache\FileSystem::delete_dir_abs
     * @covers \Vendi\Cache\FileSystem::_do_delete_dir_abs
     */
    public function test_delete_dir__not_a_dir()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);

        //The default root for FS is the cache folder, reset it to the VFS root
        //to make test paths easier
        $obj->set_root(vfsStream::url($this->get_root_dir_name_no_trailing_slash()));

        $the_folder = 'folder';

        //The absolute path to our new file
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . "/$the_folder/");

        $this->assertFileNotExists($abs);
        $this->assertTrue($obj->delete_dir("$the_folder"));
        $this->assertFileNotExists($abs);
    }

    /**
     * @covers \Vendi\Cache\FileSystem::delete_dir
     * @covers \Vendi\Cache\FileSystem::delete_dir_abs
     * @covers \Vendi\Cache\FileSystem::get_directory_contents_abs
     * @covers \Vendi\Cache\FileSystem::_do_delete_dir_abs
     * @group  FileSystemDeleteException
     * This test creates a directory with zero permissions and attempts to remove it. This will cause
     * the \RecursiveDirectoryIterator to fail because it cannot be opened.
     */
    public function test_delete_dir__with_exception()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);

        //The default root for FS is the cache folder, reset it to the VFS root
        //to make test paths easier
        $obj->set_root(vfsStream::url($this->get_root_dir_name_no_trailing_slash()));

        $the_folder = 'folder';

        //Create a directory with no permissions
        vfsStream::newDirectory($the_folder, 0000)
            ->at(
                    $this
                        ->get_vfs_root()
                )
        ;

        //The absolute path to our new file
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . "/$the_folder");

        $this->assertNull($obj->get_last_error());

        $this->assertFileExists($abs);
        $obj->delete_dir("$the_folder");
        $this->assertFileExists($abs);

        $last_error = $obj->get_last_error();
        $this->assertInstanceOf('\Exception', $last_error);

        $this->assertContains('failed to open dir', $last_error->getMessage());
    }

    /**
     * @covers \Vendi\Cache\FileSystem::delete_dir
     * @covers \Vendi\Cache\FileSystem::delete_dir_abs
     * @covers \Vendi\Cache\FileSystem::get_directory_contents_abs
     * @group  FileSystemDeleteException
     * This test creates a directory with zero permissions and attempts to remove it. This will cause
     * the \RecursiveDirectoryIterator to fail because it cannot be opened.
     */
    public function test_delete_dir__with_exception_from_deeper_dir()
    {
        $maestro = $this->__get_new_maestro();
        $obj = new FileSystem($maestro);

        //The default root for FS is the cache folder, reset it to the VFS root
        //to make test paths easier
        $obj->set_root(vfsStream::url($this->get_root_dir_name_no_trailing_slash()));

        $the_parent_folder = 'parent_folder';
        $the_child_folder = 'child_folder';
        $the_file = 'cheese.txt';

        //Create a directory with no permissions
        vfsStream::newDirectory($the_parent_folder)
            ->at(
                    $this
                        ->get_vfs_root()
                )
        ;

        //Create a directory with no permissions
        vfsStream::newDirectory($the_child_folder, 0000)
            ->at(
                    $this
                        ->get_vfs_root()
                        ->getChild($the_parent_folder)
                )
        ;

        //Create a file in the above folder.
        vfsStream::newFile($the_file)
            ->at(
                    $this
                        ->get_vfs_root()
                        ->getChild($the_parent_folder . DIRECTORY_SEPARATOR . $the_child_folder)
                )
        ;

        //The absolute path to our new file
        $abs = vfsStream::url($this->get_root_dir_name_no_trailing_slash() . "/$the_parent_folder");

        $this->assertNull($obj->get_last_error());

        $this->assertFileExists($abs);
        $obj->delete_dir("$the_parent_folder");
        $this->assertFileExists($abs);

        $last_error = $obj->get_last_error();
        $this->assertInstanceOf('\Exception', $last_error);

        $this->assertContains('failed to open dir', $last_error->getMessage());
    }
}
