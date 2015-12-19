<?php
/**
 * A class that contains code to handle any requests for the upload page
 *
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Upload extends Siteaction
    {


/**
 * @var object      The publication object
 */
        private $pub;

/**
 * @var array       The file objects
 */
        private $files = [];

/**
 * @var int         The max. character to persist in the DB.
 */
        private $maxname = 50;
        
/**
 * Helper to handle error checking and moving uploaded files.
 *
 * @param object    $context    The context object for the site
 * @param object    $base       File system base information.
 *
 * @return string    A template name rendered with errors if any, otherwise returns
 *                   the empty string '' to indicate success. Test using empty() for sucess.
 */        
        private function handleuploads($context, $base)
        {
            # Handle files.
            # Set the upload directory for publications - this will be in the form
            # /assets/data/type/year/month/day
            $storedbase = $context->local()->base() . '/assets/data/'
                    . $base . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';

            $stored = $_SERVER['DOCUMENT_ROOT'] . $storedbase; 
            
            # If this is not a directory, then create it.
            if (!is_dir($stored))
            {
                mkdir($stored, 0, true);
            }
            
            # Errors found
            $errs = [];
            
            # Ensure FILES is not empty
            if (empty($_FILES['fileupload']))
            {
                $context->local()->message('error', 'Something went wrong..., no files transmitted');
                return 'upload.twig';
            }

            # Ensure at least 2 files posted - i.e. a publication + required preview..
            if (count($_FILES['fileupload']['tmp_name']) < 2)
            {
                $context->local()->message('error', 'You must post at least two files.');
                return 'upload.twig';
            }
            
            # Check if upload was okay.
            foreach ($_FILES['fileupload']['error'] as $err)
            {
                
                $error = array( 
                    0=>"There is no error, the file uploaded with success", 
                    1=>"The uploaded file exceeds the upload_max_filesize directive in php.ini", 
                    2=>"The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                    3=>"The uploaded file was only partially uploaded", 
                    4=>"No file was uploaded", 
                    6=>"Missing a temporary folder" 
                ); 
                
                if ($err > 0)
                {
                    # Append the error
                    $errs[] = $error[(int)$err];
                }
                
            }    
            
            # If any errors were found
            if (count($errs) > 0)
            {
                # Return the errors via TWIG
                foreach ($errs as $err)
                {
                    $context->local()->message('error', $err);
                }
                return 'upload.twig';
            }
            
            # Do further error checking - size: 0 files are refuted 
            foreach ($_FILES['fileupload']['size'] as $fsize)
            {
                if ($fsize <= 0)
                {
                    # Return the errors via TWIG
                    $context->local()->message('error', 'File size cannot be zero, 
                                               check that you are not uploading 0 byte files.');
                    return 'upload.twig';
                }
            }
            
            $mimeerrs = NULL;
            # Check MIME Types - For Raw Data we allow any MIME type. For others we list them
            # in an array.
            $count = 0;
            foreach ($_FILES['fileupload']['type'] as $fmime)
            {
                
                # Check the final file (Will always be image). If the user has edited the HTML required
                # Then it makes sense to still report an error for the incorrect MIME type as the input is required.
                if ($count == count($_FILES['fileupload']['type']) - 1)
                {
                    
                    if (strpos($fmime, 'image/') !== false)
                    {
                        # Breaks out of the entire loop
                        break;
                    }
                    else
                    {
                        $mimeerrs[] = 'Image must be an image type : i.e. JPG, PNG, GIF';
                        # We are done so break out still.
                        break; 
                    }
                }    

                # Set up allowed MIME types per possible choice.
                # If the array is empty, allow all MIMEs.
                $allowedmime = array(
                                        'Paper'         => array(
                                                                    'application/pdf',
                                                                    'application/msword',
                                                                    'application/vnd.openxmlformats',
                                                                    ),
                                        'Mobile Applications'       
                                                       => array(
                                                                    'application/octet-stream',
                                                                    'application/vnd.android.package-archive',
                                                                    ),
                                        'Code'         => array(
                                                                    'text/' # Accept anything with text
                                                                    ),
                                        'Experimental Results' => array(),
                                    );
                
                # If the lookup table has a type which is unknown to us.
                if (!array_key_exists($base, $allowedmime))
                {
                    # Go to the next loop.
                    continue;
                }

                $lookup = $allowedmime[$base];

                # For each look up array.
                foreach ($lookup as $index => $mime)
                {
                    if (strpos($fmime, $mime) !== false)
                    {
                        # No MIME errors, break out of this check
                        break;
                    }
                    
                    # If the last item was checked then there must be an error.
                    if ($index == count($lookup) - 1)
                    {
                        $mimeerrs[] = 'Allowable mime types for this category are: ' . implode(', ', $lookup) 
                                     . ' File mime: ' . $fmime . ' was found instead.';
                    }
                    
                }
                
                $count++;
                
            }
            
            # Count the errors, if there are any exit out of the file moving.
            if (count($mimeerrs) > 0)
            {
                # Return the errors via TWIG
                foreach ($mimeerrs as $err)
                {
                    $context->local()->message('error', $err);
                }
                return 'upload.twig';
            }
            
            # Attempt to move the files.                
            foreach ($_FILES['fileupload']['tmp_name'] as $i => $temp) 
            {
                
                # Ensure the file path does exist
                if ($temp != "")
                {
                    
                    # Move the files to our website.
                    $newfile = $stored . $_FILES['fileupload']['name'][$i];
                    $basefile = $storedbase . $_FILES['fileupload']['name'][$i];
                    $replaceindex = 1;

                    # Check for the same name and rename if it does exist.
                    while (file_exists($newfile))
                    {
                        # Add replaceindex to the file name before the file extension.
                        $newfile = str_replace('.', $replaceindex . '.', $newfile);
                        # Idem for the base file - ensure it points to the correct one.
                        $newbase = explode('/', $newfile);
                        $newfile = $newbase[count($newbase) - 1];
                        # Ensure both the basefile and the relative file point to the new file name
                        $tempmod = explode('/', $basefile);
                        $tempmod[count($tempmod) - 1] = $newfile;
                        # Set the values, the new file requires the full machine path
                        $newfile = $_SERVER['DOCUMENT_ROOT'] . implode('/', $tempmod);
                        $basefile = implode('/', $tempmod);

                        # Increment the number of the file
                        $replaceindex++;
                    }


                    # Move the file to the directory.
                    if (move_uploaded_file($temp, $newfile))
                    {
                        # Get a File bean
                        $f = R::dispense('file');
                        $f->name = $_FILES['fileupload']['name'][$i];
                        $f->size = $_FILES['fileupload']['size'][$i];
                        $f->type = $_FILES['fileupload']['type'][$i];
                        # New File incase the name was changed
                        $f->path = $basefile;
                        # Associate it with a publication
                        $this->files[] = $f;
                    }
                    else
                    {
                        # Display upload file error
                        $context->local()->message('error', 'There was an error uploading the file.');
                        return 'upload.twig';
                    }
                }
                else
                {
                    # Display tmp folder error
                    $context->local()->message('error', 'The file path could not be resolved.');
                    return 'upload.twig';
                }
                
            }
            
            # Return an empty value to indicate to not return prematurely.
            return '';
        }
/**
 * Handle setting up the ORM handling, form handling and delegating 
 * upload information to a separate handler.
 *
 * @param object    $context    The context object for the site
 *
 * @return string    A template name
 */
        public function handle($context)
        {          
            # Ensure user is valid.
            $context->mustbestaff();

            # Check if it's a post 
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
            {
                # Dispense a publication, author and file bean.
                $this->pub = R::dispense('publication');
                
                # Set up a new publication handler.
                $pubcreator = new Manipulatepub($this->pub, 'upload.twig');
                $returnerr = $pubcreator->manipulatepublication($context);

                # If it's a non-empty string a error occured.
                if (!empty($returnerr))
                {
                    $context->local()->message('error', $returnerr);
                    return 'upload.twig';
                }
                
                $this->pub = $pubcreator->getpub();

                # Set up a category.
                $cat = R::findOne('type', 'id=?', array($this->pub->category));

                # Call method to handle uploads -- this method returns a twig
                $errorpresent = $this->handleuploads($context, $cat->name, $this->pub);
                if (!empty($errorpresent))   
                {
                    return $errorpresent;
                }

                # Finally associate the staff member uploading to the publication
                $this->pub->uploader = $context->user();

                # Persist the file data
                foreach ($this->files as $f)
                {
                    $this->pub->xownFiles[] = $f;
                }

                R::store($this->pub);
                # The publication has been sucessfully created, let's divert the user to the view page using REST.
                $context->divert("/viewpub/" . $this->pub->id);

            }

            # Will not be returned in a successful POST.
            return 'upload.twig';

        }
    }
?>
