<?php
/**
 * A class that contains code to handle posting to a publication.
 *
 * @author Raffaello Perrotta <r.perrotta@ncl.ac.uk>
 * @copyright 2015 Raffaello Perrotta
 *
 */
    class Post extends Siteaction
    {

/**
 * Handle profile viewing which will handle posting to a publication /
 *
 * @param object    $context    The context object for the site
 *
 * @return void
 */
        public function handle($context)
        {
            # Get the publication corresponding to this RESTful call.
            $pub = R::load('publication', $context->rest()[0]);

            # Redbean generates a bean with id 0 if it doesn't exist
            if ($pub->id == 0)
            {
                # Ensure to not post to imaginary publication
                (new Web())->bad();
            }

            # Dispense a Post.
            $post = R::dispense('post');

            # Set which staff is making the post.
            $post->poster = $context->user()->id;

            # Must post content, silently strip to a maximum of 8000 characters.
            $post->content = substr(strip_tags($context->mustpostpar('content')), 0, 8000);
            
            # Associate it to the publication, one->many relation with cascade.
            $pub->xownPosts[] = $post;

            # Persist the data.
            R::store($pub);

            # Divert back to the viewer.
            $context->divert("/viewpub/" . $pub->id);
        }
    }
?>
