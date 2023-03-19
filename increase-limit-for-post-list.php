<?php

/*
 * Increase total returned posts from WPGraphQl
 * default: 100
 *
 * add this to functions.php
 */
add_filter('graphql_connection_max_query_amount', function (int $max_amount, $source, array $args, $context, $info) {
    // Bail if the fieldName isn't avail
    if (empty($info->fieldName)) {
        return $max_amount;
    }
    // Bail if we're not dealing with our target fieldName
    // 	if ( 'productCategories' !== $info->fieldName ) {
    // 		return $max_amount;
    // 	}
    return 999;
}, 10, 5);
