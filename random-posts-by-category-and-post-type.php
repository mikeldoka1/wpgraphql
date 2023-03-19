<?php

/**
 * WPGrapghQL get random posts based on post category and post type
 *
 * @param int $excludePost Exclude the current post being viewed
 * @param string $postType Post type
 * @param string $postCat Post Category
 * @param string $catName Category Name
 * @param int $postsPerPage Exclude a post by id
 * @return $data
 */
function get_random_posts_gql($excludePost = null, $postType = null, $postCat = null, $catName = null, $postsPerPage = 4)
{
    $query = array(
        'post_type' => array('post'),
        'post_status'           => array('publish'),
        'orderby'               => 'rand',
        'posts_per_page'        => $postsPerPage,
    );

    // allowable post types
    $allowedPostTypes = array(
        'post',
        'books',
        'lessons',
    );

    // allowable post categories
    $allowedTaxonomies = array(
        'book_cat',
        'lesson_cat',
        'category',
    );

    // default to post if empty
    if (!isset($postType)) {
        $query['post_type'] = 'post';
    }

    if (!empty($postType) && in_array($postType, $allowedPostTypes)) {
        $query['post_type'] = array($postType);
    }

    if (!empty($excludePost) && is_int($excludePost)) {
        $query['post__not_in'] = array($excludePost);
    }

    if (!empty($postCat) && !empty($catName) && in_array($postCat, $allowedTaxonomies)) {
        $query['tax_query'] = array(
            array(
                'taxonomy' => $postCat,
                'field' => 'slug',
                'terms' => sanitize_text_field($catName),
                ),
        );
    }


    //return $query; // enable for "debugging" https://domain.com/wp-admin/admin.php?page=graphiql-ide

    $data = array();

    $query = new WP_Query($query);

    $posts = $query->posts;

    foreach ($posts as $post) {
        $image_attributes = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');

        $excerpts = wp_strip_all_tags(get_the_excerpt($post->ID), true);
        $excerpts = str_replace(' Continue reading', '', $excerpts);

        $postData = array("title" => $post->post_title, "slug" => $post->post_name, "excerpt" => $excerpts, "image" => $image_attributes[0]);
        array_push($data, $postData);
    }

    return $data;
}

add_action('graphql_register_types', function () {
    register_graphql_field('RootQuery', 'randomPosts', [
        'type' => 'String',
        'args' => [
            'excludePost' => [
                'type' => 'Integer',
                'description' => __('Post id to exclude', 'domain'),
            ],
            'postType' => [
                'type' => 'String',
            ],
            'postCat' => [
                'type' => 'String',
                'description' => __('Taxonomy', 'domain'),
            ],
            'catName' => [
                'type' => 'String',
                'description' => __('Term', 'domain'),
            ],
        ],
        'description' => __('Return random posts', 'wp-graphql'),
        'resolve' => function ($source, $args, $context, $info) {
            return json_encode(get_random_posts_gql($args['excludePost'], $args['postType'], $args['postCat'], $args['catName'], 4));
        },
    ]);
});
