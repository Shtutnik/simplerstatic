<?php
namespace SimplerStatic;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class Wrapup_Task extends Task {

    /**
     * @var string
     */
    protected static $task_name = 'wrapup';

    public function perform() : bool {
        if ( $this->options->get( 'delete_temp_files' ) === '1' ) {
            Util::debug_log( 'Deleting temporary files' );
            $this->save_status_message( __( 'Wrapping up', 'simplerstatic' ) );
            $deleted_successfully = $this->delete_temp_static_files();
        } else {
            Util::debug_log( 'Keeping temporary files' );
        }

        return true;
    }

    /**
     * Delete temporary, generated static files
     *
     * @return true|\WP_Error True on success, WP_Error otherwise
     */
    public function delete_temp_static_files() {
        $archive_dir = $this->options->get_archive_dir();

        if ( file_exists( $archive_dir ) ) {
            $directory_iterator = new RecursiveDirectoryIterator(
                $archive_dir,
                FilesystemIterator::SKIP_DOTS
            );
            $recursive_iterator = new RecursiveIteratorIterator(
                $directory_iterator,
                RecursiveIteratorIterator::CHILD_FIRST
            );

            // recurse through the entire directory and delete all files / subdirectories
            foreach ( $recursive_iterator as $item ) {
                $success = $item->isDir() ? rmdir( $item ) : unlink( $item );
                if ( ! $success ) {
                    $message =
                        sprintf( 'Could not delete temporary file or directory: %s', $item );
                    $this->save_status_message( $message );
                    return true;
                }
            }

            // must make sure to delete the original directory at the end
            $success = rmdir( $archive_dir );
            if ( ! $success ) {
                $message =
                    sprintf( 'Could not delete temporary file or directory: %s', $archive_dir );
                $this->save_status_message( $message );
                return true;
            }
        }

        return true;
    }
}
