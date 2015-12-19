<?php
/**
 * A class that contains code to handle the downloading of a .zip archive
 * for the requested publication.
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class DownloadZip extends Siteaction
    {
/**
 * Handle downloading zip /
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {

            # Get temp directory where we upload files to the user.
            $tmp = ini_get('upload_tmp_dir');

            # Get the publication object matching this id and pass to twig.
            $pub = R::load('publication', $context->rest()[0]);

            # Check if this was a download request.
            if (!$context->haspostpar('enablezip'))
            {
                $context->local()->addval(['pub' => $pub]);
                return 'downloadzip.twig';
            }

            $errs = [];
            $fullpath = strval($tmp . '\publication' . $context->rest()[0] . '.zip');

            # If a redbean ID does not exist, i.e. was not found in the DB.
            if ($pub->id < 1)
            {
                return 'downloadzip.twig';
            }


            # Create the zip archiver
            $zip = new ZipArchive();
            
            # Attempt to open the zip name.
            if ($zip->open($fullpath,  ZipArchive::CREATE) != TRUE)
            {
                $errs[] = "Could not create a zip archive: server error.";
            }

            # Get each file and pass them to the zip archiver.
            foreach ($pub->ownFile as $file)
            {
                $realpath = $_SERVER['DOCUMENT_ROOT'] . $file->path;

                if (!$zip->addFile($realpath, $file->name))
                {
                    $errs[] = "Could not add file";
                }
            }

            # If errors were detected.
            if (count($errs) > 0)
            {
                $context->local()->addval(['errors' => $errs]);
                
                return 'downloadzip.twig';
            }

            # Otherwise send the user the file.
            $context->sendFile($fullpath, strval($context->rest()[0]) . '.zip',
                              'application/zip', '', strval(md5($fullpath)));

            # Delete it from FS
            unlink($fullpath);

            return 'downloadzip.twig';


        }
    }
?>
